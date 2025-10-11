<?php

    use _lib\utils\SkoolFetcher;

    /**
     * Generic API call helper for "like" or similar endpoints
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string $api_url API endpoint URL (required)
     * @param string $method HTTP method (default: "GET")
     * @param array|null $payload Request payload for POST/PUT/PATCH methods
     * @param array|null $query Additional query parameters
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_like(
        array   $user,
        array   $community,
        string  $api_url,
        string  $method = "GET",
        ?array  $payload = null,
        ?array  $query = null
    ): array
    {

        if (empty($api_url)) {
            throw new ValueError("api_url is required");
        }

        $url = $api_url;
        if ($query) {
            $query_params = [];
            foreach ($query as $k => $v) {
                $query_params[$k] = (string)$v;
            }
            $query_string = http_build_query($query_params);
            $url = "{$api_url}?{$query_string}";
        }

        return SkoolFetcher::perform_request_to_skool(
            $user,
            $community,
            $url,
            'api',
            $method,
            $payload
        );
    }
