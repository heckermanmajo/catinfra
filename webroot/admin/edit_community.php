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

    if (lib::sdefault("action") === "edit_community")
    {
        try
        {

            $id = lib::i("id");
            $tenant_name = lib::s("tenant_name");
            $tenant_slug = lib::s("tenant_slug");
            $skool_id = lib::s("skool_id");
            $primary_community = lib::s("primary_community");
            $created_by_user_id = lib::s("created_by_user_id");
            $created_by_user_name = lib::s("created_by_user_name");
            $updated_at = time();

            lib::update(
                "Community",
                [
                    "id" => $id,
                    "tenant_name" => $tenant_name,
                    "tenant_slug" => $tenant_slug,
                    "skool_id" => $skool_id,
                    "primary_community" => $primary_community,
                    "created_by_user_id" => $created_by_user_id,
                    "created_by_user_name" => $created_by_user_name,
                    "updated_at" => $updated_at,
                ]
            );

            lib::event_log(
                "COMMUNITY_UPDATED",
                "info",
                "Community data were updated.",
                [
                    "community_id" => $id,
                    "updated_by_user_id" => lib::current_user()["id"],
                ]
            );

        }
        catch (Throwable $t)
        {
            $UPDATE_ERROR = $t->getMessage();
            $TRACE = $t->getTraceAsString();
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
        <h1>EDIT COMMUNITY</h1>
        <form method="post">
            <?php if ($UPDATE_ERROR ?? false) { ?>
                <div style="color: crimson">
                    <?= $UPDATE_ERROR ?>
                    <pre><?= $TRACE ?></pre>
                </div>
            <?php } ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($community["id"]) ?>">
            <input type="hidden" name="action" value="edit_community">
            <input type="text" name="tenant_name" value="<?= htmlspecialchars($community["tenant_name"]) ?>"
                   placeholder="Tenant Name">
            <input type="text" name="tenant_slug" value="<?= htmlspecialchars($community["tenant_slug"]) ?>"
                   placeholder="Tenant Slug">
            <input type="text" name="skool_id" value="<?= htmlspecialchars($community["skool_id"]) ?>"
                   placeholder="Skool ID">
            <input type="text" name="primary_community" value="<?= htmlspecialchars($community["primary_community"]) ?>"
                   placeholder="Primary Community">
            <input type="text" name="created_by_user_id"
                   value="<?= htmlspecialchars($community["created_by_user_id"]) ?>" placeholder="Created By User ID">
            <input type="text" name="created_by_user_name"
                   value="<?= htmlspecialchars($community["created_by_user_name"]) ?>"
                   placeholder="Created By User Name">
            <button type="submit">Save Changes</button>

        </form>
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

