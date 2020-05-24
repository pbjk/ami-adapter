<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class ZapDndOnAction extends ActionMessage
{
    public function __construct($zapChannel)
    {
        parent::__construct('ZapDNDon');
        $this->setKey('ZapChannel', $zapChannel);
    }
}
