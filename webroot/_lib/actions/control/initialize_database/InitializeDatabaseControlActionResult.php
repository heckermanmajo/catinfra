<?php

    namespace _lib\actions\control\initialize_database;

    use _lib\core\ActionResult;

    final class InitializeDatabaseControlActionResult extends ActionResult
    {
        public int $success_count = 0;
        public int $skipped_count = 0;
        public int $error_count = 0;
        public array $errors = [];
        public array $initialized_models = [];
    }
