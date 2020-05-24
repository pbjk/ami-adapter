<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class ZapShowChannelsAction extends ActionMessage
{
    public function __construct()
    {
        parent::__construct('ZapShowChannels');
    }
}
