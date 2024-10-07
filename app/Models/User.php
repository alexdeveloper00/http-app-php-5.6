<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class User extends BaseModel {
    protected $table = 'users';
    protected $connection = 'default';
    public $timestamps = false;
}