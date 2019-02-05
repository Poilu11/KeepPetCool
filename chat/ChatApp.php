<?php

namespace Chat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

//Ce script implémente les différents callback qui seront gérés par le serveur de websocket.
class ChatApp implements MessageComponentInterface {

    //Sert à garder en mémoire les clients connectés au serveur de websocket.
    protected $clients;

    //Sert à associer un id utilisateur avec une couleur, pioché dans le tableau de couleur ci-dessous.
    protected $idToColor = [];
    protected $colors = ["#800000", "#993399", "#3333cc", "#009999", "#669900", "#996600", "#cc5200"];

    //Sert à garder en mémoire les X derniers messages, où X est la capacité défini ci-dessous.
    protected $msgQueue = [];
    protected $msgCapacity = 40;

    public function __construct() {
        //\SplObjectStorage est un équivalent des ArrayCollection pour les entity, c'est une collection d'objet avec des méthodes spécifiques.
        $this->clients = new \SplObjectStorage;
    }

    // Ce callback est executé lorsqu'une nouvelle connexion s'effectue sur le serveur, c-a-d lorsque le js de la page établit la connexion.
    public function onOpen(ConnectionInterface $conn)
    {
        // On stocke la connexion dans une propriété.
        $this->clients->attach($conn);

        dump("New connection! ({$conn->resourceId})\n");

        // On envoit aux nouveaux arrivants tous les vieux messages stockés par le serveur.
        foreach($this->msgQueue as $json)
        {
            $conn->send($json);
        }
    }


    //Ce callback est executé lorsque qu'un client envoit un message au serveur.
    //Le $from représente la connexion du client qui a envoyé le message. Le $msg est une chaine de caractère qui correspond au message envoyé.
    // $msg = {"id" : id de l'utlisateur en bdd, "username" : username, 'content' : contenui du message}
    public function onMessage(ConnectionInterface $from, $msg)
    {
        
        dump('Message received : '.PHP_EOL.$msg);

        //La chaine de caractère $msg est formattée comme un json. On utilise json_decode pour la rendre exploitable sous la forme d'un tableau associatif.
        $msg = json_decode($msg, true);

        //Si aucune couleur n'est associé à cet Id, alors on en associe une.
        if(!array_key_exists($msg["id"], $this->idToColor))
        {
            // arry_rand renvoit un index aléatoire.
            $this->idToColor[$msg["id"]] = $this->colors[array_rand($this->colors)];
        }

        //On ajoute une entrée 'color' au tableau associatif.
        $msg["color"] = $this->idToColor[$msg["id"]];

        //On réencode le message en json, pour le rendre plus facilement interprétable par le js.
        $json = json_encode($msg);

        //On garde le message dans la mémoire interne du serveur de websocket, afin de les distribuer aux nouveaux arrivants.
        $this->keepMsg($json);

        //Pour chacun des utilisateurs connectés au serveur, on envoit le message.
        foreach ($this->clients as $client)
        {
            $client->send($json);
        }
    }


    // Ce callback est exécuté à la fermeture de la connexion pas un client. $conn représente le client responsable de la fermeture.
    public function onClose(ConnectionInterface $conn)
    {
        //La méthode detach permet de retirer un connexion de la collection.
        $this->clients->detach($conn);

        dump("Connection {$conn->resourceId} has disconnected\n");
    }


    // Ce callback est executé lorsqu'une connexion déclenche une erreur.
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        dump("An error has occurred: {$e->getMessage()} on connection {$conn->resourceId}\n");
    }

    //Cette fonction sert à garder en mémoire les messages du chat.
    private function keepMsg($json)
    {
        //Place un élément à la fin du tableau.
        array_push($this->msgQueue, $json);

        //Si la taille de la liste de message dépasse la capacité définie plus haut, alors on retire le message le plus ancien.
        if(count($this->msgQueue) > $this->msgCapacity)
        {
            array_shift($this->msgQueue);
        }
    }
}