<?php

    use _lib\core\App;
    use _lib\core\RequestInput;
    use _lib\model\User;
    use _lib\requests\user\crud\CreateUserRequest;
    use _lib\utils\TimeData;
    use _lib\views\AdminNavView;
    use _lib\views\HtmlPage;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    App::get_instance()->redirect_if_not_admin();

    $user_creation_output = null;
    $input = new RequestInput();
    if ($input->s("action", '') == "create_user")
    {
        $random_username = "new_user_" . bin2hex(random_bytes(4));
        $random_password = bin2hex(random_bytes(8));

        $input->data['username'] = $random_username;
        $input->data['password'] = $random_password;
        $input->data['is_admin'] = 0;
        $input->data['email'] = '';
        $input->data['status'] = 'active';

        $request = new CreateUserRequest();
        $user_creation_output = $request->execute($input);

        if (!$user_creation_output->has_error())
        {
            $new_user_id = $user_creation_output->data['user_id'];
            ob_clean();
            header("Location: /admin/edit_user.php?user_id=" . $new_user_id);
            exit();
        }

    }

    $search = lib::sdefault("search");

    if ($search != "")
    {
        $search = "%$search%";
        $users = User::select(
            "SELECT * FROM User WHERE username LIKE :search OR email LIKE :search",
            [
                "search" => $search,
            ]
        );
    }
    else
    {
        $users = User::select(
            "SELECT * FROM User",
            []
        );
    }

    HtmlPage::header_html(
        css: "
            <style>
                body {
                   background-color: darkgray; 
                }    
            </style>
        "
    );

    echo new AdminNavView()->render();

?>

    <header style="margin-bottom: 5px;margin-top: 5px;">

        <form method="get" style="display:inline-block">
            <input type="text" name="search" placeholder="Search" value="<?= lib::sdefault("search") ?>">
            <input type="submit" value="Search">
        </form>

        <form method="post" style="display:inline-block">
            <input type="hidden" name="action" value="create_user">
            <button>Create new User</button>
        </form>

        <?php $user_creation_output?->put_error_card("create_user"); ?>

    </header>

<?php

    foreach ($users as $user)
    {
        ?>
        <div style="border: 1px black solid; margin-bottom: 5px; background-color: whitesmoke">
            <span> <small><?= new TimeData($user->created_at)->format_ago() ?></small></span>
            <br>
            <a href="/admin/one_user.php?id=<?= $user->id ?>" style="text-decoration: none; color: dodgerblue">
                <span> <?= $user->username ?> - <?= $user->email ?: '[no email]' ?> </span>
            </a>
        </div>
        <?php
    }

?>

<?php

    HtmlPage::footer_html();

