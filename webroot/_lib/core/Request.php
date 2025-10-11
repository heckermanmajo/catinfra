<?php

    namespace _lib\core;

    abstract class Request
    {

        abstract protected function _execute(RequestInput $input): RequestOutput;

        function execute(RequestInput $input): RequestOutput
        {

            App::$current_request = static::class;

            ob_start();

            try
            {
                $output = $this->_execute($input);
            }

            catch (UserError $e)
            {
                $output = new RequestOutput($e);
            }

            catch (\Throwable $t)
            {
                $output = new RequestOutput($t);
            }

            try
            {
                $output->to_db();
            }
            catch (\Throwable $t)
            {
                # todo
            }

            $output->logs = ob_get_clean();

            return $output;

        }

    }