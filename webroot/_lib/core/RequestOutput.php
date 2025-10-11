<?php

    namespace _lib\core;

    use _lib\model\RequestData;
    use JsonException;

    final class RequestOutput
    {
        public string $logs = "";


        function __construct(
            private(set) array|UserError|\Throwable $data
        ) {}

        function has_error(): bool
        {
            return $this->data instanceof UserError || $this->data instanceof \Throwable;
        }

        function is_action(string $action, array $other_fields = [])
        {
            $input = RequestInput::get_last_input();

            if ($input->action === $action)
            {

                if (count($other_fields) > 0)
                {
                    foreach ($other_fields as $field => $value)
                    {
                        $okay = ($input->has($field) && $input->$field === $value);
                        if (!$okay)
                        {
                            return false;
                        }
                    }
                }
                return true;
            }
            else
            {
                return false;
            }

        }

        function put_error_card(string $action, array $other_fields = []) {
            $data_is_error = $this->data instanceof \Throwable;
            if ($this->is_action($action, $other_fields) && $data_is_error)
            {
                ?>
                <article class="error-card">
                    <header>
                        <h1>Error</h1>
                        <p>
                            <?php echo $this->data->getMessage(); ?>
                        </p>
                    </header>
                    <main>
                        <pre>
                            <?php echo $this->data->getTraceAsString(); ?>
                        </pre>
                    </main>
                </article>
                <?php
            }
        }

        function get_as_json()
        {
            try
            {

                if ($this->data instanceof UserError)
                {
                    http_response_code(400);
                    header('Content-Type: application/json; charset=utf-8');

                    echo json_encode(
                        [
                            "logs" => $this->logs,
                            "message" => $this->data->getMessage(),
                            "trace" => $this->data->getTraceAsString(),
                        ],
                        JSON_THROW_ON_ERROR
                    );
                }
                elseif ($this->data instanceof \Throwable)
                {
                    http_response_code(500);
                    header('Content-Type: application/json; charset=utf-8');

                    echo json_encode(
                        [
                            "logs" => $this->logs,
                            "message" => $this->data->getMessage(),
                            "trace" => $this->data->getTraceAsString(),
                        ],
                        JSON_THROW_ON_ERROR
                    );
                }
                else
                {
                    http_response_code(200);
                    header('Content-Type: application/json; charset=utf-8');

                    echo json_encode(
                        [
                            "logs" => $this->logs,
                            "data" => $this->data,
                        ],
                        JSON_THROW_ON_ERROR
                    );
                }


            }
            catch (JsonException $exception)
            {

                http_response_code(500);
                echo "Internal server error; failed to encode JSON response.";
                echo "JSON error: " . $exception->getMessage();

            }
        }

        function to_db(): void {
            $request_data = new RequestData();
            $request_data->request_data_json = json_encode($this->data);
            $request_data->logs = $this->logs;
            $request_data->user_error = $this->data instanceof UserError ? 1 : 0;
            $request_data->system_error = $this->data instanceof \Throwable ? 1 : 0;
            $request_data->error_message = $this->data instanceof \Throwable ? $this->data->getMessage() : "";
            $request_data->trace = $this->data instanceof \Throwable ? $this->data->getTraceAsString() : "";
            $request_data->json_response = json_encode($this->data);
            $request_data->save();
        }

    }