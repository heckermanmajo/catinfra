<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";

    if (isset($_POST['action']) && $_POST['action'] == 'login')
    {

        $username = lib::s('username');
        $password = lib::s('password');

        try
        {
            $user = lib::select("SELECT * FROM User WHERE username = :username", ["username" => $username]);
        }
        catch (Exception $e)
        {
            $LOGIN_ERROR = "Database error: " . $e->getMessage();
            goto END_LOGIN;
        }

        if (count($user) === 0)
        {
            $LOGIN_ERROR = "Invalid username or password";
            goto END_LOGIN;
        }

        $user = $user[0];
        if (!password_verify($password, $user['password_hash']))
        {
            $LOGIN_ERROR = "Invalid username or password";
            goto END_LOGIN;
        }

        $_SESSION['user_id'] = $user['id'];

        END_LOGIN:
    }

    if (lib::is_logged_in())
    {
        ob_clean();
        if (lib::current_user_is_admin())
        {
            header("Location: /admin");
        }
        else
        {
            header("Location: /user");
        }
        exit();
    }

    lib::header_html();

?>
    <h4> Cat Brain stuff </h4>
    <form method="post">

        <input type="hidden" name="action" value="login">

        <label>
            Username:
            <input type="text" name="username">
        </label>

        <label>
            Password:
            <input type="password" name="password">
        </label>

        <input type="submit" value="Submit">
    </form>
<?php

    lib::footer_html();
