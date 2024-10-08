<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = ['PersonID', 'Salary', 'VariableSalary', 'IsPerDay', 'UpdateTimestamp'];
    protected $table = 'PersonSalary';
    protected $primaryKey = 'PersonID';
    public $timestamps = true;
}
