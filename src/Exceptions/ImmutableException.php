<?php
namespace Rested\Exceptions;

class ImmutableException extends \Exception
{


    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $this->action = $action;

        parent::__construct('This object cannot be changed', $code, $previous);
    }
}
