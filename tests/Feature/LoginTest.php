<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;

class LoginTest extends TestCase
{
    public function testUserCanViewLoginForm()
    {
        // get login
        $response = $this->get('/login');

        // successful takde error
        $response->assertSuccessful();

        // dapat view
        $response->assertViewIs('auth.login');
    }

    public function testDisplayErrors()
    {
        // post login, empty data
        $response = $this->post('/login', []);

        // expect status 302
        $response->assertStatus(302);

        //error email
        $response->assertSessionHasErrors('email');
    }

    public function testUserCannotViewLoginFormWhenAuthenticated()
    {
        // act as auth user
        $user = factory(User::class)->make();

        // user go to login form
        $response = $this->actingAs($user)->get('/login');

        //redirect to home
        $response->assertRedirect('/home');
    }

    public function testUserCanLoginWithCorrectCredentials()
    {
        // create user
        $user = factory(User::class)->create(
            [
                'password' => bcrypt($password = "commonroom"),
            ]
        );

        // post login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password
        ]);

        // redirect to home
        $response->assertRedirect('/home');

        //check user still auth
        $this->assertAuthenticatedAs($user);
    }

    public function testUserCannotLoginWithWrongCredentials()
    {
        // create user
        $user = factory(User::class)->create(
            [
                'password' => bcrypt($password = "commonroom"),
            ]
        );

        // post login
        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'roomcoomon'
        ]);

        // redirect to login
        $response->assertRedirect('/login');

        //error, email
        $response->assertSessionHasErrors('email');

        //old email still there
        $this->assertTrue(session()->hasOldInput('email'));

        //password is not there
        $this->assertFalse(session()->hasOldInput('password'));

        // check user still guest
        $this->assertGuest();
    }
}
