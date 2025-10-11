<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class ActionResultData extends DataClass
    {
        public int $started_at = 0;
        public int $ended_at = 0;
        public string $message = "";
        public string $buffer = "";
        public string $trace = "";
        public string $action_name = "";
        public string $executed_during_request = "";
        public int $user_id = 0;
        public int $success = 0;
        public string $data = "{}";
    }