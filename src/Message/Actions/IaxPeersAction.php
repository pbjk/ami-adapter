<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class IaxPeersAction extends ActionMessage
{
    public function __construct()
    {
        parent::__construct('IAXPeers');
    }
}
