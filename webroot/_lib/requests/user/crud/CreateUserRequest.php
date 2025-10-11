<?php

    namespace _lib\requests\user\crud;

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
            return new RequestOutput(new \Exception("NOT IMPLEMENTED"));

        }

    }