<?php

namespace Liquidfish\ApiMultiToken;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Trait HasToken
 * @property Token  $token      When authenticated via token, contains the authenticating token.
 * @property \Illuminate\Support\Collection|Token[]  $tokens
 * @mixin Model
 */
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
     * @param \DateTime|Carbon|null $expiration
     * @param Model|null $model
     * @return string
     */
    public function generateToken(?\DateTime $expiration = null, &$model = null)
    {
        $token = Generator::generate();
        $data = [
            'api_token' => $token,
            'expired_at' => $expiration,
        ];
        $secureLength = config('laravel-tokens.secure_length');
        if(!empty($secureLength)){
            $secure = Str::random($secureLength);
            $token .= $secure;
            $data['hash'] = Hash::make($secure);
        }
        $model = $this->tokens()->create($data);
        return $token;
    }
}
