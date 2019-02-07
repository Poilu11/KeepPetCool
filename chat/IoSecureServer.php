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

    protected $secureOptions = [];

    public function __construct()
    {
        parent::__construct();

        $secureOptions = [
            'local_cert' => 'path to file',
            'local_pk' => 'path to file',
            'allow_self_signed' => true,
            'verify_peer' => false
            ];
    }

    public static function factory(MessageComponentInterface $component, $port = 80, $address = '0.0.0.0') 
    {
        $loop = LoopFactory::create();
        $socket = new Reactor($address . ':' . $port, $loop);
        
        $socket = new SecureReactor($socket, $loop, $secureOptions);

        
        return new static($component, $socket, $loop);
    }
}