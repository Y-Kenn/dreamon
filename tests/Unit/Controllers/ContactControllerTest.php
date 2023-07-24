<?php

namespace Tests\Unit\Controllers;

use App\Mail\MailSend;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{

    public function testCreate():void
    {
        $response = $this->get('/contact');
        $response->assertOk();
    }

    public function testStore():void
    {

        $response = $this
            ->post('/contact', [
                'email' => 'test@gmail.com',
                'text' => 'This is a test mail.'
            ]);

        $response->assertRedirect('/contact');
    }
}
