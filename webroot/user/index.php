<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";

    if (!lib::is_logged_in())
    {
        ob_clean();
        header("Location: /");
        exit();
    }

    lib::header_html();

    $communities = lib::select(
        "SELECT * FROM Community WHERE id in (
                SELECT community_id FROM UserCommunityRelation WHERE user_id = :user_id)",
        ["user_id" => lib::current_user()["id"]]
    );

    if (count($communities) === 1)
    {
        ob_clean();
        header("Location: /user/community.php?id=" . $communities[0]["id"]);
        exit();
    }

    foreach ($communities as $community)
    {
        ?>
        <article>
            <a href="/user/community.php?id=<?= $community["id"] ?>">
                <?= $community["tenant_name"] ?>
            </a>
        </article>
        <?php
    }

    lib::footer_html();
