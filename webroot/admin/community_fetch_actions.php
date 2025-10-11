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


    switch (lib::sdefault("action"))
    {
        case "fetch_about_page":

            try
            {
                $id = lib::i("id");
                $community = lib::select(
                    "SELECT * FROM Community WHERE id = :id",
                    ["id" => $id]
                )[0] ?? throw new Exception("Community not found");

                // Get admin user
                $user_rel = lib::select(
                    "SELECT * FROM UserCommunityRelation WHERE community_id = :community_id AND relation_type = 'admin'",
                    ["community_id" => $id]
                )[0] ?? throw new Exception("No admin user relation found for community");

                $user = lib::select(
                    "SELECT * FROM User WHERE id = :user_id",
                    ["user_id" => $user_rel["user_id"]]
                )[0] ?? throw new Exception("User not found");

                ob_start();
                $success = false;
                $data = null;
                $message = null;
                $trace = null;

                $data = fetch_about_page($user, $community);
                $success = true;

                $logs = ob_get_clean();

                // Insert into RawDataPage
                fetch_lib::insert_raw_page_data(
                    "about_page",
                    $community["skool_id"] ?? "",
                    json_encode($data ?? ["error" => $message]),
                    ["logs" => $logs, "trace" => $trace ?? ""],
                    $community["id"],
                    $success
                );
            }
            catch (Throwable $t)
            {
                $FETCH_ABOUT_PAGE_ERROR = $t->getMessage();
                $TRACE_ABOUT = $t->getTraceAsString();
            }
            break;

        case "fetch_admin_metrics":

            try
            {
                $id = lib::i("id");
                $community = lib::select(
                    "SELECT * FROM Community WHERE id = :id",
                    ["id" => $id]
                )[0] ?? throw new Exception("Community not found");

                // Get admin user
                $user_rel = lib::select(
                    "SELECT * FROM UserCommunityRelation WHERE community_id = :community_id AND relation_type = 'admin'",
                    ["community_id" => $id]
                )[0] ?? throw new Exception("No admin user relation found for community");

                $user = lib::select(
                    "SELECT * FROM User WHERE id = :user_id",
                    ["user_id" => $user_rel["user_id"]]
                )[0] ?? throw new Exception("User not found");

                $range_days = lib::sdefault("range_days", "30d");
                $amt = lib::sdefault("amt", "monthly");

                ob_start();
                $success = false;
                $data = null;
                $message = null;
                $trace = null;

                $data = fetch_admin_metrics($user, $community, $range_days, $amt);
                $success = true;

                $logs = ob_get_clean();

                // Insert into RawDataPage
                fetch_lib::insert_raw_page_data(
                    "admin_metrics",
                    $community["skool_id"] ?? "",
                    json_encode($data ?? ["error" => $message]),
                    ["logs" => $logs, "trace" => $trace ?? ""],
                    $community["id"],
                    $success
                );
            }
            catch (Throwable $t)
            {
                $FETCH_ADMIN_METRICS_ERROR = $t->getMessage();
                $TRACE = $t->getTraceAsString();
            }
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
        <hr>
        <h5> Fetch about page </h5>
        <form method="post">
            <?php if (isset($FETCH_ABOUT_PAGE_ERROR)) { ?>
                <div style="color: crimson">
                    <?= $FETCH_ABOUT_PAGE_ERROR ?>
                    <pre><?= $TRACE_ABOUT ?></pre>
                </div>
            <?php } ?>
            <input type="hidden" name="id" value="<?= $community["id"] ?>">
            <input type="hidden" name="action" value="fetch_about_page">
            <button> Fetch About Page</button>
        </form>

        <hr>
        <h5> Fetch admin page </h5>
        <form method="post">
            <?php if (isset($FETCH_ADMIN_METRICS_ERROR)) { ?>
                <div style="color: crimson">
                    <?= $FETCH_ADMIN_METRICS_ERROR ?>
                    <pre><?= $TRACE ?></pre>
                </div>
            <?php } ?>
            <input type="hidden" name="id" value="<?= $community["id"] ?>">
            <input type="hidden" name="action" value="fetch_admin_metrics">
            <label>
                Range:
                <input type="text" name="range_days" value="30d" placeholder="30d">
            </label>
            <label>
                Amount Type:
                <input type="text" name="amt" value="monthly" placeholder="monthly">
            </label>
            <button> Fetch Admin Metrics</button>
        </form>
        <hr>

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

