<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";
    require_once "./admin_lib.php";

    if (!lib::is_logged_in())
    {
        ob_clean();
        header("Location: /");
        exit();
    }

    if (!lib::current_user_is_admin())
    {
        ob_clean();
        header("Location: /user");
        exit();
    }

    lib::header_html();
    admin_lib::main_admin_nav();

    $logs = lib::select(
        "SELECT * FROM EventLog",
        []
    );

    foreach ($logs as $log)
    {
        ?>
            <div>
                <h4> <?= $log["event_name"] ?> </h4>
                <p> <?= $log["event_data"] ?> </p>
                <pre><?= json_encode(json_decode($log["event_data"]), JSON_PRETTY_PRINT) ?></pre>
            </div>
        <?php
    }

?>

<?php

    lib::footer_html();

