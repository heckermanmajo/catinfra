<?php

    namespace _lib\core;

    abstract class Request
    {

        abstract protected function _execute(RequestInput $input): RequestOutput;

        function execute(RequestInput $input): RequestOutput
        {

            ob_start();

            try
            {
                $output = $this->_execute($input);
            }

            catch (UserError $e)
            {
                # todo: simple logging ...
                $output = new RequestOutput($e);
            }

            catch (\Throwable $t)
            {
                # todo: extra logging ...
                $output = new RequestOutput($t);
            }

            $output->logs = ob_get_clean();

            return $output;

        }

    }