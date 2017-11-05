<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('unacceptableunless', function($attribute, $value, $parameters) {
            $banned = array_shift($parameters);
            foreach ($parameters as $allowed) {
              $value = str_ireplace($allowed, '', $value);
            }
            return stripos($value, $banned) === FALSE; 
        });

        Validator::replacer('unacceptableunless', function($message, $attribute, $value, $parameters) {
          $banned = array_shift($parameters);
          $allowed = implode(" or ", $parameters);
          return str_replace([':attribute', ':banned', ':allowed'], [$attribute, $banned, $allowed], $message);
        });
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
