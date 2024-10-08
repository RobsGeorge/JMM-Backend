<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class PersonHafez extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'PersonID',
        'HafezValue',
        'HafezDate',
        'HafezReason'
    ];

    protected $table = "PesonHawafez";
    protected $primaryKey = 'HafezID';

    public $timestamps = false;
}
