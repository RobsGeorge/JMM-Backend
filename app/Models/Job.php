<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'JobID', 'JobName', 'JobDescription'
    ];
    protected $table = 'JobsTable';
    protected $primaryKey = 'DepartmentID';
    public $timestamps = false; 
}
