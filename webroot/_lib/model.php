<?php

    #[Attribute]
    class LongText {}

    #[Attribute]
    class Blob {}

    class DataClass
    {
        public int $id;
        public string $created_at;
        public string $updated_at;

        // Override this in child classes
        protected static function table_name(): string
        {
            throw new Exception("table_name() not implemented in " . static::class);
        }

        function __construct(array $data = [])
        {
            foreach ($data as $key => $value)
            {
                $this->$key = $value;
            }
        }

        function to_array(): array
        {
            return get_object_vars($this);
        }

        /**
         * Generate migration SQL for this model
         * @throws ReflectionException
         */
        static function create_and_update(): array
        {
            $table = static::table_name();
            $sql = [];
            $sql[] = lib::table_creation_sql($table);

            $reflection = new ReflectionClass(static::class);
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property)
            {
                $name = $property->getName();
                // Skip base class properties
                if (in_array($name, ['id', 'created_at', 'updated_at'])) continue;

                $type = $property->getType();
                $typeName = $type ? $type->getName() : 'string';

                // Check for attributes
                $attributes = $property->getAttributes();
                $hasLongText = false;
                $hasBlob = false;
                foreach ($attributes as $attr)
                {
                    if ($attr->getName() === LongText::class) $hasLongText = true;
                    if ($attr->getName() === Blob::class) $hasBlob = true;
                }

                if ($hasLongText)
                {
                    $sql[] = lib::long_string_column_sql($table, $name);
                }
                elseif ($hasBlob)
                {
                    $sql[] = lib::blob_column_sql($table, $name);
                }
                elseif ($typeName === 'int')
                {
                    $sql[] = lib::integer_column_sql($table, $name);
                }
                elseif ($typeName === 'float')
                {
                    $sql[] = lib::real_column_sql($table, $name);
                }
                elseif ($typeName === 'bool')
                {
                    $sql[] = lib::integer_column_sql($table, $name); // bool as 0/1
                }
                else // string or other
                {
                    $sql[] = lib::string_column_sql($table, $name);
                }
            }

            return $sql;
        }

        /**
         * Insert a new record into the database
         * @throws Exception
         */
        static function insert(array $data, bool $create_event_log = true): int
        {
            return lib::insert(static::table_name(), $data, $create_event_log);
        }

        /**
         * Update an existing record in the database
         * @throws Exception
         */
        static function update(array $data, bool $create_event_log = false): bool
        {
            return lib::update(static::table_name(), $data, $create_event_log);
        }

        /**
         * Delete records from the database
         * @throws Exception
         */
        static function delete(array $where): bool
        {
            return lib::delete(static::table_name(), $where);
        }

        /**
         * Select records from the database
         * @throws Exception
         * @return static[]
         */
        static function select(string $select_sql, array $params = []): array
        {
            return array_map(fn($row) => new static($row), lib::select($select_sql, $params));
        }

        /**
         * Find a single record by ID
         * @throws Exception
         */
        static function find_by_id(int $id): ?static
        {
            $results = static::select(
                "SELECT * FROM " . static::table_name() . " WHERE id = :id LIMIT 1",
                ["id" => $id]
            );
            return $results[0] ?? null;
        }

        /**
         * Find all records
         * @throws Exception
         */
        static function all(): array
        {
            return static::select("SELECT * FROM " . static::table_name());
        }

        /**
         * Save the current instance (insert or update)
         * @throws Exception
         */
        function save(bool $create_event_log = true): bool
        {
            $data = $this->to_array();
            if (isset($this->id) && $this->id > 0)
            {
                return static::update($data, $create_event_log);
            }
            else
            {
                $this->id = static::insert($data, $create_event_log);
                return $this->id > 0;
            }
        }

    }

    class User extends DataClass
    {
        public string $username;
        public string $password_hash;
        public string $email;
        public string $status;
        public string $skool_user_id;
        public int $is_admin;
        public string $SKOOL_AUTH_TOKEN;
        public string $SKOOL_CLIENT_ID;
        public string $SKOOL_GA;
        public string $SKOOL_GA_B9;
        public string $SKOOL_GA_D0XK;
        public string $SKOOL_GCL_AU;
        public string $SKOOL_FBP;
        public string $SKOOL_AJS_ANON;
        public string $SKOOL_WAF_COOKIE;
        public string $SKOOL_WAF_HEADER;
        public string $auth_key;

        protected static function table_name(): string
        {
            return "User";
        }
    }

    class Community extends DataClass
    {
        public string $tenant_slug;
        public string $tenant_name;
        public string $skool_id;
        public string $primary_community;
        public string $created_by_user_id;
        public string $created_by_user_name;

        protected static function table_name(): string
        {
            return "Community";
        }
    }

    class UserCommunityRelation extends DataClass
    {
        public int $community_id;
        public int $user_id;
        public string $relation_type;

        protected static function table_name(): string
        {
            return "UserCommunityRelation";
        }
    }

    class RawDataPage extends DataClass
    {
        public string $page_type;
        public string $related_skoolid;
        #[LongText]
        public string $content;
        public string $logs;
        public string $trace;
        public string $community_id;
        public int $success;

        protected static function table_name(): string
        {
            return "RawDataPage";
        }
    }

    class AnalysisResultData extends DataClass
    {
        public string $result_type;
        public string $source_file_name;
        #[LongText]
        public string $content;
        public string $logs;
        public string $trace;
        public string $community_id;
        public int $success;

        protected static function table_name(): string
        {
            return "AnalysisResultData";
        }
    }

    class EventLog extends DataClass
    {
        public int $user_id;
        public int $community_id;
        public string $event_type;
        public string $priority;
        public int $done;
        public string $event_description;
        public string $event_data;
        public string $trace;

        protected static function table_name(): string
        {
            return "EventLog";
        }
    }

    class SentMail extends DataClass
    {
        public int $user_id;
        public int $community_id;
        public string $subject;
        public string $body;
        public string $warnings;
        public string $trace;
        public int $success;

        protected static function table_name(): string
        {
            return "SentMail";
        }
    }

    $m = [];

    # one user represents a skool account OR an admin
    $U = "User";
    $m[] = lib::table_creation_sql($U);
    $m[] = lib::string_column_sql($U, "username");
    $m[] = lib::string_column_sql($U, "password_hash");
    $m[] = lib::string_column_sql($U, "email");
    $m[] = lib::string_column_sql($U, "status");
    $m[] = lib::string_column_sql($U, "skool_user_id");
    $m[] = lib::integer_column_sql($U, "is_admin");
    $m[] = lib::string_column_sql($U, "SKOOL_AUTH_TOKEN");
    $m[] = lib::string_column_sql($U, "SKOOL_CLIENT_ID");
    $m[] = lib::string_column_sql($U, "SKOOL_GA");
    $m[] = lib::string_column_sql($U, "SKOOL_GA_B9");
    $m[] = lib::string_column_sql($U, "SKOOL_GA_D0XK");
    $m[] = lib::string_column_sql($U, "SKOOL_GCL_AU");
    $m[] = lib::string_column_sql($U, "SKOOL_FBP");
    $m[] = lib::string_column_sql($U, "SKOOL_AJS_ANON");
    $m[] = lib::string_column_sql($U, "SKOOL_WAF_COOKIE");
    $m[] = lib::string_column_sql($U, "SKOOL_WAF_HEADER");
    $m[] = lib::string_column_sql($U, "auth_key");

    # one community represents one skool community
    $C = "Community";
    $m[] = lib::table_creation_sql($C);
    $m[] = lib::string_column_sql($C, "tenant_slug");
    $m[] = lib::string_column_sql($C, "tenant_name");
    $m[] = lib::string_column_sql($C, "skool_id");
    $m[] = lib::string_column_sql($C, "primary_community");
    $m[] = lib::string_column_sql($C, "created_by_user_id");
    $m[] = lib::string_column_sql($C, "created_by_user_name");

    $UCR = "UserCommunityRelation";
    $m[] = lib::table_creation_sql($UCR);
    $m[] = lib::integer_column_sql($UCR, "community_id");
    $m[] = lib::integer_column_sql($UCR, "user_id");
    $m[] = lib::string_column_sql($UCR, "relation_type");

    $RDP = "RawDataPage";
    $m[] = lib::table_creation_sql($RDP);
    $m[] = lib::string_column_sql($RDP, "page_type");
    $m[] = lib::string_column_sql($RDP, "related_skoolid"); # if this is a chat, of a user profile, etc.
    $m[] = lib::long_string_column_sql($RDP, "content");
    $m[] = lib::string_column_sql($RDP, "logs");
    $m[] = lib::string_column_sql($RDP, "trace");
    $m[] = lib::string_column_sql($RDP, "community_id");
    $m[] = lib::integer_column_sql($RDP, "success");

    $ARD = "AnalysisResultData";
    $m[] = lib::table_creation_sql($ARD);
    $m[] = lib::string_column_sql($ARD, "result_type");
    $m[] = lib::string_column_sql($ARD, "source_file_name");
    $m[] = lib::string_column_sql($ARD, "content");
    $m[] = lib::string_column_sql($ARD, "logs");
    $m[] = lib::string_column_sql($ARD, "trace");
    $m[] = lib::string_column_sql($ARD, "community_id");
    $m[] = lib::integer_column_sql($ARD, "success");

    $EL = "EventLog";
    $m[] = lib::table_creation_sql($EL);
    $m[] = lib::integer_column_sql($EL, "user_id");
    $m[] = lib::integer_column_sql($EL, "community_id");
    $m[] = lib::string_column_sql($EL, "event_type");
    $m[] = lib::string_column_sql($EL, "priority");
    $m[] = lib::integer_column_sql($EL, "done");
    $m[] = lib::string_column_sql($EL, "event_description");
    $m[] = lib::string_column_sql($EL, "event_data");
    $m[] = lib::string_column_sql($EL, "trace");

    $SM = "SentMail";
    $m[] = lib::table_creation_sql($SM);
    $m[] = lib::integer_column_sql($SM, "user_id");
    $m[] = lib::integer_column_sql($SM, "community_id");
    $m[] = lib::string_column_sql($SM, "subject");
    $m[] = lib::string_column_sql($SM, "body");
    $m[] = lib::string_column_sql($SM, "warnings");
    $m[] = lib::string_column_sql($SM, "trace");
    $m[] = lib::integer_column_sql($SM, "success");

    return $m;