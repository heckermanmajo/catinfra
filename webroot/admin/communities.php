<?php

    use _lib\core\App;
    use _lib\views\AdminNavView;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    App::get_instance()->redirect_if_not_admin();

    if (lib::sdefault("action") == "create_community")
    {
        try
        {
            $new_community_id = lib::insert(
                "Community",
                [
                    "tenant_name" => "New Community " . bin2hex(random_bytes(4)),
                    "created_at" => time(),
                ]
            );
            ob_clean();
            header("Location: /admin/edit_community.php?id=" . $new_community_id);
            exit();
        }
        catch (Throwable $t)
        {
            $CREATE_USER_ERROR = $t->getMessage();
            $TRACE = $t->getTraceAsString();
        }
    }


    lib::header_html();
    echo (new AdminNavView())->render();


?>
    <header style="margin-bottom: 5px;margin-top: 5px;">
        <form method="get" style="display:inline-block">
            <input type="text" name="search" placeholder="Search" value="<?= lib::sdefault("search") ?>">
            <input type="submit" value="Search">
        </form>
        <form method="post" style="display:inline-block">
            <input type="hidden" name="action" value="create_community">
            <button>Create new Community</button>
        </form>
        <?php
            if ($CREATE_COMMUNITY_ERROR ?? false)
            {
                ?>
                <div style="color: crimson">
                    <pre><?= $CREATE_COMMUNITY_ERROR ?></pre>
                    <pre><?= $TRACE ?></pre>
                </div>
                <?php
            }

        ?>
    </header>

    <br>

<?php

    $search = lib::sdefault("search");

    if($search != "")
    {
        $search = "%$search%";
        $communities = lib::select(
            "SELECT * FROM Community WHERE tenant_name LIKE :search OR tenant_slug LIKE :search",
            [
                "search" => $search,
            ]
        );
    }
    else
    {
        $communities = lib::select(
            "SELECT * FROM Community",
            []
        );
    }


    foreach ($communities as $community)
    {

        ?>
        <div style="border: 1px solid black; margin: 10px; padding: 10px;">
            <?php
                $users = lib::select(
                    "SELECT * FROM User WHERE User.id in 
                    (SELECT user_id FROM UserCommunityRelation WHERE community_id = :community_id)",
                    [
                        "community_id" => $community["id"],
                    ]
                );
                foreach ($users as $user)
                {
                    $relation = lib::select(
                        "SELECT * FROM UserCommunityRelation
                        WHERE community_id = :community_id AND user_id = :user_id",
                        [
                            "community_id" => $community["id"],
                            "user_id" => $user["id"],
                        ]
                    )[0] ?? throw new Exception("Relation not found");

                    if ($relation["relation_type"] == "admin")
                    {
                        ?>
                        <a href="/admin/one_user.php?id=<?= $user['id'] ?>">
                            <span style='color: green'><?= $user['username'] ?> - Admin</span> &nbsp;
                        </a>
                        <?php
                    }
                    else
                    {
                        ?>
                        <a href="/admin/one_user.php?id=<?= $user['id'] ?>">
                            <span style='color: red'><?= $user['username'] ?>- USER</span>
                        </a>
                        <?php
                    }

                }

            ?>
            <br>
            <a href="/admin/one_community.php?id=<?= $community["id"] ?>">
                <span> <?= htmlspecialchars($community["tenant_name"]) ?> </span>
            </a>
        </div>
        <?php
    }

?>

<?php

    lib::footer_html();

