<?php

use Chat\ChatApp;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;


//Ce script permet de démarrer le serveur de websocket, il doit être lancé via le terminal : via la commande php runServer.php

require __DIR__ . '/../vendor/autoload.php';

$server = IoServer::factory(new HttpServer(new WsServer(new ChatApp())) ,8080);

$server->run();