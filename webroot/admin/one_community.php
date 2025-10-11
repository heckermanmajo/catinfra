<?php

    use _lib\core\App;
    use _lib\views\AdminNavView;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    App::get_instance()->redirect_if_not_admin();

    $id = lib::i("id");

    $community = lib::select(
        "SELECT * FROM Community WHERE id = :id",
        ["id" => $id]
    )[0] ?? throw new Exception("Community not found");

    lib::header_html();
    echo (new AdminNavView())->render();

?>
    <h4><?= $community["tenant_name"] ?></h4>
    <hr>

    <nav>

        <a href="/admin/add_user_to_community.php?id=<?= $id ?>">
            Add User to Community
        </a> &nbsp; | &nbsp;

        <a href="/admin/kick_user_from_community.php?id=<?= $id ?>">
            Kick User from Community
        </a> &nbsp; | &nbsp;

        <a href="/admin/edit_community.php?id=<?= $id ?>">
            Update Community Data
        </a> &nbsp; | &nbsp;

        <a href="/admin/community_fetch_actions.php?id=<?= $id ?>">
            Data Fetch Action Interface
        </a> &nbsp; | &nbsp;

        <a href="/admin/community_analysis_actions.php?id=<?= $id ?>">
            Analysis Action Interface
        </a> &nbsp; | &nbsp;

        <a href="/admin/community_control_actions.php?id=<?= $id ?>">
            Control Action Interface
        </a> &nbsp; | &nbsp;

    </nav>


    <hr>

    <nav>
        <a href="/admin/one_community.php?id=<?= $id ?>"> Overview </a> &nbsp; | &nbsp;
        <a href="/admin/one_community.php?id=<?= $id ?>&p=raw"> Raw Data </a> &nbsp; | &nbsp;
        <a href="/admin/one_community.php?id=<?= $id ?>&p=logs"> Eventlogs </a> &nbsp; | &nbsp;
        <a href="/admin/one_community.php?id=<?= $id ?>&p=analysis"> Analysis Results </a> &nbsp; | &nbsp;
        <a href="/admin/one_community.php?id=<?= $id ?>&p=email"> Emails </a> &nbsp; | &nbsp;
    </nav>
    <hr>

<?php switch ($_GET["p"] ?? '')
{

    case "raw":
        ?>
        <h4>RAW DATA</h4>
        <ul>
            <li> Raw Fetched data</li>
            <?php
                $raw_fetched_data_pages = lib::select(
                    "SELECT id, created_at, page_type FROM RawDataPage 
                             WHERE community_id = :community_id",
                    ["community_id" => $id]
                );

                # order by created_at desc
                usort($raw_fetched_data_pages, function ($a, $b)
                {
                    return $b["created_at"] - $a["created_at"];
                });

                foreach ($raw_fetched_data_pages as $page)
                {
                    ?>
                    <li>
                        <a href="/admin/one_raw_data_page.php?id=<?= $page["id"] ?>">
                            <?= $page["created_at"] ?> - <?= $page["page_type"] ?>
                        </a>
                    </li>
                    <?php
                }
            ?>
        </ul>
        <?php
        break;

    case "logs":
        $eventlogs = lib::select(
            "SELECT * FROM EventLog WHERE community_id = :community_id ORDER BY created_at DESC",
            ["community_id" => $id]
        );
        foreach ($eventlogs as $eventlog)
        {
            ?>
            <article>
                <pre>
                    <?= htmlspecialchars(json_encode($eventlog, JSON_PRETTY_PRINT)) ?>
                </pre>
            </article>
            <?php
        }

        break;

    case "transform":
        ?>
        <h4>ANALYSIS RESULTS</h4>
        <?php
        break;

    case "email":
        ?>
        <h4>EMAILS</h4>
        <?php
        break;

    default:
        ?>
        <h4>OVERVIEW</h4>
        <?php
        break;
} ?>


<?php


    lib::footer_html();

