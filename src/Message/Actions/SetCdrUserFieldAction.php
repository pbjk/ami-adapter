<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class SetCdrUserFieldAction extends ActionMessage
{

    public function setAppend($append)
    {
        $this->setKey('Append', $append ? 'true' : 'false');
    }

    public function __construct($channel, $userfield)
    {
        parent::__construct('SetCDRUserField');
        $this->setKey('Channel', $channel);
        $this->setKey('UserField', $userfield);
    }
}
