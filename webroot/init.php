<?php

    use _lib\core\App;
    use _lib\core\RequestInput;
    use _lib\requests\init\DatabaseInitRequest;
    use _lib\views\HtmlPage;

    require_once $_SERVER["DOCUMENT_ROOT"] . "/_lib/init.php";

    if (!App::is_localhost())
    {
        exit;
    }

    $in = RequestInput::get_last_input();
    $request_output = new DatabaseInitRequest()->execute($in);

    HtmlPage::header_html();

    if ($request_output->has_error())
    {
        echo "<h4>Database Initialization Error</h4>";
        echo "<pre>";
        var_dump($request_output->data);
        echo "</pre>";
    }
    else
    {
        echo "<h4>Database Initialized Successfully</h4>";
        echo "<pre>";
        var_dump($request_output->data);
        echo "</pre>";
    }

    HtmlPage::footer_html();