<?php

namespace App\DataFixtures\Faker;

class UserTypeProvider extends \Faker\Provider\Base
{
    private $type = [
        'petsitter',
        'owner'
    ];

    public function randomUserType()
    {
        return $this->randomElement($this->type);
    }
}