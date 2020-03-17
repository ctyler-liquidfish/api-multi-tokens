<?php

namespace Liquidfish\ApiMultiToken;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait HasToken
{
    /**
     * Get the tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(config('laravel-tokens.token'));
    }

    /**
     * Generate a new token and returns it.
     *
     * @param  \DateTime|Carbon|null  $expiration
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function generateToken(?\DateTime $expiration = null)
    {
        $token = Generator::generate();
        $data = [
            'api_token' => $token,
            'expired_at' => $expiration,
        ];
        $secureLength = config('laravel-tokens.secure_length');
        if(!empty($secureLength)){
            $token .= $secure = Str::random($secureLength);
            $data['hash'] = Hash::make($secure);
        }
        $this->tokens()->create($data);
        return $token;
    }
}
