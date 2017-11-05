<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function show(Request $request)
    {
        $username = $request->input('username');

        $result = app('db')->select('select * from users where username = ?', [$username]);
        $user = array_shift($result);

        return [
            'username' => $user->username,
            'email'    => $user->email,
            'phone_number' => $user->phone_number,
            'location' => 'http://maps.google.com/maps?q=' . $user->latitude . "," . $user->longitude
        ];
    }

    public function store(Request $request) 
    {
        $this->validate($request, [
            'username'     => 'required|unique:users|min:3|max:9|unacceptableunless:cat,scatter,catfish'
                           .  '|unacceptableunless:dog,bulldog'
                           .  '|unacceptableunless:horse,seahorse',
            'email'        => 'required|email|unique:users',
            'phone_number' => 'required|unique:users'
        ]);

        $username     = $request->input('username');
        $email        = $request->input('email');
        $phone_number = $request->input('phone_number');

        $result = app('db')->insert('insert into users (username, email, phone_number) values (?, ?, ?)',
            [$username, $email, $phone_number]);

        return ['created' => true];
    }

    public function update(Request $request)
    {
         $this->validate($request, [
            'username'     => 'required|exists:users',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric'
         ]);

         $username     = $request->input('username');
         $latitude     = $request->input('latitude');
         $longitude    = $request->input('longitude');

         $result = app('db')->update('update users set latitude = ?, longitude = ? where username = ?', 
            [$latitude, $longitude, $username]);

         return ['updated' => true];
    }
}
