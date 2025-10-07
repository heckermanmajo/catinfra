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

    if (lib::sdefault("search") !== "")
    {
        $logs = lib::select(
            "SELECT * FROM EventLog WHERE event_type LIKE :search OR event_data LIKE :search",
            [
                "search" => "%" . lib::s('search') . "%",
            ]
        );
    }
    else
    {
        $logs = lib::select(
            "SELECT * FROM EventLog",
            []
        );
    }

?>
    <form method="get">
        <input type="text" name="search" placeholder="Search">
        <input type="submit" value="Search">
    </form>
<?php

    foreach ($logs as $log)
    {
        ?>
        <article>
            <h4> <?= $log["event_type"] ?> </h4>
            <pre><?= json_encode(json_decode($log["event_data"]), JSON_PRETTY_PRINT) ?></pre>
        </article>
        <?php
    }

?>

<?php

    lib::footer_html();

