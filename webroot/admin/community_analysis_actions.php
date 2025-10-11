<?php

    use _lib\core\App;
    use _lib\views\AdminNavView;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    App::get_instance()->redirect_if_not_admin();


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
        echo (new AdminNavView())->render();
        ?>
        <div style="color: crimson">
            <?= $t->getMessage() ?>
            <pre><?= $t->getTraceAsString() ?></pre>
        </div>
        <?php
    }

    lib::footer_html();

