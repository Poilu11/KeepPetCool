<?php
// https://symfony.com/doc/current/testing.html

namespace App\Tests\Util;

use App\Util\Mailer;
use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{
    public function testSend()
    {
        $mailer = new Mailer('keeppetcool4!');

        $result = $mailer->send('mathevon.florian@gmail.com',
                    'Bonjour, <br>Message de Test<br> L\'équipe KeepPetCool',
                    'KeepPetCool - Nouveau message !',
                    'Testeur',
                    'Testorator'
            );

        $this->assertEquals(true, $result);
    }
}