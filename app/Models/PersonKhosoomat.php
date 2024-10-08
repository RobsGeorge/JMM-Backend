<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class PersonKhosoomat extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'KhasmValue',
        'KhasmDate',
        'KhasmReason'
    ];

    protected $table = "PersonKhosoomat";
    protected $primaryKey = 'KhasmID';

    public $timestamps = false;

}
