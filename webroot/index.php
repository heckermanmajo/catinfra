<?php

    use _lib\core\App;
    use _lib\core\RequestInput;
    use _lib\requests\user\LoginUserRequest;
    use _lib\views\HtmlPage;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    $in = RequestInput::get_last_input();
    $request_output = match ($in->action)
    {
        "login" => new LoginUserRequest()->execute($in),
        default => null,
    };


    if (App::get_instance()->somebody_is_logged_in())
    {
        ob_clean();
        if (App::get_instance()->current_user_is_admin())
        {
            header("Location: /admin");
        }
        else
        {
            header("Location: /user");
        }
        exit();
    }

?>

<?php HtmlPage::header_html(); ?>

<h4> Cat Brain stuff </h4>

<article>
    <form method="post">

        <?php $request_output?->put_error_card("login"); ?>

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
</article>

<?php HtmlPage::footer_html() ?>
