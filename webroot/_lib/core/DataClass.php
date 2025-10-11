<?php

    namespace _lib\core;

    use JsonSerializable;
    use ReflectionClass;
    use ReflectionProperty;

    abstract class DataClass implements JsonSerializable
    {

        static private DataBase $default_database;

        static function set_default_database(DataBase $database): void
        {
            self::$default_database = $database;
        }

        public int $id = -1;

        public int $created_at = 0;
        public int $updated_at = 0;
        public int $deleted_at = 0;

        public bool $is_deleted = false;

        static function table_name(): string
        {
            return (new ReflectionClass(static::class))->getShortName();
        }

        function __construct(array $data = [])
        {
            foreach ($data as $key => $value)
            {
                if ($value !== null)
                {
                    $this->$key = $value;
                }
            }
        }

        function jsonSerialize(): array
        {
            return get_object_vars($this);
        }

        function save(?DataBase $db = null): void
        {
            $db = $db ?? self::$default_database;

            $data = get_object_vars($this);

            if ($this->id > 0)
            {
                $db->update(static::table_name(), $data);
            }
            else
            {
                $this->id = $db->insert(static::table_name(), $data);
            }
        }

        function hard_delete(?DataBase $db = null): void
        {
            $db = $db ?? self::$default_database;

            $db->delete(static::table_name(), ['id' => $this->id]);
        }

        function soft_delete(?DataBase $db = null): void
        {
            $db = $db ?? self::$default_database;
            $this->is_deleted = true;
            $this->deleted_at = time();
            $this->save($db);
        }

        /**
         * @param string $full_sql
         * @param array $data
         * @return array<static>
         */
        static function select(string $full_sql, array $data = [], ?DataBase $db = null): array
        {
            $db = $db ?? self::$default_database;

            $rows = $db->select($full_sql, $data);

            return array_map(fn($row) => new static($row), $rows);
        }

        static function select_one(string $full_sql, array $data = [], bool $throw_on_null = false, ?DataBase $db = null): ?static
        {
            $db = $db ?? self::$default_database;

            $rows = $db->select($full_sql, $data);

            if (count($rows) === 0)
            {
                if ($throw_on_null)
                {
                    $short = new ReflectionClass(static::class)->getShortName();
                    throw new UserError("No $short record found.");
                }
                return null;
            }

            return new static($rows[0]);
        }

        /**
         * Get a single record by ID
         * @param int $id
         * @param DataBase|null $db
         * @return static|null
         */
        static function get_by_id(int $id, ?DataBase $db = null): ?static
        {
            $db = $db ?? self::$default_database;
            $table = static::table_name();

            $rows = $db->select("SELECT * FROM `$table` WHERE id = ?", [$id]);

            if (count($rows) === 0)
            {
                return null;
            }

            return new static($rows[0]);
        }

        /**
         * Generate SQL statements to create/alter table based on class properties
         * @return array<string>
         */
        static function create_and_alter_table(?DataBase $db = null): array
        {
            $db = $db ?? self::$default_database;
            $table = static::table_name();
            $sql = [];

            // Create table
            $sql[] = $db->table_creation_sql($table);

            // Get all public properties using reflection
            $reflection = new ReflectionClass(static::class);
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
            {
                $name = $property->getName();

                // Skip base DataClass properties
                if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at', 'is_deleted']))
                {
                    continue;
                }

                $type = $property->getType();
                $typeName = $type ? $type->getName() : 'string';

                // Check for attributes
                $attributes = $property->getAttributes();
                $hasLongText = false;
                $hasBlob = false;

                foreach ($attributes as $attr)
                {
                    if ($attr->getName() === LongText::class)
                    {
                        $hasLongText = true;
                    }
                    if ($attr->getName() === Blob::class)
                    {
                        $hasBlob = true;
                    }
                }

                // Map PHP types to SQL column types
                if ($hasLongText)
                {
                    $sql[] = $db->long_string_column_sql($table, $name);
                }
                elseif ($hasBlob)
                {
                    $sql[] = $db->blob_column_sql($table, $name);
                }
                elseif ($typeName === 'int')
                {
                    $sql[] = $db->integer_column_sql($table, $name);
                }
                elseif ($typeName === 'float')
                {
                    $sql[] = $db->real_column_sql($table, $name);
                }
                elseif ($typeName === 'bool')
                {
                    $sql[] = $db->boolean_column_sql($table, $name);
                }
                else // string or other
                {
                    $sql[] = $db->string_column_sql($table, $name);
                }
            }

            return $sql;
        }

    }