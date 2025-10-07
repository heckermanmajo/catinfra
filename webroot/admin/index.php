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

    if (lib::sdefault("action") === "logout")
    {
        session_destroy();
        ob_clean();
        header("Location: /");
        exit();
    }



    lib::header_html();

    admin_lib::main_admin_nav();
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

    lib::footer_html();
