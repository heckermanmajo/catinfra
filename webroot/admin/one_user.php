<?php

    use _lib\core\App;
    use _lib\views\AdminNavView;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";
    App::get_instance()->redirect_if_not_admin();

    echo new AdminNavView()->render();


    try
    {

        $id = lib::i("id");

        $user = lib::select(
            "SELECT * FROM User WHERE id = :id",
            ["id" => $id]
        );

        if (count($user) === 0)
        {
            throw new Exception("User not found");
        }
        else
        {
            $user = $user[0];
            ?>
            <h3> <?=$user["username"]?> </h3>
            <pre><?= json_encode($user, JSON_PRETTY_PRINT) ?></pre>
            <a href="/admin/edit_user.php?id=<?= $id ?>">
                Edit User
            </a>
            <?php
        }
    }
    catch (Throwable $t)
    {
        ?>
        <div style="color: crimson">
            <?= $t->getMessage() ?>
            <pre><?= $t->getTraceAsString() ?></pre>
        </div>
        <?php
    }

    lib::footer_html();

