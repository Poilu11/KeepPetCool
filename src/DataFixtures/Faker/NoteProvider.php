<?php

namespace App\DataFixtures\Faker;

class NoteProvider extends \Faker\Provider\Base
{
    private $note = [
        0,
        1,
        2,
        3,
        4,
        5
    ];
    public function randomNote()
    {
        return $this->randomElement($this->note);
    }
}