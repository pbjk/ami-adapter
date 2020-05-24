<?php

namespace AmiAdapter;

use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\ActionMessage;

class MockAdapterClient extends AdapterClient
{
    protected function send(ActionMessage $message, $actionId = null)
    {
        if (!is_null($actionId)) {
            $message->setActionID($actionId);
        }

        // Return the message that would have been sent so that we can inspect it
        return $message;
    }

    public function wait_response($allowTimeout = null)
    { }

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

        $this->pami = new PamiClient($this->config);
    }

    public function disconnect()
    { }

    public function add_event_handler($event, $callback)
    {
        $predicate = null;
        if ($event !== '*') {
            // Create a predicate to make sure we only receive events of the given type
            $predicate = function($message) use ($event) {
                return $message->getKey('Event') === $event;
            };
        }

        $callback = $this->wrapCallback($callback);
        return compact('callback', 'predicate');
    }
}
