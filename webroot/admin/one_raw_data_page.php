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


    if (lib::sdefault("action") == "create_community")
    {
        if (!lib::current_user_is_admin())
        {
            ob_clean();
            header("Location: /user");
            exit();
        }
    }

    lib::header_html();

    $id = lib::i("id");

    $raw_data_page = lib::select(
        "SELECT * FROM RawDataPage WHERE id = :id",
        ["id" => $id]
    )[0] ?? throw new Exception("Raw data page not found");

?>
    <header>
    </header>
    <pre>
<?php
    print_r($raw_data_page);
    $content = json_decode($raw_data_page["content"], true);
    echo "<hr>";
    echo strlen($raw_data_page["content"]);
    $ziped = gzcompress($raw_data_page["content"]);
    echo "<hr>";
    echo strlen($ziped);
    echo "<hr>";
    print_r($content);
?>
</pre>


<?php

    lib::footer_html();
