<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class EventLog extends DataClass
    {
        public int $user_id = 0;
        public int $community_id = 0;
        public string $event_type = "";
        public string $priority = "";
        public int $done = 0;
        public string $event_description = "";
        public string $event_data = "";
        public string $trace = "";
    }