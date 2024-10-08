<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mohafza extends Model
{
    use HasFactory;

    protected $fillable = [
        'MohafzaID', 'MohafzaName'
    ];

    protected $primaryKey = 'MohafzaID';
    protected $table = 'MohafazatTable';
    public $timestamps = false;

    public static function getByID($id)
    {
        return self::where('MohafzaID', $id)->first();
    }
}
