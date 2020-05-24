<?php

namespace AmiAdapter;

use AmiAdapter\Message\Actions\AnyAction;
use AmiAdapter\Message\Actions\IaxPeersAction;
use AmiAdapter\Message\Actions\MonitorAction;
use AmiAdapter\Message\Actions\SetCdrUserFieldAction;
use AmiAdapter\Message\Actions\ZapDialOffHookAction;
use AmiAdapter\Message\Actions\ZapDndOffAction;
use AmiAdapter\Message\Actions\ZapDndOnAction;
use AmiAdapter\Message\Actions\ZapHangupAction;
use AmiAdapter\Message\Actions\ZapShowChannelsAction;
use AmiAdapter\Message\Actions\ZapTransferAction;

use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\AbsoluteTimeoutAction;
use PAMI\Message\Action\ActionMessage;
use PAMI\Message\Action\ChangeMonitorAction;
use PAMI\Message\Action\CommandAction;
use PAMI\Message\Action\ExtensionStateAction;
use PAMI\Message\Action\EventsAction;
use PAMI\Message\Action\GetVarAction;
use PAMI\Message\Action\HangupAction;
use PAMI\Message\Action\ListCommandsAction;
use PAMI\Message\Action\LogoffAction;
use PAMI\Message\Action\MailboxCountAction;
use PAMI\Message\Action\MailboxStatusAction;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\ParkedCallsAction;
use PAMI\Message\Action\PingAction;
use PAMI\Message\Action\QueueAddAction;
use PAMI\Message\Action\QueueRemoveAction;
use PAMI\Message\Action\QueuesAction;
use PAMI\Message\Action\QueueStatusAction;
use PAMI\Message\Action\RedirectAction;
use PAMI\Message\Action\SetVarAction;
use PAMI\Message\Action\StatusAction;
use PAMI\Message\Action\StopMonitorAction;
use PAMI\Message\IncomingMessage;

class AdapterClient implements PhpAgiClient
{
    const DEFAULT_PHPAGI_CONFIG = '/etc/asterisk/phpagi.conf';

    /**
     * PAMI client instance
     *
     * @var \PAMI\Client\Impl\ClientImpl
     */
    public $pami;

    /**
     * PHPAGI configuration options
     *
     * @var array
     */
    public $config;

    /**
     * AMI socket resource. Not available publicly from PAMI, so this will
     * always be null here.
     *
     * @var resource|null
     */
    public $socket = null;

    /**
     * Server to which the AMI TCP connection will be made
     *
     * @var string
     */
    public $server;

    /**
     * Port to which the AMI TCP connection will be made
     *
     * @var string
     */
    public $port;

    /**
     * Constructor
     *
     * @param string|null $file     PHPAGI config file path
     * @param array $options        PHPAGI config array
     * @param array $pamiOptions    Additional PAMI-specific options
     */
    public function __construct($file = null, array $options = array(), array $pamiOptions = array())
    {
        $fileOptions = array();
        if (is_string($file) && file_exists($file)) {
            $fileOptions = parse_ini_file($file, true);
        }
        elseif (file_exists(self::DEFAULT_PHPAGI_CONFIG)) {
            $fileOptions = parse_ini_file(self::DEFAULT_PHPAGI_CONFIG, true);
        }

        $fileOptions = is_array($fileOptions) ? $fileOptions : array();
        $pagiOptions = array_merge($fileOptions, $options);
        $pamiOptions = array_merge(array(
            'host'      => array_key_exists('server', $pagiOptions)    ? $pagiOptions['server']   : 'localhost',
            'port'      => array_key_exists('port', $pagiOptions)      ? $pagiOptions['port']     : '5038',
            'username'  => array_key_exists('username', $pagiOptions)  ? $pagiOptions['username'] : 'phpagi',
            'secret'    => array_key_exists('secret', $pagiOptions)    ? $pagiOptions['secret']   : 'phpagi',
            // connect_timeout in seconds, read_timeout in milliseconds. Note
            // that these values can be overridden in $pamiOptions.
            'connect_timeout' => 10,
            'read_timeout' => 500,
        ), $pamiOptions);

        $this->config = $pamiOptions;
        $this->server = $pamiOptions['host'];
        $this->port = $pamiOptions['port'];
        $this->pami = new PamiClient($this->config);
    }

