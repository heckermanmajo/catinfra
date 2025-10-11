<?php

    /**
     * Fetches chat channels from Skool API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string|null $area Filter by area: 'direct' | 'groups' | null
     * @param bool $last Fetch last message (default: true)
     * @param bool $unread_only Fetch only unread channels (default: false)
     * @param int|null $offset Pagination offset
     * @param int|null $limit Pagination limit
     * @param bool $use_self Use self endpoint (default: true)
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_chat_channels(
        array   $user,
        array   $community,
        ?string $area = null,
        bool    $last = true,
        bool    $unread_only = false,
        ?int    $offset = null,
        ?int    $limit = null,
        bool    $use_self = true
    ): array
    {

        $base = $use_self
            ? "https://api.skool.com/self/chat-channels"
            : "https://api.skool.com/chat-channels";

        $params = [];
        if ($area !== null) {
            $params['area'] = $area;
        }
        $params['last'] = $last ? 'true' : 'false';
        $params['unread-only'] = $unread_only ? 'true' : 'false';
        if ($offset !== null) {
            $params['offset'] = (string)$offset;
        }
        if ($limit !== null) {
            $params['limit'] = (string)$limit;
        }

        $query_string = http_build_query($params);
        $url = $query_string ? "{$base}?{$query_string}" : $base;

        return fetch_lib::perform_request_to_skool($user, $community, $url, 'api');
    }
