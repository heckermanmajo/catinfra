<?php

    namespace _lib\actions\control\ensure_admin_account;

    use _lib\core\ActionResult;

    final class EnsureAdminAccountControlActionResult extends ActionResult
    {
        public bool $accounts_created = false;
        public array $created_usernames = [];
    }
