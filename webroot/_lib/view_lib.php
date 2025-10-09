<?php

    class view_lib
    {
        static function render_event_log(array $eventlog): void
        {
            $user_id = $eventlog['user_id'];
            $community_id = $eventlog['community_id'];
            $event_type = $eventlog['event_type'];
            $priority = $eventlog['priority'];
            $event_description = $eventlog['event_description'];
            $event_data = $eventlog['event_data'];
            $trace = $eventlog['trace'];
            $created_at = $eventlog['created_at'];
            $updated_at = $eventlog['updated_at'];
            $done = $eventlog['done'];
            $id = $eventlog['id'];
            $allowed_priorities = ['info', 'warning', 'error', 'critical'];

            switch ($priority)
            {
                case 'info':
                    $header_style = 'color: green;';
                    break;
                case 'warning':
                    $header_style = 'color: orange;';
                    break;
                case 'error':
                    $header_style = 'color: red;';
                    break;
                case 'critical':
                    $header_style = 'color: darkred; font-weight: bold;';
                    break;
                default:
                    $header_style = 'color: black;'; // Default style for unknown priorities
            }
            ?>
            <article>
                <header style="<?= $header_style ?>">
                    <?= lib::format_ago($created_at) ?> |
                    <strong>[<?= htmlspecialchars($priority) ?>]</strong>
                    <?= htmlspecialchars($event_type) ?> -
                    <?= htmlspecialchars($event_description) ?>
                    <button
                        onclick="this.parentElement.nextElementSibling.style.display = (this.parentElement.nextElementSibling.style.display === 'none' ? 'block' : 'none');"
                        >
                        Show JSON
                    </button>
                </header>
                <pre style="display: none;">
                    <?= htmlspecialchars(json_encode($eventlog, JSON_PRETTY_PRINT)) ?>
                </pre>
            </article>
            <?php
        }
    }