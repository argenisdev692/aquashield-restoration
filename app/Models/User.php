<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use HasRoles;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'last_name',
        'username',
        'date_of_birth',
        'email',
        'password',
        'phone',
        'address',
        'zip_code',
        'city',
        'state',
        'country',
        'gender',
        'profile_photo_path',
        'terms_and_conditions',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the company data associated with the user.
     */
    public function companyData(): HasOne
    {
        return $this->hasOne(CompanyData::class);
    }

    /**
     * Get the blog categories created by the user.
     */
    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    /**
     * Get the posts created by the user.
     */
    /**
     * Get the email data created by the user.
     */
    public function emailData(): HasMany
    {
        return $this->hasMany(EmailData::class);
    }

    /**
     * Get the AI models created by the user.
     */
    public function modelAIs(): HasMany
    {
        return $this->hasMany(ModelAI::class);
    }

    /**
     * Get the invoice demos created by the user.
     */
    public function invoiceDemos(): HasMany
    {
        return $this->hasMany(InvoiceDemo::class);
    }

    /**
     * Get the alliance companies created by the user.
     */
    public function allianceCompanies(): HasMany
    {
        return $this->hasMany(AllianceCompany::class);
    }

    /**
     * Get the insurance companies created by the user.
     */
    public function insuranceCompanies(): HasMany
    {
        return $this->hasMany(InsuranceCompany::class);
    }

    /**
     * Get the public companies created by the user.
     */
    public function publicCompanies(): HasMany
    {
        return $this->hasMany(PublicCompany::class);
    }

    /**
     * Get the zones created by the user.
     */
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    /**
     * Get the project types created by the user.
     */
    public function projectTypes(): HasMany
    {
        return $this->hasMany(ProjectType::class);
    }

    /**
     * Get the service categories created by the user.
     */
    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    /**
     * Get the W9 forms created by the user.
     */
    public function w9Forms(): HasMany
    {
        return $this->hasMany(W9Form::class);
    }

    /**
     * Get the mortgage companies created by the user.
     */
    public function mortgageCompanies(): HasMany
    {
        return $this->hasMany(MortgageCompany::class);
    }

    /**
     * Get the public adjusters created by the user.
     */
    public function publicAdjusters(): HasMany
    {
        return $this->hasMany(PublicAdjuster::class);
    }

    /**
     * Get the claims created by this user.
     */
    public function createdClaims(): HasMany
    {
        return $this->hasMany(Claim::class, 'created_by');
    }

    /**
     * Get the claims updated by this user.
     */
    public function updatedClaims(): HasMany
    {
        return $this->hasMany(Claim::class, 'updated_by');
    }

    /**
     * Get the salesperson signatures where this user is the salesperson.
     */
    public function salespersonSignatures(): HasMany
    {
        return $this->hasMany(SalespersonSignature::class, 'salesperson_id');
    }

    /**
     * Get the signatures registered by this user.
     */
    public function registeredSignatures(): HasMany
    {
        return $this->hasMany(SalespersonSignature::class, 'user_id_ref_by');
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Clear cache on user model changes
        static::saved(function ($user) {
            self::clearUserCache();
        });

        static::deleted(function ($user) {
            self::clearUserCache();
        });

        static::restored(function ($user) {
            self::clearUserCache();
        });
    }

    /**
     * Clear cache related to users
     */
    public static function clearUserCache()
    {
        // Clear common prefixed keys
        $cacheKeys = Cache::get('user_cache_keys', []);
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Reset the list
        Cache::put('user_cache_keys', [], 86400);
    }
}