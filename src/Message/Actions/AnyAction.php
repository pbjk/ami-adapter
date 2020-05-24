<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

/**
 * Generic class that can represent any valid (*or invalid*) AMI Action.
 *
 * This class is required to implement the "send_request" method for PHPAGI,
 * which allows the user to send arbitrary AMI requests.
 */
class AnyAction extends ActionMessage
{
    public function __construct($action, $parameters)
    {
        parent::__construct($action);
        foreach ($parameters as $key => $value) {
            $this->setKey($key, $value);
        }
    }
}
