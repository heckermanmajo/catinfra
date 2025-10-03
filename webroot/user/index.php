<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";

    if (!lib::is_logged_in())
    {
        ob_clean();
        header("Location: /");
        exit();
    }

    lib::header_html();

?>
    <h4> USER VIEWS -> list of communities ... </h4>
<?php

    lib::footer_html();
