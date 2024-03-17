<?php

namespace App\Models;

use CodeIgniter\Model;

class TempOtpModel extends Model
{
    protected $table = 'password_temp_tbl';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // protected $allowedFields = ['name', 'email'];

    protected bool $allowEmptyInserts = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
