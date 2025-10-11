<?php

    use _lib\core\App;
    use _lib\views\HtmlPage;
    use _lib\views\AdminNavView;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    App::get_instance()->redirect_if_not_admin();

    if (lib::sdefault("action") === "logout")
    {
        session_destroy();
        ob_clean();
        header("Location: /");
        exit();
    }

    HtmlPage::header_html();

    echo (new AdminNavView())->render();
?>
    <hr>

    <form method="post">
        <input type="hidden" name="action" value="logout">
        <input type="submit" value="Logout">
    </form>

    <h4> ADMIN VIEW </h4>

    <ul>
        <li> add user</li>
        <li> Important events page -> news for admins</li>
        <li> Search for user/community</li>
        <li> edit user data</li>
        <li> Trigger fetch</li>
        <li> Search through fetched data</li>
        <li> Search raw data</li>
        <li> Download raw data as zip (different ways)</li>
        <li> View event logs/search them</li>
        <li> See and edit cron jobs; time strategy</li>
        <li> see, search sent emails</li>
        <li> Impersonate users</li>
    </ul>

<?php

    HtmlPage::footer_html();
