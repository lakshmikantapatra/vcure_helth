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

    public function isConflictingPractice($doctor_id = '', $schedule = [])
    {
        $return = [];
        // $start_time = date('h:i', strtotime($schedule['start_time']));
        // $end_time = date('h:i', strtotime($schedule['end_time']));
        $details = $this->db->table('doctor_practice dp')
            ->select('dp.doctor_id, dp.type, dpd.day, dpd.start_time, dpd.end_time')
            ->where('dp.doctor_id', $doctor_id)
            ->where('dpd.day', $schedule['day'])
            ->where('dpd.start_time >=', $schedule['start_time'])
            ->where('dpd.start_time <=', $schedule['end_time'])
            ->join('doctor_practice_details dpd', 'dpd.practice_id = dp.id')
            ->get()->getResultArray();

        if (!empty($details)) {
            $return[$schedule['day']] = [
                'day' => $schedule['day'],
                'doctor_id' => $doctor_id,
                'start_time' => $details[0]['start_time'],
                'end_time' => $details[0]['end_time'],
            ];
        }

        return $return;
    }
}
