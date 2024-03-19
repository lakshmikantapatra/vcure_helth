<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorPracticeModel extends Model
{
    protected $table = 'doctor_practice';
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

    public function isPracticeExits($id = '')
    {
        $data = $this->find($id);

        if (!empty($data)) {
            return true;
        } else {
            return false;
        }
    }
    public function isDoctorPracticeExits($id = '', $type = '')
    {
        $data = $this->where('doctor_id', $id)->orWhere('type', $type)->first();

        if (!empty($data)) {
            return true;
        } else {
            return false;
        }
    }
}
