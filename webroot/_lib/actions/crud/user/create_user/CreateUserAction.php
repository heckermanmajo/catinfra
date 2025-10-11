<?php

    namespace _lib\actions\crud\user\create_user;

    use _lib\core\Action;
    use _lib\core\DataBase;
    use _lib\core\UserError;
    use _lib\model\User;

    final class CreateUserAction extends Action
    {
        public function __construct(
            public string $username,
            public string $password,
            public string $email = "",
            public bool   $is_admin = false,
            public string $status = "active"
        ) {}

        /**
         * @throws UserError
         */
        protected function perform(): CreateUserActionResult
        {
            $db = DataBase::get_default_instance();

            if (empty($this->username))
            {
                throw new UserError("Username cannot be empty");
            }

            if (empty($this->password))
            {
                throw new UserError("Password cannot be empty");
            }

            $existing_user = User::select_one(
                "SELECT * FROM User WHERE username = ?",
                [$this->username],
                throw_on_null: false
            );

            if ($existing_user !== null)
            {
                throw new UserError("User with username '{$this->username}' already exists");
            }

            $new_user = new User([
                'username' => $this->username,
                'password_hash' => password_hash($this->password, PASSWORD_DEFAULT),
                'email' => $this->email,
                'status' => $this->status,
                'is_admin' => $this->is_admin ? 1 : 0,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $new_user->save($db);

            $result = new CreateUserActionResult(success: true);
            $result->message = "User '{$this->username}' created successfully";
            $result->user_created = true;
            $result->created_user = $new_user;

            return $result;
        }

    }
