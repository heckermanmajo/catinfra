<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class AnalysisResultData extends DataClass
    {
        public string $result_type = "";
        public string $source_file_name = "";
        public string $content = "";
        public string $logs = "";
        public string $trace = "";
        public string $community_id = "";
        public int $success = 0;
    }
