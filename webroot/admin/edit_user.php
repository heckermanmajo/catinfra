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

    if (lib::sdefault("action") === "update_user")
    {
        try
        {

            $id = lib::i("id");
            $username = lib::s("username");
            $password = lib::s("password");
            $email = lib::s("email");
            $skool_user_id = lib::s("skool_user_id");
            $skool_auth_token = lib::s("skool_auth_token");
            $skool_client_id = lib::s("skool_client_id");
            $skool_ga = lib::s("skool_ga");
            $skool_ga_b9 = lib::s("skool_ga_b9");
            $skool_ga_d0xk = lib::s("skool_ga_d0xk");
            $skool_gcl_au = lib::s("skool_gcl_au");
            $skool_fbp = lib::s("skool_fbp");
            $skool_ajs_anon = lib::s("skool_ajs_anon");
            $skool_waf_cookie = lib::s("skool_waf_cookie");
            $skool_waf_header = lib::s("skool_waf_header");

            $user = lib::select(
                "SELECT * FROM User WHERE id = :id",
                ["id" => $id]
            )[0] ?? throw new Exception("User not found");

            $password_hash = null;

            if( $password !== "")
            {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
            }
            $password_hash = $password_hash ?? $user['password_hash'];

            lib::update(
                "User",
                [
                    "id" => $id,
                    "username" => $username,
                    "password_hash" => $password_hash,
                    "email" => $email,
                    "updated_at" => time(),
                    "skool_user_id" => $skool_user_id,
                    "SKOOL_AUTH_TOKEN" => $skool_auth_token,
                    "SKOOL_CLIENT_ID" => $skool_client_id,
                    "SKOOL_GA" => $skool_ga,
                    "SKOOL_GA_B9" => $skool_ga_b9,
                    "SKOOL_GA_D0XK" => $skool_ga_d0xk,
                    "SKOOL_GCL_AU" => $skool_gcl_au,
                    "SKOOL_FBP" => $skool_fbp,
                    "SKOOL_AJS_ANON" => $skool_ajs_anon,
                    "SKOOL_WAF_COOKIE" => $skool_waf_cookie,
                    "SKOOL_WAF_HEADER" => $skool_waf_header,
                ]
            );

        }
        catch (Throwable $t)
        {
            $UPDATE_USER_ERROR = $t->getMessage();
            $TRACE = $t->getTraceAsString();
        }
    }

    if (lib::sdefault("action") === "delete_user")
    {
        try
        {
            $id = lib::i("id");

            $user = lib::select(
                "SELECT * FROM User WHERE id = :id",
                ["id" => $id]
            )[0] ?? throw new Exception("User not found");

            lib::delete(
                "User",
                ["id" => $id]
            );

            ob_clean();
            header("Location: /admin/users.php");
            exit();
        }
        catch (Throwable $t)
        {
            $DELETE_USER_ERROR = $t->getMessage();
            $TRACE_DELETE = $t->getTraceAsString();
        }
    }

    lib::header_html(css: "
        <style>
            label{
              display: block;
            }
        </style>
    ");
    admin_lib::main_admin_nav();

    try
    {

        $id = lib::i("id");

        $user = lib::select(
            "SELECT * FROM User WHERE id = :id",
            ["id" => $id]
        );

        if (count($user) === 0)
        {
            throw new Exception("User not found");
        }
        else
        {
            $user = $user[0];
            ?>
            <form method="post">
                <h1> Edit User </h1>
                <?php if ($UPDATE_USER_ERROR ?? false) { ?>
                    <div style="color: crimson">
                        <?= $UPDATE_USER_ERROR ?>
                        <pre><?= $TRACE ?></pre>
                    </div>
                <?php } ?>
                <?php if ($DELETE_USER_ERROR ?? false) { ?>
                    <div style="color: crimson">
                        <?= $DELETE_USER_ERROR ?>
                        <pre><?= $TRACE_DELETE ?></pre>
                    </div>
                <?php } ?>
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" value="<?= $user["id"] ?>">

                <label>
                    <small>Username</small>
                    <input type="text" name="username" value="<?= $user['username'] ?>" placeholder="username">
                </label>

                <label>
                    <small>Password</small>
                    <input type="text" name="password" value="" placeholder="password">
                </label>

                <label>
                    <small>Email</small>
                    <input type="text" name="email" value="<?= $user['email'] ?>" placeholder="email">
                </label>

                <label>
                    <small>Skool User ID</small>
                    <input type="text" name="skool_user_id" value="<?= $user['skool_user_id'] ?>" placeholder="skool_user_id">
                </label>

                <label>
                    <small>Skool Auth Token</small>
                    <input type="text" name="skool_auth_token" value="<?= $user[strtoupper('skool_auth_token')] ?>" placeholder="skool_auth_token">
                </label>

                <label>
                    <small>Skool Client ID</small>
                    <input type="text" name="skool_client_id" value="<?= $user[strtoupper('skool_client_id')] ?>" placeholder="skool_client_id">
                </label>

                <label>
                    <small>Skool GA</small>
                    <input type="text" name="skool_ga" value="<?= $user[strtoupper('skool_ga')] ?>" placeholder="skool_ga">
                </label>

                <label>
                    <small>Skool GA B9</small>
                    <input type="text" name="skool_ga_b9" value="<?= $user[strtoupper('skool_ga_b9')] ?>" placeholder="skool_ga_b9">
                </label>

                <label>
                    <small>Skool GA D0XK</small>
                    <input type="text" name="skool_ga_d0xk" value="<?= $user[strtoupper('skool_ga_d0xk')] ?>" placeholder="skool_ga_d0xk">
                </label>

                <label>
                    <small>Skool GCL AU</small>
                    <input type="text" name="skool_gcl_au" value="<?= $user[strtoupper('skool_gcl_au')] ?>" placeholder="skool_gcl_au">
                </label>

                <label>
                    <small>Skool FBP</small>
                    <input type="text" name="skool_fbp" value="<?= $user[strtoupper('skool_fbp')] ?>" placeholder="skool_fbp">
                </label>

                <label>
                    <small>Skool AJS ANON</small>
                    <input type="text" name="skool_ajs_anon" value="<?= $user[strtoupper('skool_ajs_anon')] ?>" placeholder="skool_ajs_anon">
                </label>

                <label>
                    <small>Skool WAF COOKIE</small>
                    <input type="text" name="skool_waf_cookie" value="<?= $user[strtoupper('skool_waf_cookie')] ?>" placeholder="skool_waf_cookie">
                </label>

                <label>
                    <small>Skool WAF HEADER</small>
                    <input type="text" name="skool_waf_header" value="<?= $user[strtoupper('skool_waf_header')] ?>" placeholder="skool_waf_header">
                </label>

                <button> Update User</button>

            </form>

            <form method="post" onsubmit="return confirm('Are you sure you want to delete user <?= htmlspecialchars($user['username']) ?>? This action cannot be undone.');" style="margin-top: 10px;">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="id" value="<?= $user["id"] ?>">
                <button type="submit" style="background-color: crimson; color: white;"> Delete User</button>
            </form>

            <hr>

            <pre><?= json_encode($user, JSON_PRETTY_PRINT) ?></pre>

            <?php
        }
    }
    catch (Throwable $t)
    {
        ?>
        <div style="color: crimson">
            <?= $t->getMessage() ?>
            <pre><?= $t->getTraceAsString() ?></pre>
        </div>
        <?php
    }

    lib::footer_html();

