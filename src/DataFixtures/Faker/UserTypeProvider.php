<?php

namespace App\DataFixtures\Faker;

class UserTypeProvider extends \Faker\Provider\Base
{
    private $note = [
        'Petsitter',
        'Propriétaire'
    ];

    public function randomUserType()
    {
        return $this->randomElement($this->note);
    }
}