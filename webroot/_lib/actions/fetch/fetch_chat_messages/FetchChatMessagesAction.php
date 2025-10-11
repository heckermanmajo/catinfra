<?php

    namespace _lib\actions\fetch\fetch_chat_messages;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchChatMessagesAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public string $channel_id,
            public ?string $cursor = null,
            public ?int $offset = null,
            public ?int $limit = 50,
            public bool $use_self = true
        ) {}

        protected function perform(): FetchChatMessagesActionResult
        {
            if (empty($this->channel_id)) {
                throw new ValueError("channel_id is required");
            }

            $base = $this->use_self
                ? "https://api.skool.com/self/chat-messages"
                : "https://api.skool.com/chat-messages";

            $params = ['channel-id' => $this->channel_id];
            if ($this->limit !== null) {
                $params['limit'] = (string)$this->limit;
            }
            if ($this->cursor !== null) {
                $params['cursor'] = $this->cursor;
            }
            if ($this->offset !== null) {
                $params['offset'] = (string)$this->offset;
            }

            $query_string = http_build_query($params);
            $url = "{$base}?{$query_string}";

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'api');

            $result = new FetchChatMessagesActionResult(success: true);
            $result->message = "Successfully fetched chat messages";
            $result->content = $response;

            return $result;
        }
    }
