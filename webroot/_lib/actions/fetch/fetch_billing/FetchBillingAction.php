<?php

    namespace _lib\actions\fetch\fetch_billing;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchBillingAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public string $vs = "1"
        ) {}

        protected function perform(): FetchBillingActionResult
        {
            $group_id = $this->community['skool_id']
                ?? throw new ValueError("Missing skool_id in community data");

            $url = sprintf(
                "https://api.skool.com/groups/%s/billing-dashboard?vs=%s",
                urlencode($group_id),
                urlencode($this->vs)
            );

            $response = SkoolFetcher::perform_request_to_skool(
                $this->user,
                $this->community,
                $url,
                'api'
            );

            $result = new FetchBillingActionResult(success: true);
            $result->message = "Successfully fetched billing data";
            $result->content = $response;

            return $result;
        }
    }
