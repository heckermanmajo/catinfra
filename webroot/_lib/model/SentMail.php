<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class SentMail extends DataClass
    {
        public int $user_id = 0;
        public int $community_id = 0;
        public string $subject = "";
        public string $body = "";
        public string $warnings = "";
        public string $trace = "";
        public int $success = 0;
    }
