<?php

namespace Chat;
use Ratchet\Server\IoServer;
use React\Socket\ServerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Reactor;
use Ratchet\MessageComponentInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SecureServer as SecureReactor;

class IoSecureServer extends IoServer{

    protected static $secureOptions = [];

    public function __construct(MessageComponentInterface $app, ServerInterface $socket, LoopInterface $loop = null)
    {
        parent::__construct($app, $socket, $loop);

        self::$secureOptions = [
            'local_cert' => '/etc/letsencrypt/live/chat.keeppetcool.com/cert.pem',
            'local_pk' => '/etc/letsencrypt/live/chat.keeppetcool.com/privkey.pem',
            'allow_self_signed' => true,
            'verify_peer' => false
            ];
    }

    public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0') 
    {
        $loop = LoopFactory::create();
        $socket = new Reactor($address . ':' . $port, $loop);
        
        $socket = new SecureReactor($socket, $loop, self::$secureOptions);

        
        return new static($component, $socket, $loop);
    }
}