<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class ZapDialOffHookAction extends ActionMessage
{
    public function __construct($zapChannel, $number)
    {
        parent::__construct('ZapDialOffhook');
        $this->setKey('ZapChannel', $zapChannel);
        $this->setKey('Number', $number);
    }
}
