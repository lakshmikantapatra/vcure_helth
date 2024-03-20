<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentsFilterModel extends Model
{
    protected $table = 'appointments_filter';
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

    public function getFilters()
    {
        $data = $this->findAll();
        if (!empty($data)) {
            return [
                'filter_name' => $data['filter_name'],
                'filter_icon' => $data['filter_icon'],
            ];
        } else {
            return [];
        }
    }
}
