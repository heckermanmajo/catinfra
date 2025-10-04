<?php

    final class admin_lib
    {
        private function __construct() {}

        public static function main_admin_nav(): void
        {
            ?>
                <header>
                    <nav>
                        <a href="/admin/"> Start Page </a>
                        &nbsp; | &nbsp;
                        <a href="/admin/users.php"> Users </a>
                        &nbsp; | &nbsp;
                        <a href="/admin/communities.php"> Communities </a>
                        &nbsp; | &nbsp;
                        <a href="/admin/eventlogs.php"> Eventlogs </a>
                        &nbsp; | &nbsp;
                    </nav>
                </header>
            <?php
        }
    }