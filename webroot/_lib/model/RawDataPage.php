<?php

    namespace _lib\model;

    use _lib\core\DataClass;
    use _lib\core\LongText;

    class RawDataPage extends DataClass
    {
        public string $page_type = "";
        public string $related_skoolid = "";

        #[LongText]
        public string $content = "";

        public string $logs = "";
        public string $trace = "";
        public string $community_id = "";
        public int $success = 0;
    }
