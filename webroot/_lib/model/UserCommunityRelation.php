<?php

    namespace _lib\model;

    use _lib\core\DataClass;

    class UserCommunityRelation extends DataClass
    {
        public int $community_id = 0;
        public int $user_id = 0;
        public string $relation_type = "";
    }
