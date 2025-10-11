<?php

    namespace _lib\core;

    use JsonSerializable;
    use Throwable;

    abstract class Action implements JsonSerializable
    {

        function jsonSerialize(): mixed
        {
            return get_object_vars($this);
        }

        abstract protected function perform(): ActionResult;

        function execute(): ActionResult
        {

            try
            {

                ob_start();

                $app = App::get_instance();

                $start_time = microtime(true);

                try
                {
                    $result = $this->perform();
                }
                catch (Throwable $e)
                {
                    $result = new ActionResult(success: false);
                    $result->message = $e->getMessage();
                    $result->trace = $e->getTraceAsString();
                }

                $end_time = microtime(true);
                $result->ended_at = $end_time;
                $result->started_at = $start_time;
                $result->action_name = get_class($this);

                if ($app->somebody_is_logged_in())
                {
                    $result->user_id = $app->get_current_user()->id;
                }
                else
                {
                    $result->user_id = -1;
                }

                try
                {
                    $result->save_to_db();
                }
                catch (Throwable $e)
                {
                    $app->extra_error_handling(
                        "Failed to save action result to database: " . $e->getMessage(),
                        $e,
                        ["result" => $result]
                    );
                }

                $result->buffer = ob_get_clean();

                return $result;

            }
            catch (Throwable $e)
            {

                $app = App::get_instance();
                $app->extra_error_handling(
                    "Failed badly to execute action: " . $e->getMessage(),
                    $e,
                    ["action" => get_class($this)]
                );
                throw $e;

            } # end of try-catch

        } # end of execute()

    } # end of Action class