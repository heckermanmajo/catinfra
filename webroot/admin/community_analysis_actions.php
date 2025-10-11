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


    try
    {
        $id = lib::i("id");

        ?>
        <a href="/admin/one_community.php?id=<?= $id ?>">Back</a>
        <?php

        $community = lib::select(
            "SELECT * FROM Community WHERE id = :id",
            ["id" => $id]
        )[0] ?? throw new Exception("Community not found");

        ?>
        <ul>
            <li>
                TRIGGER ANALYSIS ACTIONS -> display the last updated action result in the db...
            </li>
        </ul>
        <pre><?= print_r($community, true) ?></pre>
        <?php

    }
    catch (Throwable $t)
    {
        admin_lib::main_admin_nav();
        ?>
        <div style="color: crimson">
            <?= $t->getMessage() ?>
            <pre><?= $t->getTraceAsString() ?></pre>
        </div>
        <?php
    }

    lib::footer_html();

