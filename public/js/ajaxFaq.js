var ajaxFaq = {

    init: function() {
        console.log('init ajaxFaq');

        ajaxFaq.ajaxRequest();
        
    },

    ajaxRequest: function(evt) {
        console.log('ajaxRequest');
        console.log(urlLink);

        // Requête Ajax
        var jqxhr = $.ajax(
        {
            url: urlLink,
            method: 'GET',
            dataType: 'json',
            data: {
                
            }
        }
        );

        // Réponse Ajax correcte
        jqxhr.done(function(response) {

            console.log(response);

            var template = '<div id="accordion" class="col-lg-8 col-sm-10 m-auto pt-5">\
                            <div class="card mb-5 border-10 border-dark">\
                            <div class="card-header" id="heading#number#">\
                                <h5 class="mb-0">\
                                <button class="btn btn-link text-dark" data-toggle="collapse" data-target="#collapse#number#" aria-expanded="true" aria-controls="collapse#number#">\
                                    <span class="h4">#question#</span>\
                                </button>\
                                </h5>\
                            </div>\
                            <div id="collapse#number#" class="collapse" aria-labelledby="heading#number#" data-parent="#accordion">\
                                <div class="card-body text-dark">\
                                #content#\
                                </div>\
                            </div>\
                            </div>\
                             </div>';

            // https://flaviocopes.com/how-to-replace-all-occurrences-string-javascript/
            // Exemple pour remplacer toutes occurences : String.replace(/<TERM>/g, '')
            for (var index in response)
            {

                var questionChgt = template.replace(/#number#/g, response[index][0]);
                var questionChgt = questionChgt.replace(/#question#/g, response[index][1]);
                var questionChgt = questionChgt.replace(/#content#/g, response[index][2]);

                var faqContent = document.querySelector('#faqContent');

                faqContent.outerHTML = questionChgt + faqContent.outerHTML;

            }

        });

        // Réponse Ajax incorrecte
        jqxhr.fail(function() {
            alert('Réponse ajax incorrecte');
        });

  
    },

};

document.addEventListener('DOMContentLoaded', ajaxFaq.init);