    /**
     * Rewrite the response in an array format that PHPAGI users will be
     * familiar with.
     *
     * @param \PAMI\Message\IncomingMessage $response
     * @return array
     */
    protected function convertResponse(IncomingMessage $response)
    {
        $keys = $response->getKeys();
        $events = method_exists($response, 'getEvents') ? $response->getEvents() : array();

        if (!count($events)) {
            return $keys;
        }

        return array_merge($keys, array(
            '__eventlist' => array_map(function($event) {
                return $event->getKeys();
            }, $events)
        ));
    }

    /**
     * Set the action ID of a message and retrieve the response in a
     * PHPAGI-compatible format.
     *
     * @param \PAMI\Message\Action\ActionMessage $message
     * @param string|null $actionId
     * @return array
     */
    protected function send(ActionMessage $message, $actionId = null)
    {
        if (!is_null($actionId)) {
            $message->setActionID($actionId);
        }
        return $this->convertResponse($this->pami->send($message));
    }

    public function send_request($action, $parameters)
    {
        return $this->send(new AnyAction($action, $parameters));
    }

    /**
     * {@inheritdoc} PAMI sets the socket to nonblocking, so the $allowTimeout parameter is
     * useless. If polling this function in a loop, it will be necessary to add
     * a small sleep between iterations.
     */
    public function wait_response($allowTimeout = null)
    {
        $this->pami->process();
    }

    public function connect($server = null, $username = null, $secret = null)
    {
        if (!is_null($server)) {
            if (($colonPos = strpos($server, ':')) !== false) {
                $this->config['port'] = substr($server, $colonPos + 1);
                $server = substr($server, 0, $colonPos);
            }
            $this->config['host'] = $server;
        }
        if (!is_null($username)) {
            $this->config['username'] = $username;
        }
        if (!is_null($secret)) {
            $this->config['secret'] = $secret;
        }

        // If a config was specified here, re-initialize the PAMI client
        if (!is_null($server) || !is_null($username) || !is_null($secret)) {
            $this->pami = new PamiClient($this->config);
        }

        $this->pami->open();
    }

    public function disconnect()
    {
        $this->pami->close();
    }

    public function AbsoluteTimeout($channel, $timeout)
    {
        return $this->send(new AbsoluteTimeoutAction($channel, $timeout));
    }

    public function ChangeMonitor($channel, $file)
    {
        return $this->send(new ChangeMonitorAction($channel, $file));
    }

    public function Command($command, $actionId = null)
    {
        return $this->send(new CommandAction($command), $actionId);
    }

    public function Events($eventMask)
    {
        // PHPAGI uses a string eventmask, PAMI uses array
        $eventMask = array_map(function($eventType) { return trim($eventType); }, explode(',', $eventMask));
        return $this->send(new EventsAction($eventMask));
    }

    public function ExtensionState($exten, $context, $actionId = null)
    {
        return $this->send(new ExtensionStateAction($exten, $context), $actionId);
    }

    public function GetVar($channel, $variable, $actionId = null)
    {
        return $this->send(new GetVarAction($variable, $channel), $actionId);
    }

    public function Hangup($channel)
    {
        return $this->send(new HangupAction($channel));
    }

    public function IAXPeers()
    {
        return $this->send(new IaxPeersAction());
    }

    public function ListCommands($actionId = null)
    {
        return $this->send(new ListCommandsAction(), $actionId);
    }

    public function Logoff()
    {
        return $this->send(new LogoffAction());
    }

    public function MailboxCount($mailbox, $actionId = null)
    {
        return $this->send(new MailboxCountAction($mailbox), $actionId);
    }

    public function MailboxStatus($mailbox, $actionId = null)
    {
        return $this->send(new MailboxStatusAction($mailbox), $actionId);
    }

    public function Monitor($channel, $file = null, $format = null, $mix = null)
    {
        $message = new MonitorAction($channel);
        if (!is_null($file)) {
            $message->setFile($file);
        }
        if (!is_null($format)) {
            $message->setFormat($format);
        }
        if (!is_null($mix)) {
            $message->setMix($mix);
        }

        return $this->send($message);
    }

