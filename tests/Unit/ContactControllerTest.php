<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Mail\MailSend;
use Illuminate\Support\Facades\Mail;

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
