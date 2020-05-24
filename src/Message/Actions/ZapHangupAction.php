<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class ZapHangupAction extends ActionMessage
{
    public function __construct($zapChannel)
    {
        parent::__construct('ZapHangup');
        $this->setKey('ZapChannel', $zapChannel);
    }
}
