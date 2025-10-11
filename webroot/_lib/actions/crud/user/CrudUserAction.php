<?php

    namespace _lib\actions\crud\user;

    use _lib\core\Action;
    use _lib\core\ActionResult;
    use _lib\core\App;
    use _lib\model\Community;
    use _lib\model\User;

    class CrudUserAction extends Action
    {
        function __construct(
            public User $target_user,
            public Community $target_community,
        ) {
            $app = App::get_instance();
        }

        protected function perform(): ActionResult
        {
            // TODO: Implement perform() method.
        }

    }