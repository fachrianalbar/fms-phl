<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Bank\ConfigBank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'username',
        'password',
        'roleCode',
        'language'
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'username' => $this->username,
            'name' => $this->name
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleCode', 'code');
    }

    public function configBank()
    {
        return $this->hasMany(ConfigBank::class, 'userCode', 'code');
    }

    public function balance()
    {
        $balance = 0;

        $userBank = ConfigBank::where('userCode', Auth::user()->code)->pluck('userBankCode')->toArray();

        $liveMutation = LiveMutation::whereIn('userBankCode', $userBank)->get();

        if (count($liveMutation) == 0) {
            return null;
        }

        foreach ($liveMutation as $item) {
            $balance += $item->balance;
        }

        return number_format($balance, 0, ',', '.');
    }

    public function listBalance()
    {
        $userBank = ConfigBank::where('userCode', Auth::user()->code)->pluck('userBankCode')->toArray();

        $liveMutation = LiveMutation::whereIn('userBankCode', $userBank)->with(['userBank', 'userBank.bank'])->get();

        return $liveMutation;
    }
}
