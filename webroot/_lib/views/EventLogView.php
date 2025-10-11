<?php

    namespace _lib\views;

    use _lib\model\EventLog;
    use _lib\utils\TimeData;

    class EventLogView
    {

        function __construct(private EventLog $eventLog) {}

        function render(): string
        {
            $eventLog = $this->eventLog;

            $priority = $eventLog->priority;
            $header_style = match ($priority)
            {
                'info' => 'color: green;',
                'warning' => 'color: orange;',
                'error' => 'color: red;',
                'critical' => 'color: darkred; font-weight: bold;',
                default => 'color: black;',
            };

            $timeData = new TimeData($eventLog->created_at);

            ob_start();
            ?>
            <article>
                <header style="<?= $header_style ?>">
                    <?= $timeData->format_ago() ?> |
                    <strong>[<?= htmlspecialchars($eventLog->priority) ?>]</strong>
                    <?= htmlspecialchars($eventLog->event_type) ?> -
                    <?= htmlspecialchars($eventLog->event_description) ?>
                    <button
                        onclick="
                            this.parentElement.nextElementSibling.style.display
                                = (this.parentElement.nextElementSibling.style.display === 'none' ? 'block' : 'none');
                        "
                    >
                        Show JSON
                    </button>
                </header>
                <pre style="display: none;">
                    <?= htmlspecialchars(json_encode($eventLog, JSON_PRETTY_PRINT)) ?>
                </pre>
            </article>
            <?php
            return ob_get_clean();
        }
    }
