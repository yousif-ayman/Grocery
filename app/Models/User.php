<?php

namespace App\Models;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{ 
    public const USERNAME_MAX_LENGTH = 50;
    use HasApiTokens, HasFactory;

   protected $fillable = [
    'username',
    'firstname',
    'lastname',
    'gender',
    'birthday',
    'email',
    'phone',
    'country_code',
    'profile_image',
    'password',
    'email_verified',
    'phone_verified',
    'email_verified_at',
    'phone_verified_at',
    'agree_terms',
    'is_active',
    'is_admin',
    'stripe_customer_id',
    'google_id',
    'avatar',
    'loyalty_points',
    'store_credits',
    'preferred_languages',
    'app_language',
    'app_theme',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public static function findByIdentifier(string $identifier): ?self
{
    $identifier = trim($identifier);

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $identifier = strtolower($identifier);
    } else {
        $identifier = preg_replace('/\s+/', '', $identifier);
    }

    return static::where('email', $identifier)
        ->orWhere('phone', $identifier)
        ->first();
}

    /**
     * SRS-07: Multiple delivery addresses support
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
 
        }
        public function cart(): HasOne
{
    return $this->hasOne(Cart::class);
}
public function getOrCreateCart(): Cart
{
    return $this->cart()->firstOrCreate([
        'user_id' => $this->id,
    ]);
}
}