<?php
// https://symfony.com/doc/current/testing.html

namespace App\Tests\Util;

use App\Util\Slugger;
use PHPUnit\Framework\TestCase;

class SluggerTest extends TestCase
{
    public function testSluggify()
    {
        $slugger = new Slugger(true);

        $result = $slugger->sluggify('Message Titre Test');

        $this->assertEquals('message-titre-test', $result);
    }
}