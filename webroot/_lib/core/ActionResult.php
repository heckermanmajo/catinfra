<?php

    namespace _lib\core;

    use _lib\model\ActionResultData;

    class ActionResult
    {
        public int $started_at;
        public int $ended_at;
        public string $message;
        public string $buffer;
        public string $trace;
        public string $action_name;
        public int $user_id;

        function __construct(public bool $success) {}

        function save_to_db(): void
        {
           $db_action_result_data = new ActionResultData();
           $db_action_result_data->action_name = $this->action_name;
           $db_action_result_data->user_id = $this->user_id;
           $db_action_result_data->started_at = $this->started_at;
           $db_action_result_data->ended_at = $this->ended_at;
           $db_action_result_data->success = $this->success;
           $db_action_result_data->message = $this->message;
           $db_action_result_data->trace = $this->trace;
           $db_action_result_data->buffer = $this->buffer;
           $db_action_result_data->data = json_encode($this, JSON_THROW_ON_ERROR);
           $db_action_result_data->save();
        }

        static function from_database(ActionResultData $db_result_data): static
        {
            $specific_result = new static(
                $db_result_data->success
            );
            $specific_result->action_name = $db_result_data->action_name;
            $specific_result->user_id = $db_result_data->user_id;
            $specific_result->started_at = $db_result_data->started_at;
            $specific_result->ended_at = $db_result_data->ended_at;
            $specific_result->message = $db_result_data->message;
            $specific_result->trace = $db_result_data->trace;
            $specific_result->buffer = $db_result_data->buffer;
            $data = json_decode($db_result_data->data, true, 512, JSON_THROW_ON_ERROR);
            foreach ($data as $key => $value)
            {
                if (!property_exists($specific_result, $key))
                {
                    $specific_result->$key = $value;
                }
            }
            return $specific_result;
        }

        function was_successful(): bool
        {
            return $this->success;
        }

        function dump_as_log(): void
        {
            echo "ActionResult: " . ($this->success ? "SUCCESS" : "FAILED") . "\n";
            echo "Message: " . $this->message . "\n";
            echo "Trace: " . $this->trace . "\n";
            echo "Action: " . $this->action_name . "\n";
            echo "User: " . $this->user_id . "\n";
            echo "Started at: " . date('Y-m-d H:i:s', $this->started_at) . "\n";
            echo "Ended at: " . date('Y-m-d H:i:s', $this->ended_at) . "\n";
            echo "Buffer: " . $this->buffer . "\n";
        }

        function throw_if_not_successful(): void
        {
            if (!$this->success)
            {
                throw new \Exception("ACTION FAILED: " . $this->message);
            }
        }

    }