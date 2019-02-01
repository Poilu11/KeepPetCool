<?php

namespace Chat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatApp implements MessageComponentInterface {

    //Ce script implémente les différents callback qui seront gérés par le serveur de websocket.

    protected $clients;
    protected $idToColor = [];
    protected $colors = ["#800000", "#993399", "#3333cc", "#009999", "#669900", "#996600", "#cc5200"];

    public function __construct() {
        //\SplObjectStorage est un équivalent des ArrayCollection pour les entity, c'est une collection d'objet avec des méthodes spécifiques.
        $this->clients = new \SplObjectStorage;
    }

    // Ce callback est executé lorsqu'une nouvelle connexion s'effectue sur le serveur. Une nouvelle connexion s'effectue lorsque qu'un sript js du client se connecte.
    public function onOpen(ConnectionInterface $conn)
    {
        // On stocke la connexion dans une propriété.
        $this->clients->attach($conn);

        dump("New connection! ({$conn->resourceId})\n");
    }

    //Ce callback est executé lorsque qu'un client envoit un message au serveur via le javascript.
    //Le $from représente la connexion du client qui a envoyé le message. Le $msg est une chaine de caractère qui correspond au message envoyé.
    public function onMessage(ConnectionInterface $from, $msg)
    {

        //On récupère le nombre de connexion au chat, sauf celui qui est l'auteur du chat. Cela sert uniquement pour les logs du chat.
        $numRecv = count($this->clients) - 1;

        dump(sprintf('Connection %d sending message "%s" to %d other connections' . "\n" , $from->resourceId, $msg, $numRecv));

        //La chaine de caractère $msg est formattée à la manière du json. On utilise json décode pour la rendre exploitable.
        
        $msg = json_decode($msg, true);
        var_dump($msg);

        //Si aucune couleur n'est associé à cet Id, alors on en associe une.
        if(!array_key_exists($msg["id"], $this->idToColor))
        {
            $this->idToColor[$msg["id"]] = $this->colors[array_rand($this->colors)];
        }

        $msg["color"] = $this->idToColor[$msg["id"]];

        //Pour chaque client qui n'est pas l'auteur du message, on trnasmet le message.
        foreach ($this->clients as $client)
        {
            if ($from !== $client)
            {
                $json = json_encode($msg);
                $client->send($json);
            }
            else if( $from === $client)
            {
                $msg["username"] = "Me (".$msg["username"].")";
                $json = json_encode($msg);
                $client->send($json);
            }
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
}