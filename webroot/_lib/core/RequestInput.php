<?php

    namespace _lib\core;

    use Exception;
    use JsonSerializable;

    final class RequestInput implements JsonSerializable{

        static public RequestInput $last_input;

        static function get_last_input(): RequestInput {
            if( !isset( self::$last_input ))
            {
                new RequestInput();
            }
            return self::$last_input;
        }

        public string $action = "";
        
        public array $data = [];
        
        function __construct() {

            self::$last_input = $this;

            $request = $_REQUEST;

            try
            {
                $json = json_decode(file_get_contents('php://input'), true);
            }
            catch (Exception $e)
            {
                $json = null;
            }

            if ($json)
            {
                $this->data = array_merge($request, $json);
            }
            else
            {
                $this->data = $request;
            }

            $this->action = $this->s('action', '');

        }

        function s(string $name, ?string $default = null): string {
            if (isset($this->data[$name]))
            {
                return (string)$this->data[$name];
            }
            if ($default !== null)
            {
                return $default;
            }
            throw new UserError("Missing required string parameter: $name");
        }

        function i(string $name, ?int $default = null): int {
            if (isset($this->data[$name]))
            {
                return (int)$this->data[$name];
            }
            if ($default !== null)
            {
                return $default;
            }
            throw new UserError("Missing required integer parameter: $name");
        }

        function b(string $name, ?bool $default = null): bool {
            if (isset($this->data[$name]))
            {
                return (bool)$this->data[$name];
            }
            if ($default !== null)
            {
                return $default;
            }
            throw new UserError("Missing required boolean parameter: $name");
        }

        function f(string $name, ?float $default = null): float {
            if (isset($this->data[$name]))
            {
                return (float)$this->data[$name];
            }
            if ($default !== null)
            {
                return $default;
            }
            throw new UserError("Missing required float parameter: $name");
        }

        public function has(string $name): bool {
            return isset($this->data[$name]);
        }

        public function jsonSerialize(): mixed
        {
            return get_object_vars($this);
        }

    }