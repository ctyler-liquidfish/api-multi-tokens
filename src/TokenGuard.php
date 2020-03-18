<?php

namespace Liquidfish\ApiMultiToken;

use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class TokenGuard extends \Illuminate\Auth\TokenGuard
{
    /**
     * The currently authenticated token.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $token;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $inputKey
     * @param  string  $storageKey
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        $inputKey = 'api_token',
        $storageKey = 'api_token'
    )
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->storageKey = $storageKey;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }
        $user = null;

        $token = $this->token();

        if (! empty($token)) {
            $user = $token->user;
        }

        return $this->user = $user;
    }

    /**
     * Get the currently token model.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function token()
    {
        if (! is_null($this->token)) {
            return $this->token;
        }

        return $this->token = $this->retrieveToken(
            $this->getTokenForRequest()
        );
    }

    protected function retrieveToken($requestToken)
    {
        $hash = null;
        if ($secured = !empty($secureLength = config('laravel-tokens.secure_length'))) {
            $hash = substr($requestToken, -$secureLength);
            $requestToken = substr($requestToken, 0, -$secureLength);
        }

        if (empty($requestToken)) return null;

        /** @var Token $token */
        $token = $this->provider->retrieveByCredentials([
            $this->storageKey => $requestToken,
        ]);

        if (! empty ($token)) {
            if ($token->isExpired()) return false;
            if ($secured && ! Hash::check($hash, $token->hash)) return false;
        }

        return $token;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        if ($this->retrieveToken($credentials[$this->inputKey])) {
            return true;
        }

        return false;
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
