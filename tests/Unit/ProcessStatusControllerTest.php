<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\TwitterAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;

class ProcessStatusControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $twitter_id;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1);
        $this->twitter_id = 1683346494706028549;
        Session::put('twitter_id',$this->twitter_id);
    }

    public function testIndex(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->get('/process-status');
        $response->assertOk();
        $response->assertJson([0 => ['id' => 0, 'status' => 0],
                                1 => ['id' => 1, 'status' => 0],
                                2 => ['id' => 2, 'status' => 0],
                                3 => ['id' => 3, 'status' => 0]]);
    }

    public function testUpdate(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->put('/process-status/1',[
                'flag_name' => 'following_flag',
                'status' => true,
            ]);
        $response->assertOk();

        $response = $this
            ->actingAs($this->user)
            ->put('/process-status/1',[
                'flag_name' => 'liking_flag',
                'status' => true,
            ]);
        $response->assertOk();

        $response = $this
            ->actingAs($this->user)
            ->put('/process-status/1',[
                'flag_name' => 'unfollowing_flag',
                'status' => true,
            ]);
        $response->assertOk();

        $twitter_account = TwitterAccount::find($this->twitter_id)->toArray();
        $this->assertEquals($twitter_account['following_flag'], 1);
        $this->assertEquals($twitter_account['liking_flag'], 1);
        $this->assertEquals($twitter_account['unfollowing_flag'], 1);
    }
}
