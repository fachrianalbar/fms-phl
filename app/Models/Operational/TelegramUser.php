<?php

namespace App\Models\Operational;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory, Uuid;

    protected $table = 'telegram_user';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'chatId',
        'username',
        'firstName',
        'lastName'
    ];
}
