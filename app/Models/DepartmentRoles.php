<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentRoles extends Model
{
    use HasFactory;

    protected $fillable = ['DepartmentRoleID', 'DepartmentRoleName', 'DepartmentRoleDescription'];
    protected $table = 'DepartmentRolesTable';
    protected $primaryKey = 'DepartmentRoleID';
    public $timestamps = false;
}
