<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class ZapDndOffAction extends ActionMessage
{
    public function __construct($zapChannel)
    {
        parent::__construct('ZapDNDoff');
        $this->setKey('ZapChannel', $zapChannel);
    }
}
