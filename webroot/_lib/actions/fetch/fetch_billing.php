<?php

    use _lib\utils\SkoolFetcher;

    /**
     * Fetches billing dashboard data from Skool API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string $vs Version string (default: "1")
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_billing(
        array  $user,
        array  $community,
        string $vs = "1"
    ): array
    {

        $group_id = $community['skool_id']
            ?? throw new ValueError("Missing skool_id in community data");

        $url = sprintf(
            "https://api.skool.com/groups/%s/billing-dashboard?vs=%s",
            urlencode($group_id),
            urlencode($vs)
        );

        return SkoolFetcher::perform_request_to_skool($user, $community, $url, 'api');
    }
