<?php

    namespace _lib\requests\user;

    use _lib\core\App;
    use _lib\core\Request;
    use _lib\core\RequestInput;
    use _lib\core\RequestOutput;
    use _lib\core\UserError;
    use _lib\model\User;

    class LoginUserRequest extends Request
    {

        /**
         * @throws UserError
         * @throws \Exception
         */
        protected function _execute(
            RequestInput $input
        ): RequestOutput {

            $username = $input->i('username');
            $password = $input->s('password');

            $user = User::select_one(
                "SELECT * FROM User WHERE username = ?",
                [$username],
                throw_on_null:  true
            );

            if (!password_verify($password, $user['password_hash']))
            {
                throw new UserError("Invalid username or password");
            }

            App::get_instance()->login($user);

            return new RequestOutput(
                [
                    'success' => true,
                ]
            );

        }
    }