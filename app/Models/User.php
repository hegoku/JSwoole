<?php
namespace App\Models;

use JSwoole\Database\Model;

class UserModel extends Model
{
    protected $connection = 'default';
    protected $table = 'user';
    public $timestamps=false;
}