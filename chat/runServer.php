<?php

//https://www.maketecheasier.com/run-bash-commands-background-linux/

/* Pour lancer la commande : 
*php chat/runServer.php &>/dev/null & (&>/dev/null sert à empecher les dump de s'afficher) (le & de la fin sert à faire tourner le processus en background et donc à récupérer la main sur le terminal)
*disown (sert à empecher que le processus s'arrete en fermant le terminal)
*/

/* Pour arrêter une commande :
*   ps -ef |grep "runServer.php" pour afficher la liste des processus lié au script runServer.php
*   Il faut ensuite repérer le numéro du processus puis faire la commande : kill numéro
*/


use Chat\ChatApp;
use Ratchet\Http\HttpServer;
use Chat\IoSecureServer;
use Ratchet\WebSocket\WsServer;


//Ce script permet de démarrer le serveur de websocket, il doit être lancé via le terminal : via la commande php runServer.php

require __DIR__ . '/../vendor/autoload.php';

$server = IoSecureServer::factory(new HttpServer(new WsServer(new ChatApp())) ,80, '95.142.169.6');

$server->run();