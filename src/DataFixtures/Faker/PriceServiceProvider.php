<?php

namespace App\DataFixtures\Faker;

class PriceServiceProvider extends \Faker\Provider\Base
{
    private $price = [
        '10 €',
        '15 €',
        '20 €',
        '25 €',
        '30 €'
    ];

    public function randomPriceService()
    {
        return $this->randomElement($this->price);
    }
}