<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class APITest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function setUp() { parent::setUp(); $this->refreshApplication(); }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->get('/');

        $this->assertEquals(
            $this->app->version(), $this->response->getContent()
        );
    }

    public function testRegisterRouteResponds()
    {
        $this->json('POST', '/register', [
            'username'     => 'osirun',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])
        ->seeJson([
            'created'      => true
        ]);
    }

    public function testRegistrationSavesToDb()
    {
        $this->json('POST', '/register', [
            'username'     => 'osirun',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ]);

        $users = app('db')->select('SELECT * FROM users');

        $this->assertEquals(count($users), 1);
    }

    public function testValuesSubmittedInRegistrationMustBeUnique()
    {
        $this->json('POST', '/register', [
            'username'     => 'osirun',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ]);

        $this->json('POST', '/register', [
            'username'     => 'osirun',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 0000000' 
        ])->seeJSON([
            'username' => ['The username has already been taken.'],
            'email' => ['The email has already been taken.']
        ]);

        $users = app('db')->select('SELECT * FROM users');

        $this->assertEquals(count($users), 1);
    }

    public function testUsernameMustBetween3And9Characters()
    {
        $this->json('POST', '/register', [
            'username'     => 'o',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'username' => ['The username must be at least 3 characters.']
        ]);

        $this->json('POST', '/register', [
            'username'     => 'osirunosirunosirun',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'username' => ['The username may not be greater than 9 characters.']
        ]);

        $this->json('POST', '/register', [
            'username'     => 'osi',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'created' => true
        ]);

        $this->json('POST', '/register', [
            'username'     => 'osirunosi',
            'email'        => 'osirun2@gmail.com',
            'phone_number' => '07979 000000' 
        ])->seeJSON([
            'created' => true
        ]);
    }

    public function testUsernameCantContainCatUnlessPartOfCatfishOrScatter()
    {
        $this->json('POST', '/register', [
            'username'     => 'cat',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'username' => ['The username may not contain cat except as part of scatter or catfish.']
        ]);

        $this->json('POST', '/register', [
            'username'     => 'catfish',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'created' => true
        ]);

        $this->json('POST', '/register', [
            'username'     => 'scatter79',
            'email'        => 'scatter@gmail.com',
            'phone_number' => '07979 000000' 
        ])->seeJSON([
            'created' => true
        ]);
    }

    public function testUsernameCantContainDogUnlessPartOfBulldog()
    {
        $this->json('POST', '/register', [
            'username'     => 'dog',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'username' => ['The username may not contain dog except as part of bulldog.']
        ]);

        $this->json('POST', '/register', [
            'username'     => 'bulldog',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'created' => true
        ]);
    }

    public function testUsernameCantContainHorseUnlessPartOfSeahorse()
    {
        $this->json('POST', '/register', [
            'username'     => 'horse',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'username' => ['The username may not contain horse except as part of seahorse.']
        ]);

        $this->json('POST', '/register', [
            'username'     => 'seahorse',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'created' => true
        ]);
    }

    public function testUsernameValidationIsCaseInsensitive()
    {
        $this->json('POST', '/register', [
            'username'     => 'CAt',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'username' => ['The username may not contain cat except as part of scatter or catfish.']
        ]);

        $this->json('POST', '/register', [
            'username'     => 'caTFish',
            'email'        => 'osirun@gmail.com',
            'phone_number' => '07979 121232' 
        ])->seeJSON([
            'created' => true
        ]);

        $this->json('POST', '/register', [
            'username'     => 'sCATTer79',
            'email'        => 'scatter@gmail.com',
            'phone_number' => '07979 000000' 
        ])->seeJSON([
            'created' => true
        ]);
    }

    public function testLatitudeAndLongitudeCanBeUpdated()
    {
        $this->json('POST', '/register', [
            'username'     => 'sCATTer79',
            'email'        => 'scatter@gmail.com',
            'phone_number' => '07979 000000' 
        ]);

        $this->json('POST', '/user/setposition', [
            'username'     => 'sCATTer79',
            'latitude'     => 51.510358, 
            'longitude'    => -0.132484,
        ])->seeJSON([
            'updated' => true
        ]);

        $result = app('db')->select('SELECT * FROM users where username = "sCATTer79"');
        $user = array_shift($result);

        $this->assertEquals($user->latitude, 51.510358);
        $this->assertEquals($user->longitude, -0.132484);

    }

    public function testUserDetailsEndpoint()
    {
        $this->json('POST', '/register', [
            'username'     => 'sCATTer79',
            'email'        => 'scatter@gmail.com',
            'phone_number' => '07979 000000' 
        ]);

        $this->json('POST', '/user/setposition', [
            'username'     => 'sCATTer79',
            'latitude'     => 51.4665, 
            'longitude'    => 0.0259,
        ])->seeJSON([
            'updated' => true
        ]);

        $this->json('POST', '/user/show', [
            'username'     => 'sCATTer79'
        ])->seeJSON([
            'email' => 'scatter@gmail.com',
            'location' => 'http://maps.google.com/maps?q=51.4665,0.0259'
        ]);
    }
}
