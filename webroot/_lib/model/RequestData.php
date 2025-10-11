<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class RequestData extends DataClass {

        public string $request_data_json = "";

        public string $logs = "";
        public int $user_error = 0;
        public int $system_error = 0;
        public string $error_message = "";

        public string $trace = "";
        public string $json_response = "";

    }