    public function Originate(
        $channel,
        $exten = null,
        $context = null,
        $priority = null,
        $application = null,
        $data = null,
        $timeout = null,
        $callerId = null,
        $variable = null,
        $account = null,
        $async = null,
        $actionId = null
    ) {
        $message = new OriginateAction($channel);

        if (!is_null($exten)) {
            $message->setExtension($exten);
        }

        if (!is_null($context)) {
            $message->setContext($context);
        }

        if (!is_null($priority)) {
            $message->setPriority($priority);
        }

        if (!is_null($application)) {
            $message->setApplication($application);
        }

        if (!is_null($data)) {
            $message->setData($data);
        }

        if (!is_null($timeout)) {
            $message->setTimeout($timeout);
        }

        if (!is_null($callerId)) {
            $message->setCallerId($callerId);
        }

        if (!is_null($variable)) {
            $vars = explode('&', $variable);
            if (!is_array($vars)) {
                $vars = array();
            }
            foreach ($vars as $var) {
                $var = explode('=', $var, 2);
                if (!is_array($var) || count($var) !== 2) {
                    continue;
                }

                $message->setVariable($var[0], $var[1]);
            }
        }

        if (!is_null($account)) {
            $message->setAccount($account);
        }

        if (!is_null($async)) {
            $message->setAsync($async);
        }

        return $this->send($message, $actionId);
    }

    public function ParkedCalls($actionId = null)
    {
        return $this->send(new ParkedCallsAction(), $actionId);
    }

    public function Ping()
    {
        return $this->send(new PingAction());
    }

    public function QueueAdd($queue, $interface, $penalty = 0)
    {
        $message = new QueueAddAction($queue, $interface);
        $message->setPenalty($penalty);
        return $this->send($message);
    }

    public function QueueRemove($queue, $interface)
    {
        return $this->send(new QueueRemoveAction($queue, $interface));
    }

    public function Queues()
    {
        return $this->send(new QueuesAction());
    }

    public function QueueStatus($actionId = null)
    {
        return $this->send(new QueueStatusAction(), $actionId);
    }

    public function Redirect($channel, $extraChannel, $exten, $context, $priority)
    {
        $message = new RedirectAction($channel, $exten, $context, $priority);
        if ($extraChannel) {
            $message->setExtraChannel($extraChannel);
        }
        return $this->send($message);
    }

    public function SetCDRUserField($userfield, $channel, $append = null)
    {
        $message = new SetCdrUserFieldAction($channel, $userfield);
        if (!is_null($append)) {
            $message->setAppend($append);
        }
        return $this->send($message);
    }

    public function SetVar($channel, $variable, $value)
    {
        return $this->send(new SetVarAction($variable, $value, $channel));
    }

    public function Status($channel = false, $actionId = null)
    {
        return $this->send(new StatusAction($channel), $actionId);
    }

    public function StopMonitor($channel)
    {
        return $this->send(new StopMonitorAction($channel));
    }

    public function ZapDialOffhook($zapChannel, $number)
    {
        return $this->send(new ZapDialOffHookAction($zapChannel, $number));
    }

    public function ZapDNDoff($zapChannel)
    {
        return $this->send(new ZapDndOffAction($zapChannel));
    }

    public function ZapDNDon($zapChannel)
    {
        return $this->send(new ZapDndOnAction($zapChannel));
    }

    public function ZapHangup($zapChannel)
    {
        return $this->send(new ZapHangupAction($zapChannel));
    }

    public function ZapTransfer($zapChannel)
    {
        return $this->send(new ZapTransferAction($zapChannel));
    }

    public function ZapShowChannels($actionId = null)
    {
        return $this->send(new ZapShowChannelsAction(), $actionId);
    }

    public function log($message, $level = 1)
    {
        error_log(date('r') . ' - ' . $message);
    }

    /**
     * Format event data as an array rather than EventMessage.
     *
     * @param callable $callback
     * @return callable
     */
    protected function wrapCallback($callback)
    {
        $server = $this->config['host'];
        $port = $this->config['port'];
        return function($message) use ($callback, $server, $port) {
            $keys = $message->getKeys();
            // Currently it appears that PAMI does not set IncomingMessage::$variables
            // $keys['__variables'] = $message->getVariables();
            $keys['__channelvariables'] = $message->getAllChannelVariables();
            return $callback(strtolower($message->getKey('Event')), $keys, $server, $port);
        };
    }

    public function add_event_handler($event, $callback)
    {
        $predicate = null;
        if ($event !== '*') {
            // Create a predicate to make sure we only receive events of the given type
            $predicate = function($message) use ($event) {
                return strtolower($message->getKey('Event')) === strtolower($event);
            };
        }

        $callback = $this->wrapCallback($callback);
        $this->pami->registerEventListener($callback, $predicate);
    }
}
