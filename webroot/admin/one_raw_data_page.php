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

    $community = lib::select(
        "SELECT * FROM Community WHERE id = :id",
        ["id" => $raw_data_page["community_id"]]
    )[0] ?? throw new Exception("Community not found");

?>
    <header>
        <a href="/admin/one_community.php?id=<?= $raw_data_page["community_id"] ?>&p=raw">
            Back
        </a>
    </header>

    <span style="font-size: 22px;">
        <?= $community["tenant_name"] ?> -
        <?= $raw_data_page["page_type"] ?> - <?= lib::format_ago($raw_data_page["created_at"]) ?>
    </span>
    <pre><?php
            $no_content_no_logs = [...$raw_data_page];
            unset($no_content_no_logs["content"]);
            unset($no_content_no_logs["logs"]);
            print_r($no_content_no_logs);
            #print_r($raw_data_page);
            $content = json_decode($raw_data_page["content"], true);
            echo "<hr>";
            echo strlen($raw_data_page["content"]);
            $ziped = gzcompress($raw_data_page["content"]);
            echo "<hr>";
            echo strlen($ziped);
            echo "<hr>";
            echo admin_lib::render_collapsible_list($content);
        ?>
</pre>


<?php

    lib::footer_html();
