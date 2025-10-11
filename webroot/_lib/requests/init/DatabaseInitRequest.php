<?php

    namespace _lib\requests\init;

    use _lib\actions\control\ensure_admin_account\EnsureAdminAccountControlAction;
    use _lib\actions\control\initialize_database\InitializeDatabaseControlAction;
    use _lib\core\Request;
    use _lib\core\RequestInput;
    use _lib\core\RequestOutput;

    class DatabaseInitRequest extends Request
    {

        protected function _execute(RequestInput $input): RequestOutput
        {
            // First, initialize database tables
            $init_db_action = new InitializeDatabaseControlAction();
            $init_result = $init_db_action->execute();
            $init_result->throw_if_not_successful();

            // Then, ensure admin accounts exist
            $admin_action = new EnsureAdminAccountControlAction();
            $admin_result = $admin_action->execute();
            $admin_result->throw_if_not_successful();

            $output = new RequestOutput([
                'success' => true,
                'database_init' => $init_result,
                'admin_accounts' => $admin_result,
            ]);

            return $output;
        }

    }
