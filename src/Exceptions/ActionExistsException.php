<?php
namespace Rested\Exceptions;

class ActionExistsException extends \Exception
{

    private $action;

    public function __construct($action, $code = 0, Exception $previous = null)
    {
        $this->action = $action;

        parent::__construct(sprintf('"%s" already exists in this resource', $action), $code, $previous);
    }

    public function getAction()
    {
        return $this->action;
    }
}