<?php

namespace AmiAdapter\Message\Actions;

use PAMI\Message\Action\ActionMessage;

class MonitorAction extends ActionMessage
{
    /**
     * Whether the input and output streams should be mixed into a single audio file
     *
     * @param bool $mix
     * @return void
     */
    public function setMix($mix)
    {
        $this->setKey('Mix', $mix ? 'true' : 'false');
    }

    /**
     * Set the monitor format for the recording
     *
     * @param string $format
     * @return void
     */
    public function setFormat($format)
    {
        $this->setKey('Format', $format);
    }

    /**
     * Custom name for the monitor file generated. Absolute and relative paths
     * are both allowed.
     *
     * @param string $file
     * @return void
     */
    public function setFile($file)
    {
        $this->setKey('File', $file);
    }

    /**
     * Build a new Monitor action
     *
     * @param string $channel  Channel to monitor.
     */
    public function __construct($channel)
    {
        parent::__construct('Monitor');
        $this->setKey('Channel', $channel);
    }
}
