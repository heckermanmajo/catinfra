<?php

    namespace _lib\actions\fetch\fetch_admin_metrics;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchAdminMetricsAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public string $range_days = "30d",
            public string $amt = "monthly"
        ) {}

        protected function perform(): FetchAdminMetricsActionResult
        {
            $group_id = $this->community['skool_id']
                ?? throw new ValueError("Missing skool_id in community data");

            $url = sprintf(
                "https://api.skool.com/groups/%s/admin-metrics?range=%s&amt=%s",
                urlencode($group_id),
                urlencode($this->range_days),
                urlencode($this->amt)
            );

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'api');

            $result = new FetchAdminMetricsActionResult(success: true);
            $result->message = "Successfully fetched admin metrics";
            $result->content = $response;

            return $result;
        }
    }
