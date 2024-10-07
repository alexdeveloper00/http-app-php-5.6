<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class General extends BaseModel {
    protected $table = 'generals';
    protected $connection = 'test2';
    public $timestamps = false;
}