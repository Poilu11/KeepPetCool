
var connection = {

    socket: null,

    init: function()
    {
        // L'adresse du serveur de websocket est de la forme ws://127.0.0.1:8080.
        // Cette ligne permet de se connecter au serveur de websocket.
        connection.socket = new WebSocket("ws://"+ws_server_ip+":"+ws_server_port);

        // On définit ici les différents callbacks qui seront déclenché l'ouverture et à la fermeture de la connexion, et à la reception d'un message provenant du serveur.
        connection.socket.onopen = function(e) {console.log("Connexion établie.\n");};

        connection.socket.onclose = function(e) {console.log("Connexion terminée.\n");};

        connection.socket.onmessage = function(e) {displayPanel.write(e.data)};
    },

    //Cette fonction permet d'envoyer un message au serveur de websocket.
    send: function(jsonObj)
    {
        var jsonStr = JSON.stringify(jsonObj);
        connection.socket.send(jsonStr);
    }
}


var sendPanel = {

    input: null,

    init: function()
    {   
        // On récupère l'input dans lequel l'utilisateur tape son message.
        sendPanel.input = document.querySelector(".sendPanel");
        // On ajoute un event au relachement de la touche "entrée"
        sendPanel.input.addEventListener('keyup', sendPanel.handleEnterKey);
    },

    handleEnterKey: function(e)
    {
        // 13 est le keyCode de la touche entrée. Ce callback ne fera rien si la touche relachée n'est pas "entrée".
        if(e.keyCode === 13)
        {
            var msg = {
                id: userId,
                username: username,
                content: sendPanel.input.value,
            }

            console.log(msg.content);

            if(msg.content !== "")
            {
                connection.send(msg);
                sendPanel.input.value = "";
            } 
        }
    }
}


var displayPanel = {

    board: null,

    init: function()
    {
        displayPanel.board = document.querySelector(".displayPanel");
    },

    // Cette fonction sert à afficher un message dans le chat.
    write: function(jsonStr)
    {
        // On détermine si la scrollbar est tout en bas.
        var isScrollBottom = ((displayPanel.board.scrollTop + displayPanel.board.clientHeight)/displayPanel.board.scrollHeight) > 0.95 ? true : false ;

        displayPanel.board.innerHTML += displayPanel.createLine(jsonStr);

        if(isScrollBottom)
        {
            displayPanel.keepTrack();
        }

    },

    // Cette fonction sert à garder la scroll bar en bas, si elle l'était avant l'écriture du message.
    keepTrack: function()
    {
        displayPanel.board.scrollTop = displayPanel.board.scrollHeight - displayPanel.board.clientHeight;
    },

    createLine: function(jsonStr)
    {   
        console.log()
        jsonObj = JSON.parse(jsonStr);

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