<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'DepartmentID', 'DepartmentName', 'DepartmentDescription'
    ];
    protected $primaryKey = 'DepartmentID';
    protected $table = "DepartmentTable";
    public $timestamps = false;

    public function persons()
    {
        return $this->hasMany(Person::class, 'DepartmentID', 'PersonDepartment');
    }
}
