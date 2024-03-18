<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorsModel extends Model
{
    protected $table = 'doctors';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $protectFields = false;
    protected $allowedFields;

    protected bool $allowEmptyInserts = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
