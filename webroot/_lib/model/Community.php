<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class Community extends DataClass
    {
        public string $tenant_slug = "";
        public string $tenant_name = "";
        public string $skool_id = "";
        public string $primary_community = "";
        public string $created_by_user_id = "";
        public string $created_by_user_name = "";
    }
