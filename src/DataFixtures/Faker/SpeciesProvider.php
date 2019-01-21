<?php

namespace App\DataFixtures\Faker;

class SpeciesProvider extends \Faker\Provider\Base
{
    private $species = [
        'Chien',
        'Chat',
        'Poisson',
        'Oiseau',
        'Serpent',
        'Iguane',
        'Gecko',
        'Cheval',
        'Poney',
        'Lapin',
        'Cochon d\'inde',
        'Tortue',
        'Perroquet'
    ];
    public function speciesName()
    {
        return $this->randomElement($this->species);
    }
}