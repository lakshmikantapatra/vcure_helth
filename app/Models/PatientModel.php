<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table = 'patients';
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

    public function isPatientExits($id = '')
    {
        $data = $this->find($id);
        if (!empty($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function isEmailExits($email = '')
    {
        $data = $this->where('email', $email)->first();
        if (!empty($data)) {
            return true;
        } else {
            return false;
        }
    }
}
