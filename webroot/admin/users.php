<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";

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

    if (lib::sdefault("action") == "create_user")
    {
        try
        {
            $new_user_id = lib::insert(
                "User",
                [
                    "username" => "new_user_" . bin2hex(random_bytes(4)),
                    "password_hash" => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                    "is_admin" => 0,
                    "created_at" => time(),
                ]
            );
            ob_clean();
            header("Location: /admin/edit_user.php?user_id=" . $new_user_id);
            exit();
        }
        catch (Throwable $t)
        {
            $CREATE_USER_ERROR = $t->getMessage();
            $TRACE = $t->getTraceAsString();
        }
    }

    $search = lib::sdefault("search");

    if($search != "")
    {
        $search = "%$search%";
        $users = lib::select(
            "SELECT * FROM User WHERE username LIKE :search OR email LIKE :search",
            [
                "search" => $search,
            ]
        );
    }
    else
    {
        $users = lib::select(
            "SELECT * FROM User",
            []
        );
    }

    lib::header_html(
        css: "
            <style>
                body {
                   background-color: darkgray; 
                }    
            </style>
        "
    );
    admin_lib::main_admin_nav();
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
            <?php
                if ($CREATE_USER_ERROR ?? false)
                {
                    ?>
                    <div style="color: crimson">
                        <pre><?=$CREATE_USER_ERROR?></pre>
                        <pre><?=$TRACE?></pre>
                    </div>
                    <?php
                }

            ?>
        </header>
    <?php

    foreach ($users as $user)
    {
        ?>
        <div style="border: 1px black solid; margin-bottom: 5px; background-color: whitesmoke">
            <span> <small><?=lib::format_ago($user["created_at"] ?? time() )?></small></span>
            <br>
            <a href="/admin/one_user.php?id=<?=$user['id']?>" style="text-decoration: none; color: dodgerblue">
                <span> <?=$user["username"]?> - <?= $user["email"] ?: '[no email]'?> </span>
            </a>
        </div>
        <?php
    }

?>

<?php

    lib::footer_html();

