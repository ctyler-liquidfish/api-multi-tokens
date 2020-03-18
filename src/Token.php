<?php

namespace Liquidfish\ApiMultiToken;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Token
 * @package Liquidfish\ApiMultiToken
 * @property string $api_token
 * @property string $hash
 * @property Carbon $expired_at
 */
class Token extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'api_token',
        'hash',
        'expired_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['expired_at'];

    /**
     * Determine if the token is expired or not.
     *
     * @return bool
     */
    public function isExpired()
    {
        if (is_null($this->expired_at)) {
            return false;
        }

        return now()->greaterThan($this->expired_at);
    }

    /**
     * Determine if the token is not expired.
     *
     * @return bool
     */
    public function isNotExpired()
    {
        return ! $this->isExpired();
    }

    public function revoke()
    {
        $this->update(['expired_at'=>now()]);
    }

    /**
     * Get the user that owns the token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('laravel-tokens.user'));
    }
}
