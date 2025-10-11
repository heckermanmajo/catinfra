<?php

    namespace _lib\utils;

    class TimeData
    {

        function __construct(private int $timestamp) {}

        function format_ago(): string
        {
            $periods = ['second', 'minute', 'hour', 'day', 'month', 'year', 'decade'];
            $lengths = ['60', '60', '24', '7', '4.35', '12', '10'];
            $now = time();
            $difference = $now - $this->timestamp;
            $tense = 'ago';

            for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++)
            {
                $difference /= $lengths[$j];
            }

            $difference = round($difference);

            if ($difference != 1)
            {
                $periods[$j] .= 's';
            }

            return "$difference $periods[$j] $tense";
        }

        function format_default(): string
        {
            return date('Y-m-d H:i:s', $this->timestamp);
        }

    }