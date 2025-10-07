<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/actions/control/transform_lib.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/actions/transform/transform_test.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/actions/control/control_lib.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/actions/control/send_info_mail.php";
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


    if (lib::sdefault("action") === "send_test_email")
    {
        $community_id = lib::i("id");

        try
        {

            ob_start();

            $users_of_this_community = lib::select(
                "SELECT u.* FROM User u
                JOIN UserCommunityRelation ucr ON u.id = ucr.user_id
                WHERE ucr.community_id = :community_id AND u.is_admin = 1",
                ["community_id" => $community_id]
            );

            # check if we have done the analysis in the last 24 hours
            $analysis = lib::select(
                "SELECT * FROM AnalysisResultData WHERE community_id = :community_id AND created_at > :created_at",
                [
                    "community_id" => lib::i("id"),
                    "created_at" => time() - 24 * 60 * 60,
                ]
            );

            if (count($analysis) > 0)
            {
                $analysis = $analysis[0];
                $json = $analysis["content"];
                $data = json_decode($json, true);
            }
            else
            {
                $data = transform_test($community_id);
            }

            foreach ($users_of_this_community as $user)
            {

                echo "Sending test email to " . $user["email"] . "\n";

                control_lib::sendFramedMail(
                    $user["id"],
                    $community_id,
                    $user["email"],
                    "Testmail",
                    "<h2>Testmail</h2> <pre>" . print_r($data, true) . "</pre>",
                    "brain@catknows.com"
                );

            }

            lib::insert(
                "EventLog",
                [
                    "event_type" => "Called Send Test Email",
                    "priority" => "info",
                    "event_description" => ob_get_clean(),
                    "user_id" => lib::current_user()["id"],
                    "community_id" => $community_id,
                    "event_data" => [
                        "users_of_this_community" => $users_of_this_community,
                        "analysis" => count($analysis),
                        "data" => $data,
                    ],
                    "trace" => "",
                    "created_at" => time(),
                ],
                create_event_log: false
            );

        }catch (Throwable $t)
        {
            lib::insert(
                "EventLog",
                [
                    "event_type" => "FAILED: Called Send Test Email",
                    "priority" => "info",
                    "event_description" => ob_get_clean(),
                    "user_id" => lib::current_user()["id"],
                    "community_id" => $community_id,
                    "event_data" => "{}",
                    "trace" => $t->getMessage() . "\n" . $t->getTraceAsString(),
                    "created_at" => time(),
                ],
                create_event_log: false
            );
            throw $t;
        }

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

        <article>
            <form method="post">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="action" value="send_test_email">
                <button> Test Email senden</button>
            </form>
        </article>

        <ul>
            <li>
                SEND EMAIL TO ALL ADMINS OF COMMUNITY
            </li>
        </ul>
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

