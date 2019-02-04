<?php

// Pour lancer le serveur de websocket en background sur bash : nohup php monChemin/runServer.php &
// Utiliser crtl + c pour sortir de la commande, elle continuera à tourner en background.

//Pour tuer le processus il faut d'abord afficher la liste des processus lié au script runServer.php : ps -ef |grep "runServer.php"
//Il faut repérer le numéro du processus puis faire la commande : kill numéro

use Chat\ChatApp;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;


//Ce script permet de démarrer le serveur de websocket, il doit être lancé via le terminal : via la commande php runServer.php

require __DIR__ . '/../vendor/autoload.php';

$server = IoServer::factory(new HttpServer(new WsServer(new ChatApp())) ,8080);

$server->run();