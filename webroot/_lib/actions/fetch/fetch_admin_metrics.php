<?php

    use _lib\utils\SkoolFetcher;

    /**
     * Fetches admin metrics from Skool API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string $range_days Time range (e.g., "30d", "7d")
     * @param string $amt Aggregation method (e.g., "monthly", "daily")
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_admin_metrics(
        array  $user,
        array  $community,
        string $range_days = "30d",
        string $amt = "monthly"
    ): array
    {

        $group_id = $community['skool_id']
            ?? throw new ValueError("Missing skool_id in community data");

        $url = sprintf(
            "https://api.skool.com/groups/%s/admin-metrics?range=%s&amt=%s",
            urlencode($group_id),
            urlencode($range_days),
            urlencode($amt)
        );

        return SkoolFetcher::perform_request_to_skool($user, $community, $url, 'api');
    }
