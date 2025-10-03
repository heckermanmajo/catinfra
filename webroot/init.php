<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";

    if (lib::is_prod())
    {
        exit;
    }

    lib::init_and_update_db();
    lib::header_html();
    lib::footer_html();