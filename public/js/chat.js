
// Cet objet gère tout la logique relative à l'envoi et à la réception des données avec le serveur.
var connection = {

    socket: null,
    recoProcess: null,

    init: function()
    {
        // L'adresse du serveur de websocket est de la forme ws://127.0.0.1:8080.
        // Cette ligne permet de se connecter au serveur de websocket.
        connection.socket = new WebSocket("wss://chat.keeppetcool.com:8080/");

        // On définit ici les différents callbacks qui seront déclenché l'ouverture et à la fermeture de la connexion, et à la reception d'un message provenant du serveur.
        connection.socket.onopen = function(e)
        {
            //On indique dans le chat que la connxion est effectuée.
            displayPanel.write(connection.info("Vous êtes connecté au chat !"));

            //Si il y a un processus de reconnexion en cours, alors on l'arrête, car nous sommes désormais connecté.
            if(connection.recoProcess != null)
            {
                clearInterval(connection.recoProcess);
                connection.recoProcess = null;
            }
        };

        // Ce callback est effectué à la fermeture de la connexion.
        connection.socket.onclose = function(e) {

            //Si la connecion est fermée, alors on lance un processus de reconnexion, en vérifiant au éalable qu'aucun processus de reconnexion ne soit en cours d'exécution.
            if(connection.recoProcess == null)
            {   //On indique la déconnexion dans le chat.
                displayPanel.write(connection.info("Vous n'êtes plus connecté au chat ! Tentative de reconnexion..."));

                //On lance le processus de reconnexion. SetInterval renvoit l'id de la fonction executé à interval régulier.
                connection.recoProcess = setInterval(connection.init, 2000);
            }
        };

        // Ce callback est exécuté lorsque l'on reçoit un message du serveur.
        connection.socket.onmessage = function(e) {displayPanel.write(e.data)};
    },

    //Cette fonction permet d'envoyer un message au serveur de websocket.
    send: function(jsonObj)
    {
        // On convertit le json en chaîne de caractère afin qu'il soit compréhensible par le serveur.
        var jsonStr = JSON.stringify(jsonObj);
        // On envoit le message au serveur.
        connection.socket.send(jsonStr);
    },

    //Sert à générer des messages d'information pour le user.
    info: function(str)
    {
        return '{"username": "INFO", "content": "'+str+'", "color": "#f9000c" }';
    },
}

// Cette objet gère toute la logique à la saisie des données par l'utilisateur.
var sendPanel = {

    input: null,
    maxLength: 200, 

    init: function()
    {   
        // On récupère l'input dans lequel l'utilisateur tape son message.
        sendPanel.input = document.querySelector(".sendPanel");
        // On ajoute un event au relachement de la touche "entrée"
        sendPanel.input.addEventListener('keyup', sendPanel.handleEnterKey);
        // On ajoute un event à la saisie du caractère dans l'input.
        sendPanel.input.addEventListener('input', sendPanel.handleMaxLength);
    },


    handleEnterKey: function(e)
    {
        // 13 est le keyCode de la touche entrée. Ce callback ne fera rien si la touche relachée n'est pas "entrée".
        if(e.keyCode === 13)
        {
            // On formatte le message qui sera envoyé au serveur.
            var msg = {
                id: userId,
                username: username,
                content: sendPanel.input.value,
            }

            // On envoit le message au serveur et on reset l'input.
            if(msg.content !== "")
            {
                connection.send(msg);
                sendPanel.input.value = "";
            } 
        }
    },

    // On gère le longueur maximale du message.
    handleMaxLength: function(e)
    { 
        if(sendPanel.input.value.length > sendPanel.maxLength)
        {
            sendPanel.input.value = sendPanel.input.value.slice(0, sendPanel.maxLength);
        }
    }
}

// Cet objet gère toute la logique à l'affichage des données reçues par le serveur.
var displayPanel = {

    board: null,

    init: function()
    {
        // On récupère la div dans laquelle seront affichés les messages.
        displayPanel.board = document.querySelector(".displayPanel");
    },

    // Cette fonction sert à afficher un message dans le chat.
    write: function(jsonStr)
    {
        // Le problème était que le scrollbar ne suit pas par défaut le défilement du chat. Ainsi, si 15 messages apparaissent à la seconde, ceux-ci sont cachés en bas de la du container, puisque la scrollbar ne suit pas le défilement. Le comportement que l'on souhaite développer est le suivant: si la scrollbar est en bas de la div, alors si un nouveau message apparaît elle resque coller en bas de la div.

        // On détermine si la scrollbar est tout en bas.
        // scrollTop renvoit les nombre de pixels cachés en haut du scroll de la div.
        // clientHeight retourne le nombre de pixels visibles sur la div.
        // scrollHeight retourne la hauteur totale de la div, y compris les pixels cachés par le scroll en haut et en bas de la div.
        var isScrollBottom = ((displayPanel.board.scrollTop + displayPanel.board.clientHeight)/displayPanel.board.scrollHeight) > 0.95 ? true : false ;

        //On créer affiche une nouvelle ligne dans le tableau d'affichage.
        displayPanel.board.innerHTML += displayPanel.createLine(jsonStr);

        // Si la scrollbar est en bas la div, alors on fait en sorte qu'elle y reste.
        if(isScrollBottom)
        {
            displayPanel.keepTrack();
        }

    },

    // Cette fonction sert à garder la scrollbar en bas, si elle l'était avant l'écriture du message.
    keepTrack: function()
    {
        displayPanel.board.scrollTop = displayPanel.board.scrollHeight - displayPanel.board.clientHeight;
    },

    // Sert à créer un élément html à partir de données formattées en json.
    createLine: function(jsonStr)
    {   
        //JSON.parse sert à transformer en un objet json.
        jsonObj = JSON.parse(jsonStr);

        //Je crée mon objet du DOM à partir du message json.
        var html = '<div style="color:'+jsonObj.color+';">';
        html += '<strong>'+jsonObj.username+' : </strong>';
        html += jsonObj.content;
        html += '</div>';
        return(html);        
    }
}

document.addEventListener("DOMContentLoaded", sendPanel.init);
document.addEventListener("DOMContentLoaded", displayPanel.init);
document.addEventListener("DOMContentLoaded", connection.init);