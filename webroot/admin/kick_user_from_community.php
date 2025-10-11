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

    if (lib::sdefault("action") === "kick_user_from_community")
    {
        try
        {
            $user_id = lib::i("user_id");
            $community_id = lib::i("community_id");

            lib::delete(
                "UserCommunityRelation",
                [
                    "community_id" => $community_id,
                    "user_id" => $user_id,
                ]
            );

        }
        catch (Throwable $e)
        {
            $KICK_USER_ERROR = $e->getMessage();
            $TRACE = $e->getTraceAsString();
        }
    }

    $id = lib::i("id");

    $community = lib::select(
        "SELECT * FROM Community WHERE id = :id",
        ["id" => $id]
    )[0] ?? throw new Exception("Community not found");

    lib::header_html();
    admin_lib::main_admin_nav();

?>
    <h4><?= $community["tenant_name"] ?></h4>
    <?php if (isset($KICK_USER_ERROR)) { ?>
        <div style="color: red">
            <?=$KICK_USER_ERROR?>
        </div>
    <?php } ?>
<?php

    $users_in_community = lib::select(
        "SELECT * FROM User WHERE User.id in
                    (SELECT user_id FROM UserCommunityRelation WHERE community_id = :community_id)",
        [
            "community_id" => $community["id"],
        ]
    );

    foreach ($users_in_community as $user)
    {
        $relation = lib::select(
            "SELECT * FROM UserCommunityRelation
                        WHERE community_id = :community_id AND user_id = :user_id",
            [
                "community_id" => $community["id"],
                "user_id" => $user["id"],
            ]
        )[0] ?? throw new Exception("Relation not found");

        ?>
            <form method="post">
                <input type="hidden" name="action" value="kick_user_from_community">
                <input type="hidden" name="user_id" value="<?=$user["id"]?>">
                <input type="hidden" name="community_id" value="<?=$community["id"]?>">
                <button type="submit">Kick <?=$user["username"]?> (<?=$relation["relation_type"]?>)</button>
            </form>
        <?php
    }

?>
<?php


    lib::footer_html();

