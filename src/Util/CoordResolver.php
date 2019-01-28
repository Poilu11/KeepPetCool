<?php

namespace App\Util;

class CoordResolver{

    private $key;

    public function __construct(string $key)
    {
        // Le paramètre $key est automatiquement récupérer lors du parametrage de service.yaml. C'est la clef d'authentification qui nous permet d'accéder à l'api locationiq.
        // Locationiq documentation : https://locationiq.com
        $this->key = $key;
    }

    public function getCoords($adress)
    {
        $request = '&q='.$adress.'&countrycodes=fr&format=json';
        $responses = json_decode($this->getApiResponse($request), true);

        
        // On vérifie que le retour de location ok.
        if(!array_key_exists("error", $responses))
        {
            // On prend par défaut le premier tableau du json retourné, sauf si responses est vide.
            $validResponse = $responses[0];

            foreach($responses as $resp)
            {
                // Si on croise un tableau qui référence une localidation de type 'place' on le définit comme tableau à exploiter. En effet, sinon on risque de traiter une localisation de type highway ou country, ce qui ne nous intéresse, sauf si on a pas le choix.
                if(array_key_exists("class", $resp) && $resp['class'] === 'place')
                {
                    $validResponse = $resp;
                    break;
                }
            }
        }
        else
        {
            $validResponse = null;
        }
        
        dump($validResponse);

        // Si validResponse est null alors il est impossible de récupérer des latitudes et des longitudes. Auquel cas on leur donne des valeurs erronnées afin d'effectuer une vérification dans le controller.
        $coords[0] = (!is_null($validResponse)) ? $validResponse['lat'] : 'NC';
        $coords[1] = (!is_null($validResponse)) ? $validResponse['lon'] : 'NC';

        return $coords;
    }

    private function getApiResponse($request)
    {
        // Lesrequêtes sur locationiq doivent être de la form $key=ma_clé_dauthentification&q=mon_adress&format=mon_format
        $locationiqApiUrl = 'https://eu1.locationiq.com/v1/search.php?key='.$this->key.$request;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $locationiqApiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // locationiq renvoit un json avec plusieurs objets pouvant correspondre à une adresse.
        $responses = curl_exec($curl);

        curl_close($curl);

        return $responses;
    }
}