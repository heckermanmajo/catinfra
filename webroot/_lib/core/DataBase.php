<?php

    namespace _lib\core;

    use Exception;
    use InvalidArgumentException;
    use PDO;
    use Throwable;

    /**
     * Wrapper around the database to keep the DataClass lean.
     */
    class DataBase
    {

        static public DataBase $default_database;

        static function get_default_instance(): DataBase
        {
            if (!isset(self::$default_database))
            {
                $host = "frellow.de";
                $dbname = "d044f42e";
                $user = "d044f42e";
                $pass = "H5CmgEBg24fVcYbpcfQe";
                $dsn = "mysql:host=$host;dbname=$dbname";
                self::$default_database = new DataBase($dsn, $user, $pass);
            }
            return self::$default_database;
        }

        private PDO $connection;

        function __construct(
            string $dsn,
            string $username = "",
            string $password = "",
            array  $options = []
        )
        {
            $this->connection = new PDO($dsn, $username, $password, $options);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        /**
         * Generate INSERT SQL statement with parameters
         * @return array{sql: string, params: array}
         */
        function get_insert_sql(string $table, array $data): array
        {
            // Ensure array values are converted to JSON
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    $data[$key] = json_encode($value);
                }
            }

            $keys = array_keys($data);
            $placeholders = array_map(fn($k) => ":$k", $keys);
            $sql = "INSERT INTO `$table` (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $placeholders) . ")";

            $params = [];
            foreach ($data as $key => $value)
            {
                $params[":$key"] = $value;
            }

            return ['sql' => $sql, 'params' => $params];
        }

        /**
         * Generate UPDATE SQL statement with parameters
         * @return array{sql: string, params: array}
         * @throws InvalidArgumentException
         */
        function get_update_sql(string $table, array $data): array
        {
            if (!isset($data['id']))
            {
                throw new InvalidArgumentException("Data must contain 'id' key for update");
            }

            // Ensure array values are converted to JSON
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    $data[$key] = json_encode($value);
                }
            }

            $id = $data['id'];
            unset($data['id']);

            $set_clauses = [];
            foreach (array_keys($data) as $key)
            {
                $set_clauses[] = "`$key` = :$key";
            }

            $sql = "UPDATE `$table` SET " . implode(", ", $set_clauses) . " WHERE id = :id";

            $params = [':id' => $id];
            foreach ($data as $key => $value)
            {
                $params[":$key"] = $value;
            }

            return ['sql' => $sql, 'params' => $params];
        }

        /**
         * Generate DELETE SQL statement with parameters
         * @return array{sql: string, params: array}
         */
        function get_delete_sql(string $table, array $where): array
        {
            $where_clauses = [];
            foreach (array_keys($where) as $key)
            {
                $where_clauses[] = "`$key` = :$key";
            }

            $sql = "DELETE FROM `$table` WHERE " . implode(" AND ", $where_clauses);

            $params = [];
            foreach ($where as $key => $value)
            {
                $params[":$key"] = $value;
            }

            return ['sql' => $sql, 'params' => $params];
        }

        /**
         * Execute a SELECT query and return results
         * @throws Exception
         */
        function select(string $sql, array $params = []): array
        {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Execute a raw SQL string without parameters
         * @throws Exception
         */
        function execute_string(string $sql): bool
        {
            return $this->connection->exec($sql) !== false;
        }

        /**
         * Execute a prepared statement with parameters
         * @throws Exception
         */
        function execute_prepared(string $sql, array $params = []): bool
        {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        }

        /**
         * Insert a record into the database
         * @throws Exception
         */
        function insert(string $table, array $data): int
        {
            $data['created_at'] = time();

            $insert_data = $this->get_insert_sql($table, $data);

            try
            {
                $stmt = $this->connection->prepare($insert_data['sql']);
                $stmt->execute($insert_data['params']);
                return (int)$this->connection->lastInsertId();
            }
            catch (Throwable $e)
            {
                echo "<pre>";
                echo $e->getMessage();
                echo "<br>";
                echo $e->getTraceAsString();
                echo "<br>";
                echo $insert_data['sql'];
                echo "<br>";
                echo json_encode($insert_data['params']);
                echo "</pre>";
                throw $e;
            }
        }

        /**
         * Update a record in the database
         * @throws Exception
         */
        function update(string $table, array $data): bool
        {
            $data['updated_at'] = time();

            $update_data = $this->get_update_sql($table, $data);

            $stmt = $this->connection->prepare($update_data['sql']);
            return $stmt->execute($update_data['params']);
        }

        /**
         * Delete records from the database
         * @throws Exception
         */
        function delete(string $table, array $where): bool
        {
            $delete_data = $this->get_delete_sql($table, $where);

            $stmt = $this->connection->prepare($delete_data['sql']);
            return $stmt->execute($delete_data['params']);
        }

        /**
         * Generate CREATE TABLE SQL
         */
        function table_creation_sql(string $name): string
        {
            return "CREATE TABLE IF NOT EXISTS `$name` (
                `id` INTEGER NOT NULL AUTO_INCREMENT,
                `created_at` INTEGER,
                `updated_at` INTEGER,
                `deleted_at` INTEGER,
                `is_deleted` INTEGER DEFAULT 0,
                PRIMARY KEY (`id`)
            )";
        }

        /**
         * Generate ALTER TABLE ADD COLUMN SQL for integer
         */
        function integer_column_sql(string $table_name, string $name): string
        {
            return "ALTER TABLE `$table_name` ADD `$name` INTEGER DEFAULT 0";
        }

        /**
         * Generate ALTER TABLE ADD COLUMN SQL for string
         */
        function string_column_sql(string $table_name, string $name): string
        {
            return "ALTER TABLE `$table_name` ADD `$name` TEXT DEFAULT ''";
        }

        /**
         * Generate ALTER TABLE ADD COLUMN SQL for long text
         */
        function long_string_column_sql(string $table_name, string $name): string
        {
            return "ALTER TABLE `$table_name` ADD `$name` LONGTEXT DEFAULT ''";
        }

        /**
         * Generate ALTER TABLE ADD COLUMN SQL for blob
         */
        function blob_column_sql(string $table_name, string $name): string
        {
            return "ALTER TABLE `$table_name` ADD `$name` BLOB";
        }

        /**
         * Generate ALTER TABLE ADD COLUMN SQL for real/float
         */
        function real_column_sql(string $table_name, string $name): string
        {
            return "ALTER TABLE `$table_name` ADD `$name` REAL DEFAULT 0";
        }

        /**
         * Generate ALTER TABLE ADD COLUMN SQL for boolean
         */
        function boolean_column_sql(string $table_name, string $name): string
        {
            return "ALTER TABLE `$table_name` ADD `$name` INTEGER DEFAULT 0";
        }

    }