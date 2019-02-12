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

/*  Pour déployer le chat en sécure.
*   Dans runServer.php : insérer l'ip du serveur de déploiement en tant que 3eme paramètre de la fonction factory. Il doit etre passé sous forme de chaine de caractère.
*   Dans le .env : mettre le nom de domaine dans wp_server_ip. mettre le numéro de port dans wp_server_port, mettre wp_server_secure = true si connection https.
*   Dans le vhost : rediriger toutes les requête pour des websocket vers le serveur de websocket (inutile en http, essentiel en https).
* RewriteCond %{HTTP:Upgrade} =websocket [NC]
RewriteRule  ""    ws://chat.keeppetcool.com:8080 [P,L]
*/

use Chat\ChatApp;
use Ratchet\Http\HttpServer;
use Chat\IoSecureServer;
use Ratchet\WebSocket\WsServer;


//Ce script permet de démarrer le serveur de websocket, il doit être lancé via le terminal : via la commande php runServer.php

require __DIR__ . '/../vendor/autoload.php';

$server = IoSecureServer::factory(new HttpServer(new WsServer(new ChatApp())) ,8080, '127.0.0.1');

$server->run();