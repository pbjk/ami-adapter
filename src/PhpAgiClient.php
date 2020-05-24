<?php

namespace AmiAdapter;

interface PhpAgiClient
{
    /**
     * Send an arbitrary AMI action request.
     *
     * @param string $action
     * @param string $parameters
     * @return void
     */
    public function send_request($action, $parameters);

    /**
     * Poll the socket for responses/events.
     *
     * @param mixed $allowTimeout
     * @return void
     */
    public function wait_response($allowTimeout);

    /**
     * Initialize the client and make a TCP connection to the AMI.
     *
     * @param string|null $server
     * @param string|null $username
     * @param string|null $secret
     * @return void
     */
    public function connect($server, $username, $secret);

    /**
     * Disconnect the TCP socket
     *
     * @return void
     */
    public function disconnect();

    /**
     * Hangup a channel after a certain amount of time has passed
     *
     * @param string $channel
     * @param integer $timeout
     * @return array
     */
    public function AbsoluteTimeout($channel, $timeout);

    /**
     * Change monitoring filename for a given channel
     *
     * @param string $channel
     * @param string $file
     * @return array
     */
    public function ChangeMonitor($channel, $file);

    /**
     * Execute a command as if it were run at the Asterisk console
     *
     * @param string $command
     * @param string|null $actionId
     * @return array
     */
    public function Command($command, $actionId);

    /**
     * Filter the types of events that will be sent to this AMI client.
     *
     * @param string $eventMask
     * @return array
     */
    public function Events($eventmask);

    /**
     * Use DEVICE_STATE to check the state of an extension
     *
     * @param string $exten
     * @param string $context
     * @param string|null $actionId
     * @return array
     */
    public function ExtensionState($exten, $context, $actionId);

    /**
     * Get a channel variable.
     *
     * Pass false as $channel to get a global variable.
     *
     * @param string|bool $channel
     * @param string $variable
     * @param string|null $actionId
     * @return array
     */
    public function GetVar($channel, $variable, $actionId);

    /**
     * Hangup a channel.
     *
     * @param string $channel
     * @return array
     */
    public function Hangup($channel);

    /**
     * List IAX Peers
     *
     * @return array
     */
    public function IAXPeers();

    /**
     * List available AMI commands
     *
     * @param string|null $actionId
     * @return array
     */
    public function ListCommands($actionId);

    /**
     * Log off the AMI session. Note that this does not necessarily disconnect
     * the TCP socket.
     *
     * @return array
     */
    public function Logoff();

    /**
     * Get a count of "New", "Old", and "Urgent" messages for the given mailbox
     *
     * @param string $mailbox
     * @param string|null $actionId
     * @return array
     */
    public function MailboxCount($mailbox, $actionId);

    /**
     * Check whether a mailbox has any messages waiting
     *
     * @param string $mailbox
     * @param string|null $actionId
     * @return array
     */
    public function MailboxStatus($mailbox, $actionId);

    /**
     * Start monitoring a channel
     *
     * @param string $channel
     * @param string|null $file
     * @param string|null $format
     * @param string|null $mix
     * @return array
     */
    public function Monitor($channel, $file, $format, $mix);

    /**
     * Make a call
     *
     * The parameters $exten, $context, and $priority should be specified
     * together (if one is non-null, the others should be non-null). The
     * parameters $application and $data must be specified together.
     *
     * @param string $channel
     * @param string|null $exten
     * @param string|null $context
     * @param string|null $priority
     * @param string|null $application
     * @param string|null $data
     * @param integer|null $timeout
     * @param string|null $callerid
     * @param string|null $variable
     * @param string|null $account
     * @param boolean|null $async
     * @param string|null $actionId
     * @return array
     */
    public function Originate($channel,
        $exten,
        $context,
        $priority,
        $application,
        $data,
        $timeout,
        $callerid,
        $variable,
        $account,
        $async,
        $actionId
    );

    /**
     * Get a list of parked calls
     *
     * @param string|null $actionId
     * @return array
     */
    public function ParkedCalls($actionId);

    /**
     * Keepalive command
     *
     * @return array
     */
    public function Ping();

    /**
     * Add an interface to a queue
     *
     * @param string $queue
     * @param string $interface
     * @param int $penalty
     * @return array
     */
    public function QueueAdd($queue, $interface, $penalty);

    /**
     * Add an interface to a queue
     *
     * @param string $queue
     * @param string $interface
     * @return array
     */
    public function QueueRemove($queue, $interface);

    /**
     * Get properties of all queues
     *
     * @return array
     */
    public function Queues();

    /**
     * Show the status of all queues
     *
     * @param string|null $actionId
     * @return array
     */
    public function QueueStatus($actionId);

    /**
     * Redirect ("transfer", "pickup", "hijack") one or two channels.
     *
     * The $extrachannel parameter can be set to an empty string if double
     * redirect is not desired.
     *
     * @param string $channel
     * @param string $extraChannel
     * @param string $exten
     * @param string $context
     * @param string $priority
     * @return array
     */
    public function Redirect($channel, $extraChannel, $exten, $context, $priority);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $userfield
     * @param string $channel
     * @param string $append
     * @return array
     */
    public function SetCDRUserField($userfield, $channel, $append);

    /**
     * Set a channel variable.
     *
     * Pass false as $channel to set a global variable.
     *
     * @param string|bool $channel
     * @param string $variable
     * @param string $value
     * @return array
     */
    public function SetVar($channel, $variable, $value);

    /**
     * Return the status of a given channel
     *
     * Pass false as $channel to get all status.
     *
     * @param string|bool $channel
     * @param string|null $actionId
     * @return
     */
    public function Status($channel, $actionId);

    /**
     * Stop monitoring a channel
     *
     * @param string $channel
     * @return array
     */
    public function StopMonitor($channel);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $zapChannel
     * @param string $number
     * @return array
     */
    public function ZapDialOffHook($zapChannel, $number);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $zapChannel
     * @return array
     */
    public function ZapDNDoff($zapChannel);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $zapChannel
     * @return array
     */
    public function ZapDNDon($zapChannel);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $zapChannel
     * @return array
     */
    public function ZapHangup($zapChannel);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $zapChannel
     * @return array
     */
    public function ZapTransfer($zapChannel);

    /**
     * TODO: Undocumented by Asterisk
     *
     * @param string $zapChannel
     * @return array
     */
    public function ZapShowChannels($actionId);

    /**
     * Log a message via AGI
     *
     * @param string $message
     * @param int $level
     * @return void
     */
    public function log($message, $level);

    /**
     * Register an event handler to be called when a given event is received.
     *
     * The $event '*' can be specified to register the handler for all events.
     *
     * @param string $event
     * @param callable $callback
     * @return bool
     */
    public function add_event_handler($event, $callback);
}
