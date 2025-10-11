<?php

    namespace _lib\views;

    class AdminNavView
    {
        function render(): string
        {
            ob_start();
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
            return ob_get_clean();
        }
    }
