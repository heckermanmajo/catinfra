<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class User extends DataClass
    {
        public string $username = "";
        public string $password_hash = "";
        public string $email = "";
        public string $status = "";
        public string $skool_user_id = "";
        public int $is_admin = 0;
        public string $SKOOL_AUTH_TOKEN = "";
        public string $SKOOL_CLIENT_ID = "";
        public string $SKOOL_GA = "";
        public string $SKOOL_GA_B9 = "";
        public string $SKOOL_GA_D0XK = "";
        public string $SKOOL_GCL_AU = "";
        public string $SKOOL_FBP = "";
        public string $SKOOL_AJS_ANON = "";
        public string $SKOOL_WAF_COOKIE = "";
        public string $SKOOL_WAF_HEADER = "";
        public string $auth_key = "";
    }