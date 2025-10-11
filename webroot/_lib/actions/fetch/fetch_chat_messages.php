<?php

    /**
     * Fetches chat messages for a channel from Skool API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string $channel_id Channel ID (required)
     * @param string|null $cursor Pagination cursor
     * @param int|null $offset Pagination offset
     * @param int|null $limit Pagination limit (default: 50)
     * @param bool $use_self Use self endpoint (default: true)
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_chat_messages(
        array   $user,
        array   $community,
        string  $channel_id,
        ?string $cursor = null,
        ?int    $offset = null,
        ?int    $limit = 50,
        bool    $use_self = true
    ): array
    {

        if (empty($channel_id)) {
            throw new ValueError("channel_id is required");
        }

        $base = $use_self
            ? "https://api.skool.com/self/chat-messages"
            : "https://api.skool.com/chat-messages";

        $params = ['channel-id' => $channel_id];
        if ($limit !== null) {
            $params['limit'] = (string)$limit;
        }
        if ($cursor !== null) {
            $params['cursor'] = $cursor;
        }
        if ($offset !== null) {
            $params['offset'] = (string)$offset;
        }

        $query_string = http_build_query($params);
        $url = "{$base}?{$query_string}";

        return fetch_lib::perform_request_to_skool($user, $community, $url, 'api');
    }
