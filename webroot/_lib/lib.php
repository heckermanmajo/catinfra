<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    $json = json_decode(file_get_contents('php://input'), true);
    if ($json) $_REQUEST = array_merge($_REQUEST, $json);

    final class lib
    {

        private function __construct() {}

        static function is_prod(): bool
        {
            return str_contains($_SERVER["DOCUMENT_ROOT"], 'w016728f');
        }

        /**
         * @throws Exception
         */
        static function db(): PDO
        {
            static $pdo = null;
            if ($pdo === null)
            {
                $host = "frellow.de";
                $dbname = "d044f42e";
                $user = "d044f42e";
                $pass = "H5CmgEBg24fVcYbpcfQe";

                $dsn = "mysql:host=$host;dbname=$dbname";
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $pdo;
        }


        /**
         * @throws Exception
         */
        static function select(string $sql, array $data): array
        {
            $stmt = lib::db()->prepare($sql);
            $stmt->execute($data);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * @throws Exception
         */
        static function insert(string $table_name, array $data): int
        {
            $keys = array_keys($data);
            $placeholders = array_map(fn($k) => ":$k", $keys);
            $sql = "INSERT INTO `$table_name` (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $placeholders) . ")";
            #print_r($sql);
            $stmt = lib::db()->prepare($sql);
            $params = [];
            foreach ($data as $key => $value)
            {
                $params[":$key"] = $value;
            }
            $stmt->execute($params);
            return (int)lib::db()->lastInsertId();
        }

        /**
         * @throws Exception
         */
        static function update(string $table_name, array $data): bool
        {
            if (!isset($data['id']))
            {
                throw new InvalidArgumentException("Data must contain 'id' key for update");
            }
            $id = $data['id'];
            unset($data['id']);
            $set_clauses = [];
            foreach (array_keys($data) as $key)
            {
                $set_clauses[] = "`$key` = :$key";
            }
            $sql = "UPDATE `$table_name` SET " . implode(", ", $set_clauses) . " WHERE id = :id";
            $stmt = lib::db()->prepare($sql);
            $params = [':id' => $id];
            foreach ($data as $key => $value)
            {
                $params[":$key"] = $value;
            }
            return $stmt->execute($params);
        }

        /**
         * @throws Exception
         */
        static function delete(string $table_name, array $where): bool
        {
            $where_clauses = [];
            foreach (array_keys($where) as $key)
            {
                $where_clauses[] = "`$key` = :$key";
            }
            $sql = "DELETE FROM `$table_name` WHERE " . implode(" AND ", $where_clauses);
            $stmt = lib::db()->prepare($sql);
            $params = [];
            foreach ($where as $key => $value)
            {
                $params[":$key"] = $value;
            }
            return $stmt->execute($params);
        }

        static function is_logged_in(): bool
        {
            return isset($_SESSION['user_id']);
        }

        static function force_logged_in(): void
        {
            if (!lib::is_logged_in())
            {
                throw new Exception("Not logged in");
            }
        }

        /**
         * @throws Exception
         */
        static function current_user(): ?array
        {
            if (!lib::is_logged_in()) return null;
            $user = lib::select("SELECT * FROM User WHERE id = ?", [$_SESSION['user_id']]);
            return count($user) > 0 ? $user[0] : null;
        }

        /**
         * @throws Exception
         */
        static function current_user_is_admin(): bool
        {
            $current_user = lib::current_user();
            if ($current_user == null) return false;
            if ($current_user["is_admin"] === 1) return true;
            return false;
        }

        static function i(string $name): int
        {
            return (int)($_REQUEST[$name]
                ?? throw new ValueError("Missing parameter: $name"));
        }

        static function s(string $name): string
        {
            return (string)($_REQUEST[$name]
                ?? throw new ValueError("Missing parameter: $name"));
        }

        static function idefault(string $name, int $default = 0): int
        {
            return isset($_REQUEST[$name]) ? (int)$_REQUEST[$name] : $default;
        }

        static function sdefault(string $name, string $default = ''): string
        {
            return isset($_REQUEST[$name]) ? (string)$_REQUEST[$name] : $default;
        }

        static function lang(): string
        {
            return "de";
        }

        static function table_creation_sql($name): string
        {
            return "CREATE TABLE IF NOT EXISTS `$name` (
                `id` INTEGER NOT NULL AUTO_INCREMENT,
                `created_at` INTEGER,
                `updated_at` INTEGER,
                PRIMARY KEY (`id`)
            )";
        }

        static function integer_column_sql($table_name, $name): string
        {
            return "ALTER TABLE $table_name ADD `$name` INTEGER DEFAULT 0";
        }

        static function string_column_sql($table_name, $name): string
        {
            return "ALTER TABLE $table_name ADD `$name` TEXT DEFAULT ''";
        }

        static function long_string_column_sql($table_name, $name): string
        {
            return "ALTER TABLE $table_name ADD `$name` LONGTEXT DEFAULT ''";
        }

        static function real_column_sql($table_name, $name): string
        {
            return "ALTER TABLE $table_name ADD `$name` REAL DEFAULT ''";
        }


        static function do_try_and_return_json(callable $fn): void
        {
            ob_start();
            header('Content-Type: application/json');
            try
            {
                $result = $fn();
                $buffer = ob_get_clean();
                echo json_encode(
                    [
                        'success' => true,
                        'data' => $result,
                        'buffer' => $buffer
                    ]
                );
            }
            catch (Throwable $e)
            {
                $buffer = ob_get_clean();
                echo json_encode(
                    [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'buffer' => $buffer
                    ]
                );
            }
        }

        /**
         * @throws Exception
         */
        static function init_and_update_db(): void
        {
            $db = lib::db();
            $model = require $_SERVER["DOCUMENT_ROOT"] . "/_lib/model.php";
            foreach ($model  as $sql_statment)
            {
                try
                {
                    $db->exec($sql_statment);
                }
                catch (Throwable $t)
                {
                    # ...
                    echo $t->getMessage();
                    echo "<br>";
                }
            }
            $admin = lib::select("SELECT * FROM User WHERE username = ?", ['admin']);
            if (count($admin) === 0)
            {
                lib::insert(
                    "User",
                    [
                        "username" => "majo",
                        "password_hash" => password_hash("8W6FfVmcTnangl03C8S85KU", PASSWORD_DEFAULT),
                        "is_admin" => 1,
                        "created_at" => time(),
                        "updated_at" => time(),
                    ]
                );
                lib::insert(
                    "User",
                    [
                        "username" => "niklas",
                        "password_hash" => password_hash("69KlrZ21TAJEiO9PRUzErcV", PASSWORD_DEFAULT),
                        "is_admin" => 1,
                        "created_at" => time(),
                        "updated_at" => time(),
                    ]
                );
            }
        }

        static function is_mobile(): bool
        {
            return false;
        }

        static function is_dark_mode(): bool
        {
            return false;
        }

        static function header_html(string $title = 'App', string $css = "",  string $onload = ""): void
        {
            ?>
            <!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="/_lib/lib.css">
                <script src="/_lib/lib.js" defer></script>
                <?= $onload ?>
                <?= $css ?>
                <title><?php echo htmlspecialchars($title); ?></title>
            </head>
            <body>
            <?php
        }

        static function footer_html(): void
        {
            ?>
            </body>
            </html>
            <?php
        }

        static function format_ago(int $timestamp): string
        {
            # months, days, hours, minutes, seconds
            $diff = time() - $timestamp;
            $periods = ['second', 'minute', 'hour', 'day', 'month', 'year', 'decade'];
            $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];
            $now = time();
            $difference = $now - $timestamp;
            $tense = 'ago';
            for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++)
            {
                $difference /= $lengths[$j];
            }
            $difference = round($difference);
            if ($difference != 1)
            {
                $periods[$j] .= 's';
            }
            return "$difference $periods[$j] $tense";
        }

        static function event_log(
            string $event_type,
            string $priority,
            string $event_description,
            array $event_data = [],
            string $trace = "",
            int $user_id = 0,
            int $community_id = 0
        )
        {
            $allowed_priorities = ['info', 'warning', 'error', 'critical'];
            if (!in_array($priority, $allowed_priorities))
            {
                throw new ValueError("Invalid priority: $priority");
            }
            lib::insert(
                "EventLog",
                [
                    "event_type" => $event_type,
                    "priority" => $priority,
                    "event_description" => $event_description,
                    "event_data" => json_encode($event_data),
                    "trace" => $trace,
                    "user_id" => $user_id,
                    "community_id" => $community_id,
                    "created_at" => time(),
                ]
            );
        }

    }
