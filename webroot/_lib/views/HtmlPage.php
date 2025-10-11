<?php

    namespace _lib\views;

    class HtmlPage {

        static function header_html(string $title = 'App', string $css = "", string $onload = ""): void
        {
            ?>
            <!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="/_lib/lib.css">
                <script src="/_lib/lib.js" defer></script>
                <?= $onload ?>
                <?= $css ?>
                <title><?php echo htmlspecialchars($title); ?></title>
            </head>
            <body>
            <?php
        }

        static function footer_html(): void
        {
            ?>
            </body>
            </html>
            <?php
        }

    }