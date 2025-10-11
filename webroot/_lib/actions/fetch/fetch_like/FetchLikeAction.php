<?php

    namespace _lib\actions\fetch\fetch_like;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchLikeAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public string $api_url,
            public string $method = "GET",
            public ?array $payload = null,
            public ?array $query = null
        ) {}

        protected function perform(): FetchLikeActionResult
        {
            if (empty($this->api_url)) {
                throw new ValueError("api_url is required");
            }

            $url = $this->api_url;
            if ($this->query) {
                $query_params = [];
                foreach ($this->query as $k => $v) {
                    $query_params[$k] = (string)$v;
                }
                $query_string = http_build_query($query_params);
                $url = "{$this->api_url}?{$query_string}";
            }

            $response = SkoolFetcher::perform_request_to_skool(
                $this->user,
                $this->community,
                $url,
                'api',
                $this->method,
                $this->payload
            );

            $result = new FetchLikeActionResult(success: true);
            $result->message = "Successfully performed API request";
            $result->content = $response;

            return $result;
        }
    }
