<?php

namespace AmiAdapter\Tests;

use PAMI\Message\Action\ActionMessage;
use PHPUnit\Framework\TestCase;

class AdapterClientTest extends TestCase
{
    public $client;

    public function setUp(): void
    {
        $this->client = new MockAdapterClient();
    }

    public function testSetConfigFromFile()
    {
        $client = new MockAdapterClient(__DIR__ . DIRECTORY_SEPARATOR . 'AdapterClientTest.ini');
        $this->assertTrue($client->config['host'] === '127.0.0.1');
        $this->assertTrue($client->config['port'] === '5039');
        $this->assertTrue($client->config['username'] === 'amiadapter');
        $this->assertTrue($client->config['secret'] === 'amiadapter');
    }

    public function optionsProvider()
    {
        return [
            [
                'pagiOptions' => [
                    'server' => '127.0.0.2',
                    'port' => '5040',
                ],
                'pamiOptions' => [
                    'host' => '127.0.0.3',
                    'scheme' => 'https',
                ],
            ],
        ];
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testSetOptionsFromArray($pagiOptions, $pamiOptions)
    {
        $client = new MockAdapterClient(null, $pagiOptions);
        $this->assertTrue($client->config['host'] === '127.0.0.2');
        $this->assertTrue($client->config['port'] === '5040');
        $this->assertTrue($client->config['username'] === 'phpagi');
        $this->assertTrue($client->config['secret'] === 'phpagi');
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testPagiOptionsOverrideFileOptions($pagiOptions, $pamiOptions)
    {
        $client = new MockAdapterClient(__DIR__ . DIRECTORY_SEPARATOR . 'AdapterClientTest.ini', $pagiOptions);
        $this->assertTrue($client->config['host'] === '127.0.0.2');
        $this->assertTrue($client->config['port'] === '5040');
        $this->assertTrue($client->config['username'] === 'amiadapter');
        $this->assertTrue($client->config['secret'] === 'amiadapter');
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testPamiOptionsOverridePagiOptions($pagiOptions, $pamiOptions)
    {
        $client = new MockAdapterClient(null, $pagiOptions, $pamiOptions);
        $this->assertTrue($client->config['host'] === '127.0.0.3');
        $this->assertTrue($client->config['port'] === '5040');
        $this->assertTrue($client->config['scheme'] === 'https');
    }

    public function testProvideOptionsToConnect()
    {
        $this->client->connect('127.0.0.4:5041', 'admin', 'admin');
        $this->assertTrue($this->client->config['host'] === '127.0.0.4');
        $this->assertTrue($this->client->config['port'] === '5041');
        $this->assertTrue($this->client->config['username'] === 'admin');
        $this->assertTrue($this->client->config['secret'] === 'admin');
    }

    public function testSendArbitraryAmiRequest()
    {
        $keys = $this->client->send_request('UserAction', [
            'UserTag1' => 'UserTag1Value',
            'UserTag2' => 'UserTag2Value',
        ])->getKeys();

        $this->assertTrue($keys['action'] === 'UserAction');
        $this->assertTrue($keys['usertag1'] === 'UserTag1Value');
        $this->assertTrue($keys['usertag2'] === 'UserTag2Value');
    }

    public function testSetActionId()
    {
        $keys = $this->client->Command('core show channels concise', 'testActionId')->getKeys();
        $this->assertTrue($keys['actionid'] === 'testActionId');
    }

    public function testAddEventHandler()
    {
        $callbacks = $this->client->add_event_handler('UserEvent', function() {});
        $this->assertTrue(is_callable($callbacks['callback']));
        $this->assertTrue(is_callable($callbacks['predicate']));
    }

    // // // // // //
    // Actions start
    // // // // // //

    public function testAbsoluteTimeoutAction()
    {
        $channel = 'SIP/101-00000000';
        $timeout = 100;
        $expected = [
            'action' => 'AbsoluteTimeout',
            'channel' => $channel,
            'timeout' => $timeout,
        ];
        $keys = $this->client->AbsoluteTimeout($channel, $timeout)->getKeys();
        // Use loose comparison because sometimes integers will get converted to strings
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testChangeMonitorAction()
    {
        $channel = 'SIP/101-00000000';
        $file = time() . '.wav';
        $expected = [
            'action' => 'ChangeMonitor',
            'channel' => $channel,
            'file' => $file,
        ];
        $keys = $this->client->ChangeMonitor($channel, $file)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testCommandAction()
    {
        $command = 'dialplan reload';
        $expected = [
            'action' => 'Command',
            'command' => $command,
        ];
        $keys = $this->client->Command($command)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function eventMaskProvider()
    {
        return [
            [ false ],
            [ 'all' ],
            [ 'system,call,log,verbose,agent,user,config,command,dtmf,reporting,cdr,dialplan,originate,agi,cc,aoc,test' ],
        ];
    }

    /**
     * @dataProvider eventMaskProvider
     */
    public function testEventsAction($eventMask)
    {
        $keys = $this->client->Events($eventMask)->getKeys();
        $this->assertTrue($keys['eventmask'] === (string) $eventMask);
    }

    public function testExtensionStateAction()
    {
        $extension = '101';
        $context = 'from-internal';
        $expected = [
            'action' => 'ExtensionState',
            'exten' => $extension,
            'context' => $context,
        ];
        $keys = $this->client->ExtensionState($extension, $context)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testGetVarAction()
    {
        $channel = 'SIP/101-00000000';
        $variable = 'BRIDGEPEER';
        $expected = [
            'action' => 'Getvar',
            'channel' => $channel,
            'variable' => $variable,
        ];
        $keys = $this->client->GetVar($channel, $variable)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testHangupAction()
    {
        $channel = 'SIP/101-00000000';
        $expected = [
            'action' => 'Hangup',
            'channel' => $channel,
        ];
        $keys = $this->client->Hangup($channel)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testIaxPeersAction()
    {
        $expected = [
            'action' => 'IAXPeers',
        ];
        $keys = $this->client->IAXPeers()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testListCommandsAction()
    {
        $expected = [
            'action' => 'ListCommands',
        ];
        $keys = $this->client->ListCommands()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testLogoffAction()
    {
        $expected = [
            'action' => 'Logoff',
        ];
        $keys = $this->client->Logoff()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testMailboxCountAction()
    {
        $mailbox = '101@default';
        $expected = [
            'action' => 'MailboxCount',
            'mailbox' => $mailbox,
        ];
        $keys = $this->client->MailboxCount($mailbox)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testMailboxStatusAction()
    {
        $mailbox = '101@default';
        $expected = [
            'action' => 'MailboxStatus',
            'mailbox' => $mailbox,
        ];
        $keys = $this->client->MailboxStatus($mailbox)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testMonitorAction()
    {
        $channel = 'SIP/101-00000000';
        $file = time() . '.wav';
        $format = 'wav';
        $mix = true;
        $expected = [
            'action' => 'Monitor',
            'channel' => $channel,
            'file' => $file,
            'format' => $format,
            'mix' => 'true',
        ];
        $keys = $this->client->Monitor($channel, $file, $format, $mix)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testOriginateAction()
    {
        $channel = 'SIP/101-00000000';
        $exten = '101';
        $context = 'from-internal';
        $priority = '1';
        $application = 'MusicOnHold';
        $data = 'no data';
        $timeout = '30';
        $callerId = 'Your Daily Music on Hold';
        $account = 'acct';
        $async = false;
        $expected = [
            'action' => 'Originate',
            'channel' => $channel,
            'exten' => $exten,
            'context' => $context,
            'priority' => $priority,
            'application' => $application,
            'data' => $data,
            'timeout' => $timeout,
            'callerid' => $callerId,
            'account' => $account,
            'async' => 'false',
        ];
        $keys = $this->client->Originate(
            $channel,
            $exten,
            $context,
            $priority,
            $application,
            $data,
            $timeout,
            $callerId,
            null,
            $account,
            $async
        )->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function variableProvider()
    {
        return [
            [ '' ],
            [ 'CDR(dst)=test' ],
            [ 'CDR(dst)=test1&TEST_VAR=test2' ],
        ];
    }

    /**
     * @dataProvider variableProvider
     */
    public function testOriginateSetsVariables($variables)
    {
        $serialized = $this->client->Originate(
            $channel = '',
            $exten = null,
            $context = null,
            $priority = null,
            $application = null,
            $data = null,
            $timeout = null,
            $callerId = null,
            $variables
        )->serialize();

        if (empty($variables)) {
            $this->assertTrue(strpos($serialized, 'Variable:') === false);
        }
        else {
            foreach (explode('&', $variables) as $variable) {
                $this->assertTrue(strpos($serialized, "Variable: $variable") !== false);
            }
        }
    }

    public function testParkedCallsAction()
    {
        $expected = [
            'action' => 'ParkedCalls',
        ];
        $keys = $this->client->ParkedCalls()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testPingAction()
    {
        $expected = [
            'action' => 'Ping',
        ];
        $keys = $this->client->Ping()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testQueueAddAction()
    {
        $queue = '200';
        $interface = 'Local/101@from-queue/n';
        $penalty = 1;
        $expected = [
            'action' => 'QueueAdd',
            'queue' => $queue,
            'interface' => $interface,
            'penalty' => (string) $penalty,
        ];
        $keys = $this->client->QueueAdd($queue, $interface, $penalty)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testQueueRemoveAction()
    {
        $queue = '200';
        $interface = 'Local/101@from-queue/n';
        $expected = [
            'action' => 'QueueRemove',
            'queue' => $queue,
            'interface' => $interface,
        ];
        $keys = $this->client->QueueRemove($queue, $interface)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testQueuesAction()
    {
        $expected = [
            'action' => 'Queues',
        ];
        $keys = $this->client->Queues()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testQueueStatusAction()
    {
        $expected = [
            'action' => 'QueueStatus',
        ];
        $keys = $this->client->QueueStatus()->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testRedirectAction()
    {
        $channel = 'SIP/101-00000000';
        $extraChannel = 'SIP/102-00000000';
        $exten = 's';
        $context = 'conference-general';
        $priority = 1;
        $expected = [
            'action' => 'Redirect',
            'channel' => $channel,
            'extrachannel' => $extraChannel,
            'exten' => $exten,
            'context' => $context,
            'priority' => (string) $priority,
        ];
        $keys = $this->client->Redirect($channel, $extraChannel, $exten, $context, $priority)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testSetCdrUserFieldAction()
    {
        $userfield = 'inbound';
        $channel = 'SIP/101-00000000';
        $append = true;
        $expected = [
            'action' => 'SetCDRUserField',
            'userfield' => $userfield,
            'channel' => $channel,
            'append' => 'true',
        ];
        $keys = $this->client->SetCDRUserField($userfield, $channel, $append)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testSetVarAction()
    {
        $channel = 'SIP/101-00000000';
        $variable = 'ON_HOLD';
        $value = 'Yes';
        $expected = [
            'action' => 'Setvar',
            'channel' => $channel,
            'variable' => $variable,
            'value' => $value,
        ];
        $keys = $this->client->SetVar($channel, $variable, $value)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testStatusAction()
    {
        $channel = 'SIP/101-00000000';
        $expected = [
            'action' => 'Status',
            'channel' => $channel,
        ];
        $keys = $this->client->Status($channel)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testStopMonitorAction()
    {
        $channel = 'SIP/101-00000000';
        $expected = [
            'action' => 'StopMonitor',
            'channel' => $channel,
        ];
        $keys = $this->client->StopMonitor($channel)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testZapDialOffHookAction()
    {
        $zapChannel = '101';
        $number = '8005550000';
        $expected = [
            'action' => 'ZapDialOffhook',
            'zapchannel' => $zapChannel,
            'number' => $number,
        ];
        $keys = $this->client->ZapDialOffhook($zapChannel, $number)->getKeys();
        $this->assertEquals(array_intersect_assoc($keys, $expected), $expected);
    }

    public function testZapDndOffAction()
    {
        $zapChannel = 'SIP/101-00000000';
        $expected = [
            'action' => 'ZapDNDoff',
            'zapchannel' => $zapChannel,
        ];
        $keys = $this->client->ZapDNDoff($zapChannel)->getKeys();
        $this->assertTrue(array_intersect_assoc($keys, $expected) == $expected);
    }

    public function testZapDndOnAction()
    {
        $zapChannel = 'SIP/101-00000000';
        $expected = [
            'action' => 'ZapDNDon',
            'zapchannel' => $zapChannel,
        ];
        $keys = $this->client->ZapDNDon($zapChannel)->getKeys();
        $this->assertTrue(array_intersect_assoc($keys, $expected) == $expected);
    }

    public function testZapHangupAction()
    {
        $zapChannel = 'SIP/101-00000000';
        $expected = [
            'action' => 'ZapHangup',
            'zapchannel' => $zapChannel,
        ];
        $keys = $this->client->ZapHangup($zapChannel)->getKeys();
        $this->assertTrue(array_intersect_assoc($keys, $expected) == $expected);
    }

    public function testZapTransferAction()
    {
        $zapChannel = 'SIP/101-00000000';
        $expected = [
            'action' => 'ZapTransfer',
            'zapchannel' => $zapChannel,
        ];
        $keys = $this->client->ZapTransfer($zapChannel)->getKeys();
        $this->assertTrue(array_intersect_assoc($keys, $expected) == $expected);
    }

    public function testZapShowChannelsAction()
    {
        $expected = [
            'action' => 'ZapShowChannels',
        ];
        $keys = $this->client->ZapShowChannels()->getKeys();
        $this->assertTrue(array_intersect_assoc($keys, $expected) == $expected);
    }
}
