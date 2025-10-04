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

    function insert_raw_page_data(
        $page_type,
        $related_skoolid,
        $content,
        $trace_and_logs,
        $community_id,
        $success
    ){}

    switch (lib::sdefault("action"))
    {
        case "fetch_about_page":
            require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/actions/fetch/fetch_about_page.php";
            ob_start();
            try
            {
                $data = fetch_about_page();
            }catch (Throwable $t)
            {
                $message = $t->getMessage();
                $trace = $t->getTraceAsString();
            }
            $logs = ob_get_clean();
            # todo: insert data into raw page
            break;
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
        <pre><?= print_r($community, true) ?></pre>

        <form method="post">
            <input type="hidden" name="id" value="<?= $community["id"] ?>">
            <button> Fetch About Page </button>
        </form>

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

