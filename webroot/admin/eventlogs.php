<?php

    use _lib\core\App;
    use _lib\views\AdminNavView;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    App::get_instance()->redirect_if_not_admin();

    lib::header_html();
    echo (new AdminNavView())->render();

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

