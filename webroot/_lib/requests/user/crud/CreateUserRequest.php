<?php

    namespace _lib\requests\user\crud;

    use _lib\actions\crud\user\create_user\CreateUserAction;
    use _lib\core\App;
    use _lib\core\Request;
    use _lib\core\RequestInput;
    use _lib\core\RequestOutput;
    use _lib\core\UserError;

    class CreateUserRequest extends Request {

        /**
         * @throws UserError
         */
        protected function _execute(
            RequestInput $input
        ): RequestOutput
        {

            $app = App::get_instance();
            $app->force_admin();

            $username = $input->s('username');
            $password = $input->s('password');

            $email = $input->s('email', '');
            $is_admin = $input->b('is_admin', false);
            $status = $input->s('status', 'active');

            $action = new CreateUserAction(
                username: $username,
                password: $password,
                email: $email,
                is_admin: $is_admin,
                status: $status
            );

            $result = $action->execute();

            $result->throw_if_not_successful();

            return new RequestOutput([
                'success' => true,
                'message' => $result->message,
                'user' => $result->created_user,
                'user_id' => $result->created_user->id,
                'action_result' => $result
            ]);

        }

    }