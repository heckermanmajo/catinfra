<?php

    namespace _lib\actions\control\ensure_admin_account;

    use _lib\core\Action;
    use _lib\core\DataBase;
    use _lib\model\User;

    final class EnsureAdminAccountControlAction extends Action
    {

        protected function perform(): EnsureAdminAccountControlActionResult
        {
            $db = DataBase::get_default_instance();

            // Check if admin user already exists
            $existing_admin = User::select_one(
                "SELECT * FROM User WHERE username = ?",
                ['admin'],
                throw_on_null: false
            );

            if ($existing_admin !== null)
            {
                $result = new EnsureAdminAccountControlActionResult(success: true);
                $result->message = "Admin accounts already exist";
                $result->accounts_created = false;
                return $result;
            }

            // Create admin accounts
            $majo = new User([
                'username' => 'majo',
                'password_hash' => password_hash('8W6FfVmcTnangl03C8S85KU', PASSWORD_DEFAULT),
                'is_admin' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
            $majo->save($db);

            $niklas = new User([
                'username' => 'niklas',
                'password_hash' => password_hash('69KlrZ21TAJEiO9PRUzErcV', PASSWORD_DEFAULT),
                'is_admin' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
            $niklas->save($db);

            $result = new EnsureAdminAccountControlActionResult(success: true);
            $result->message = "Admin accounts created successfully";
            $result->accounts_created = true;
            $result->created_usernames = ['majo', 'niklas'];
            return $result;
        }

    }
