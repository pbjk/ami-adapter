<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class ZapTransferAction extends ActionMessage
{
    public function __construct($zapChannel)
    {
        parent::__construct('ZapTransfer');
        $this->setKey('ZapChannel', $zapChannel);
    }
}
