<?php

    namespace _lib\actions\fetch\fetch_chat_channels;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;

    final class FetchChatChannelsAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public ?string $area = null,
            public bool $last = true,
            public bool $unread_only = false,
            public ?int $offset = null,
            public ?int $limit = null,
            public bool $use_self = true
        ) {}

        protected function perform(): FetchChatChannelsActionResult
        {
            $base = $this->use_self
                ? "https://api.skool.com/self/chat-channels"
                : "https://api.skool.com/chat-channels";

            $params = [];
            if ($this->area !== null) {
                $params['area'] = $this->area;
            }
            $params['last'] = $this->last ? 'true' : 'false';
            $params['unread-only'] = $this->unread_only ? 'true' : 'false';
            if ($this->offset !== null) {
                $params['offset'] = (string)$this->offset;
            }
            if ($this->limit !== null) {
                $params['limit'] = (string)$this->limit;
            }

            $query_string = http_build_query($params);
            $url = $query_string ? "{$base}?{$query_string}" : $base;

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'api');

            $result = new FetchChatChannelsActionResult(success: true);
            $result->message = "Successfully fetched chat channels";
            $result->content = $response;

            return $result;
        }
    }
