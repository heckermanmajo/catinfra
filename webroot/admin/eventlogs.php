<?php

    require_once "./_lib/lib.php";

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

    # sort logs
    usort($logs, function($a, $b) {
        return strtotime($b["created_at"]) - strtotime($a["created_at"]);
    });

    #reverse order
    $logs = array_reverse($logs);


    foreach ($logs as $log)
    {
        view_lib::render_event_log($log);
    }

?>

<?php

    lib::footer_html();

