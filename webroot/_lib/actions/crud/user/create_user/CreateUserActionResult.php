<?php

    namespace _lib\actions\crud\user\create_user;

    use _lib\core\ActionResult;
    use _lib\model\User;

    final class CreateUserActionResult extends ActionResult
    {
        public bool $user_created = false;
        public ?User $created_user = null;
    }
