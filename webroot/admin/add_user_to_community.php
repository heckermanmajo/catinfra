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

    if (lib::sdefault("action") === "add_user_to_community")
    {
        try
        {
            $user_id = lib::i("user_id");
            $community_id = lib::i("community_id");
            $relation_type = lib::s("relation_type");

            lib::insert(
                "UserCommunityRelation",
                [
                    "community_id" => $community_id,
                    "user_id" => $user_id,
                    "relation_type" => $relation_type,
                ]
            );

        }
        catch (Throwable $e)
        {
            $ADD_USER_ERROR = $e->getMessage();
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
    <?php if (isset($ADD_USER_ERROR)) { ?>
        <div style="color: red">
            <?=$ADD_USER_ERROR?>
        </div>
    <?php } ?>
<?php

    $already_added_users = lib::select(
        "SELECT * FROM User WHERE User.id in 
                    (SELECT user_id FROM UserCommunityRelation WHERE community_id = :community_id)",
        [
            "community_id" => $community["id"],
        ]
    );
    foreach ($already_added_users as $user)
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
            <div style='color: gray'>
                <?=$user['username']?> - Already in community as <?=$relation["relation_type"]?>
            </div>
        <?php
    }

    $not_added_users = lib::select(
        "SELECT * FROM User WHERE User.id not in 
                    (SELECT user_id FROM UserCommunityRelation WHERE community_id = :community_id)",
        [
            "community_id" => $community["id"],
        ]
    );

    foreach ($not_added_users as $user)
    {
        ?>
            <form method="post">
                <input type="hidden" name="action" value="add_user_to_community">
                <input type="hidden" name="user_id" value="<?=$user["id"]?>">
                <input type="hidden" name="community_id" value="<?=$community["id"]?>">
                <select name="relation_type">
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Add <?=$user["username"]?> to community</button>
            </form>
        <?php
    }

?>
<?php


    lib::footer_html();

