# Laravel Multi Token

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

## Introduction

This package provides a simple solution for give multiple token to your application's users. This solution is similar to Laravel's TokenGuard class.

This package is for Laravel 5.5 and above.

## Installation

This project is not (yet) on packagist, so to install it, add the repository directly:
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ctyler-liquidfish/api-multi-tokens"
    }
],
```
then
```
composer require ctyler-liquidfish/laravel-tokens
```

The service provider will automatically get registered for Laravel 5.5 and above versions. If you want you can add the service provider in `config/app.php` file:

```php
'providers' => [
    // ...
    Liquidfish\ApiMultiToken\TokenServiceProvider::class,
];
```

Publish the `migration` file with:

```
php artisan vendor:publish --provider="Liquidfish\ApiMultiToken\TokenServiceProvider" --tag="laravel-tokens-migrations"
```

Then, you can create the `tokens` table by running the migrations:

```
php artisan migrate
```

You can publish the config file with:

```
php artisan vendor:publish --provider="Liquidfish\ApiMultiToken\TokenServiceProvider" --tag="laravel-tokens-config"
```

If you need you are free to change your `config` file.

## Implementation

After installation, you can implement the new feature for your application.

Add the `Liquidfish\ApiMultiToken\HasToken` trait to your `App\User` model. This trait will provide a few helper methods to your model which allow you to inspect the authenticated user's tokens:

```php
<?php

namespace App;

use Liquidfish\ApiMultiToken\HasToken;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasToken, Notifiable;
    
    // ...
}
```

And finally, you will add the new guard to your application. Open the `config/auth.php` file and apply following changes:

```php
  'guards' => [
        // ...

        'api' => [
            'driver' => 'multi-token',
            'provider' => 'tokens',
        ],
    ],

    'providers' => [
        // ...

        'tokens' => [
            'driver' => 'eloquent',
            'model' => Liquidfish\ApiMultiToken\Token::class,
        ],
    ],
```

Congratulations!

## Usage

When you need it (after login or any actions later), use the helper function to create a new token.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TokenController
{
     public function __invoke(Request $request)
     {
         $token = $request->user()->generateToken();
         
         return $token;
     }
}
```

By default tokens never expire if you do not pass the lifetime when generation. For define expiration, you can pass the time period parameter (as Carbon) to `generateToken` method.

Generate a new token of 10 minutes life with:

```php
$token = $request->user()->generateToken(now()->addMinutes(10));
```

The tokens are not refreshed, tokens will die when expired. The authentication attempts with expired token will fail.

Tokens can be revoked, such as in a logout function:
```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogoutController
{
    public function logout(Request $request)
    {
        // ...
        $token = $request->token();
        $token->revoke();
        // ...
    }
}
```

The authentication process is similar to that of the standard Laravel api_token flows:

The token guard is looking for the token:

1. Firstly looking the URL for parameter `?api_token=XXX`
2. Secondly for an input field `api_token`
3. Thirdly looking the header for `Authorization: Bearer XXX`
4. Lastly looking for a header `PHP_AUTH_PW: XXX`

Finally, if you need the current token model information underlying the authentication process, you can use the `token` method.


```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController
{
     public function __construct()
     {
          $this->middleware('auth:api');
     }
     
     public function __invoke(Request $request)
     {
         return [
             'user' => $request->user(),
             'token' => $request->token(),
         ];
     }
}

```

## Token Generator

By default, the generated token is a string of random 36 chars. If you want to create more meaningful (such as uuid4) tokens, you are free to change the generator method.

Let's make change to generate of `uuid4` string. Open the `app/Providers/AuthServiceProvider` file and apply the additions:

```php
<?php

namespace App\Providers;

use Liquidfish\ApiMultiToken\Generator;
// ...

class AuthServiceProvider extends ServiceProvider
{
    // ...

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // ...

        Generator::extend(function () {
            return \Ramsey\Uuid\Uuid::uuid4()->toString();
        });
    }
}
```

If there is no `ramsey/uuid` package in your application, you can install with:

```
composer require ramsey/uuid
```

Cheers.

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Security Vulnerabilities

If you discover any security related issues, please create a new issue with using the "Bug" label. All security vulnerabilities will be promptly addressed.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
