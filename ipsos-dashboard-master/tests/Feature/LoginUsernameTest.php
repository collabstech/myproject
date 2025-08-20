<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginUsernameTest extends TestCase
{
    private $url = '/login';

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testValidLogin()
    {
        $data = [
            'username' => 'ibrahim.nababan',
            'password' => '123123',
        ];

        $response = $this->json('POST', $this->url, $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'name',
            'username',
            'role',
        ]);
    }

    public function testInvalidLogin()
    {
        $data = [
            'username' => 'unit.test',
            'password' => '123123',
        ];

        $response = $this->json('POST', $this->url, $data);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'code',
            'message',
        ]);
    }

    public function testEmptyField()
    {
        $data = [
            'username' => '',
            'password' => '',
        ];

        $response = $this->json('POST', $this->url, $data);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'code',
            'message',
        ]);
    }

    public function testEmptyUsername()
    {
        $data = [
            'username' => '',
            'password' => '123123',
        ];

        $response = $this->json('POST', $this->url, $data);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'code',
            'message',
        ]);
    }

    public function testEmptyPassword()
    {
        $data = [
            'username' => 'unit.test',
            'password' => '',
        ];

        $response = $this->json('POST', $this->url, $data);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'code',
            'message',
        ]);
    }
}
