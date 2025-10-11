<?php

    namespace _lib\core;

    use _lib\model\User;
    use Exception;

    class App
    {

        static string $current_request = "";

        static function get_instance(): App
        {
            static $instance = null;
            if ($instance === null)
            {
                $instance = new App();
            }
            return $instance;
        }

        function __construct()
        {
            DataClass::set_default_database(
                DataBase::get_default_instance()
            );
        }

        /**
         * @throws Exception
         */
        function login(User $user): void
        {
            $_SESSION['user_id'] = $user->id;
        }

        function logout(): void
        {
            unset($_SESSION['user_id']);
            session_destroy();
        }

        function somebody_is_logged_in(): bool
        {
            return isset($_SESSION['user_id']);
        }

        /**
         * @throws Exception
         */
        function get_current_user(): ?User
        {
            if (!$this->somebody_is_logged_in())
            {
                return null;
            }

            return User::get_by_id($_SESSION['user_id']);
        }

        /**
         * @throws Exception
         */
        function current_user_is_admin(): bool
        {
            if (!$this->somebody_is_logged_in())
            {
                return false;
            }
            $current_user = $this->get_current_user();
            if ($current_user === null)
            {
                return false;
            }
            return $current_user->is_admin === 1;
        }

        /**
         * @throws Exception
         */
        function redirect_if_not_admin(string $target = "/user"): void
        {
            if (!$this->current_user_is_admin())
            {
                header("Location: $target");
                exit();
            }
        }

        function redirect_if_not_logged_in(string $target = "/"): void
        {
            if (!$this->somebody_is_logged_in())
            {
                header("Location: $target");
                exit();
            }
        }

        function force_login(): void
        {
            if (!$this->somebody_is_logged_in())
            {
                throw new UserError("Not logged in");
            }
        }

        function force_admin(): void
        {
            if (!$this->somebody_is_logged_in())
            {
                throw new UserError("Not logged in");
            }
            if (!$this->current_user_is_admin())
            {
                throw new UserError("Not admin");
            }
        }

        protected ?string $logging_space = null;

        /**
         * Check if the application is running on localhost
         */
        static function is_localhost(): bool
        {
            return !str_contains($_SERVER["DOCUMENT_ROOT"], 'w016728f');
        }

        /**
         * This handles errors, where no connection to the database is possible or other errors, f.e.
         * if we cannot send an email to the admins, etc.
         */
        function extra_error_handling(string $message, \Throwable $t, array $data): void {}

        /**
         * @throws Exception
         */
        function start_logging(string $logging_space): void
        {
            if ($this->$logging_space !== null)
            {
                throw new Exception(
                    "Logging already started with space: "
                    . $this->logging_space .
                    " couldn't start with space: $logging_space"
                );
            }
            $this->logging_space = $logging_space;
            ob_start();
        }

        function stop_logging(): string
        {
            $buffer = ob_get_clean();
            $this->logging_space = null;
            return $buffer;
        }

    }