<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/lib.php";

    if (!lib::is_logged_in())
    {
        ob_clean();
        header("Location: /");
        exit();
    }

    if (lib::sdefault("action") === "logout")
    {
        session_destroy();
        ob_clean();
        header("Location: /");
        exit();
    }

    lib::header_html();

    $community = lib::select(
        "SELECT * FROM Community WHERE id = :id",
        ["id" => lib::i("id")]
    );

    $emails = lib::select(
        "SELECT * FROM SentMail WHERE community_id = :community_id ORDER BY created_at DESC",
        ["community_id" => lib::i("id")]
    );


?>

    <header>
        <form method="post" style="display: inline-block">
            <input type="hidden" name="action" value="logout">
            <input type="submit" value="Logout">
        </form>
    </header>
    <pre><?= print_r($community, true) ?></pre>
    <pre>
        One community:
        - list of received emails -> reports
        - more special views added over time
        - display Analsis-results in a beautiful way
    </pre>

<?php
    foreach ($emails as $email)
    {
        ?>
        <article>
            <h1><?= $email["subject"] ?></h1>
            <?= $email["body"] ?>
        </article>
        <?php
    }
?>


<?php

    lib::footer_html();
