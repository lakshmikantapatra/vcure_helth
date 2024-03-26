<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\AppointmentsFilterModel;
use App\Models\DoctorPracticeDetailsModel;
use App\Models\DoctorPracticeModel;
use App\Models\DoctorsModel;
use App\Models\TempOtpModel;

class Doctor extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function registration()
    {
        $doctorModel = new DoctorsModel();
        $tempOtpModel = new TempOtpModel();
        $formData = $this->request->getPost();
        $data = [];

        $data['email'] = $formData['email'];
        $data['mobile'] = $formData['mobile'];

        $prev = $doctorModel
            ->where('email', $data['email'])
            ->orWhere('mobile', $data['mobile'])
            ->first();
        if (!empty($prev)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'User already exists!',
                'data' => [],
            ]);
        }

        $data['name'] = $formData['name'];
        $data['gender'] = $formData['gender'];

        $dob = $formData['dateOfBirth'];
        [$d, $m, $y] = explode('/', $dob);
        $data['date_of_birth'] = "{$y}-{$m}-{$d}";

        $password = $formData['password'];
        $data['hashed_password'] = password_hash($password, PASSWORD_BCRYPT);

        if (!empty($formData['profileimage'])) {
            $prof_img = $this->request->getFile('profileimage');
            if (!$prof_img->hasMoved()) {
                $newName = 'profile_img_' . time() . $prof_img->getExtension();
                $moved = $prof_img->move('uploads/doctor/profile', $newName);
                if (!$moved) {
                    return $this->response->setJSON([
                        'status' => false,
                        'msg' => 'Profile image upload failed!',
                        'data' => [],
                    ]);
                }
                $data['profileimage'] = $newName;
            }
        }
        if (!empty($formData['kycimage'])) {
            $kyc_img = $this->request->getFile('kycimage');
            if (!$kyc_img->hasMoved()) {
                $newName = 'kyc_img_' . time() . $kyc_img->getExtension();
                $moved = $kyc_img->move('uploads/doctor/kyc', $newName);
                if (!$moved) {
                    return $this->response->setJSON([
                        'status' => false,
                        'msg' => 'KYC image upload failed!',
                        'data' => [],
                    ]);
                }
                $data['kycimage'] = $newName;
            }
        }

        $temp['token'] = unique_token($data['email'] . time());
        $temp['email'] = $data['email'];
        $temp['mobile'] = $data['mobile'];
        $temp['otp'] = random_int(100000, 999999);
        $temp['expiry'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $this->db->transStart();
        $res['status1'] = $doctorModel->insert($data);
        $res['status2'] = $tempOtpModel->insert($temp);
        $this->db->transComplete();

        if ($res['status1'] && $res['status2']) {
            $return['status'] = true;
            $return['msg'] = 'Registration Successful!';
            $return['data']['otp'] = $temp['otp'];
        } else {
            $return['status'] = false;
            $return['msg'] = 'Internal Server Error!';
            $return['data']['otp'] = null;

        }

        return $this->response->setJSON($return);
    }

    public function personalDetails($id)
    {
        $doctorModel = new DoctorsModel();
        $input = $this->request->getJSON();
        $data = [];

        $data['license_no'] = $input->license_no;
        $data['experience_year'] = $input->experience;
        $data['education'] = $input->education;

        $expertise_name = $input->expertise_field;

        $expertise = $this->db->table('expertise')->where('expertise_name', $expertise_name)->first();
        if (!empty($expertise)) {
            $data['expertise_id'] = $expertise['id'];
            $data['expertise_name'] = $expertise['expertise_name'];
        }

        if (!empty($input->bio)) {
            $data['professional_bio'] = $input->bio;
        }
        if (!empty($input->name)) {
            $data['name'] = $input->name;
        }
        if (!empty($input->email)) {
            $data['email'] = $input->email;
        }
        if (!empty($input->mobile)) {
            $data['mobile'] = $input->mobile;
        }

        if (!empty($input->profileimage)) {
            $prof_img = $this->request->getFile('profileimage');
            if (!$prof_img->hasMoved()) {
                $newName = 'profile_img_' . time() . $prof_img->getExtension();
                $moved = $prof_img->move('uploads/doctor/profile', $newName);
                if (!$moved) {
                    return $this->response->setJSON([
                        'status' => false,
                        'msg' => 'Profile image upload failed!',
                        'data' => [],
                    ]);
                }
                $data['profileimage'] = $newName;
            }
        }

        $return['status'] = $doctorModel->update($data, $id);
        $return['msg'] = $return['status'] ? 'Personal Details Updated!' : 'Internal Server Error!';

        return $this->response->setJSON($return);
    }

    public function checkExistingUser($email = '', $phone = '')
    {
        $doctorModel = new DoctorsModel();
        if (empty($email) && empty($phone)) {
            return ([
                'status' => false,
                'data' => [],
            ]);
        }
        $data = $doctorModel
            ->where('email', $email)
            ->orWhere('mobile', $phone)
            ->first();
        if (!empty($data)) {
            return ([
                'status' => true,
                'data' => $data,
            ]);
        } else {
            return ([
                'status' => false,
                'data' => [],
            ]);
        }
    }

    public function login()
    {
        $input = $this->request->getPost();
        $tempOtpModel = new TempOtpModel();

        $email = !empty($input['email']) ? $input['email'] : '';
        $mobile = !empty($input['phone']) ? $input['phone'] : '';

        $existingUser = $this->checkExistingUser($email, $mobile);

        if ($existingUser['status']) {
            $data = $existingUser['data'];
        } else {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'No user with this email or phone!',
                'data' => [],
            ]);
        }

        if (!empty($input['password'])) {
            if (!password_verify($input['password'], $data['hashed_password'])) {
                return $this->response->setJSON([
                    'status' => false,
                    'msg' => 'Invalid Credentials!',
                    'data' => [],
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'msg' => 'Login Successful!',
                'data' => [
                    'user_id' => $data['id'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ],
            ]);
        } else {
            $temp['token'] = unique_token($data['email'] . time());
            $temp['email'] = $data['email'];
            $temp['mobile'] = $data['mobile'];
            $temp['otp'] = random_int(100000, 999999);
            $temp['expiry'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $return['status'] = $tempOtpModel->insert($temp);
            $return['data']['otp'] = $return['status'] ? $temp['otp'] : null;

            return $this->response->setJSON($return);
        }
    }

    public function resetPassword()
    {
        $input = $this->request->getPost();
        $tempOtpModel = new TempOtpModel();

        $email = !empty($input['email']) ? $input['email'] : '';
        $mobile = !empty($input['phone']) ? $input['phone'] : '';

        $existingUser = $this->checkExistingUser($email, $mobile);
        if ($existingUser['status']) {
            $data = $existingUser['data'];
        } else {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'No user with this email or phone!',
                'data' => [],
            ]);
        }
        $temp['token'] = unique_token($data['email'] . time());
        $temp['email'] = $data['email'];
        $temp['mobile'] = $data['mobile'];
        $temp['otp'] = random_int(100000, 999999);
        $temp['expiry'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $return['status'] = $tempOtpModel->insert($temp);
        $return['data']['token'] = $return['status'] ? $temp['token'] : null;

        return $this->response->setJSON($return);
    }

    public function verifyOTP($token)
    {
        $input = $this->request->getJSON();
        $tempOtpModel = new TempOtpModel();
        $doctorModel = new DoctorsModel();

        $otp = !empty($input->otp) ? $input->otp : '';

        $data = $tempOtpModel
            ->where('token', $token)
            ->where('expiry >', date('Y-m-d H:i:s'))
            ->where('otp', $otp)
            ->first();

        if (!empty($data)) {
            $doc_data = $doctorModel->where('email', $data['email'])->first();

            return $this->response->setJSON([
                'status' => true,
                'data' => [
                    'doctor_id' => $doc_data['id'],
                ],
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'data' => [],
            ]);
        }
    }

    public function newPassword($id)
    {

        $input = $this->request->getJSON();
        $doctorModel = new DoctorsModel();

        $password = $input->password;

        if (empty($password)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Please enter password!',
            ]);
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $return['status'] = $doctorModel->update($id, [
            'hashed_password' => $hashed_password,
        ]);
        $return['msg'] = $return['status'] ? 'Password changed successfully!' : 'Failed to change password!';

        return $this->response->setJSON($return);
    }

    public function postPractice($id = '')
    {

        $input = $this->request->getPost();
        $docPracticeModel = new DoctorPracticeModel();

        $data['doctor_id'] = $id;
        $data['type'] = $input['practice_type'];
        $data['price'] = $input['practice_fees'];
        if (!empty($input['clinic_name'])) {
            $data['clinic_name'] = $input['clinic_name'];
        }
        if (!empty($input['contact'])) {
            $data['clinic_mobile'] = $input['contact'];
        }
        if (!empty($input['email'])) {
            $data['clinic_email'] = $input['email'];
        }
        if (!empty($input['clinic_address'])) {
            $data['clinic_address'] = $input['clinic_address'];
        }
        if (!empty($input['pincode'])) {
            $data['clinic_pincode'] = $input['pincode'];
        }

        $return['status'] = $docPracticeModel->insert($data);
        $return['msg'] = $return['status'] ? 'Practice added successfully!' : 'Failed to add practice!';
        $return['data'] = $return['status'] ? [
            'id' => $docPracticeModel->getInsertID(),
            'practice_type' => $data['type'],
            'price' => $data['price'],
        ] : [];

        return $this->response->setJSON($return);
    }

    public function postPracticeSchedule($id = '')
    {
        $input = $this->request->getJSON();
        $practiceDetailsModel = new DoctorPracticeDetailsModel();
        $practiceModel = new DoctorPracticeModel();
        $existingPractice = $practiceModel->find($id);

        if (empty($existingPractice)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid Operation!',
                'data' => [],
            ]);
        }

        $this->db->transStart();
        $data['practice_id'] = $id;
        $return['practiceId'] = $id;
        try {
            foreach ($input as $key => $schedule) {
                foreach ($schedule->days as $day) {
                    $data['day'] = $day;

                    $data['start_time'] = date('H:i', strtotime($schedule->startTime));
                    $data['end_time'] = date('H:i', strtotime($schedule->endTime));

                    if ($data['start_time'] > $data['end_time']) {
                        $this->db->transRollback();
                        return $this->response->setJSON([
                            'status' => false,
                            'msg' => 'Start time should be less than end time!',
                            'data' => $data,
                        ]);
                    }

                    $isConflictingPracticeSchedule = $practiceModel->isConflictingPractice($existingPractice['doctor_id'], $data);

                    if (!empty($isConflictingPracticeSchedule)) {
                        $this->db->transRollback();
                        return $this->response->setJSON([
                            'status' => false,
                            'msg' => 'Time slot already exists!',
                            'data' => $isConflictingPracticeSchedule,
                        ]);
                    }

                    $status = $practiceDetailsModel->insert($data, false);

                    $return['schedules'][$key]['days'][] = $data['day'];
                    $return['schedules'][$key]['startTime'] = $data['start_time'];
                    $return['schedules'][$key]['endTime'] = $data['end_time'];
                }
            }
            $this->db->transComplete();
            return $this->response->setJSON([
                'status' => $status,
                'msg' => $status ? 'Schedules added successfully!' : 'Invalid Operation!',
                'data' => $return,
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'status' => false,
                'msg' => $e->getMessage(),
                'data' => [],
            ]);
        }

    }

    public function getPractice($doc_id = '')
    {
        $docPracticeModel = new DoctorPracticeModel();

        try {
            $data = $docPracticeModel
                ->select('doctor_practice.*, doctor_practice_details.id as details_id, doctor_practice_details.day, doctor_practice_details.start_time, doctor_practice_details.end_time')
                ->where('doctor_practice.doctor_id', $doc_id)
                ->join('doctor_practice_details', 'doctor_practice_details.practice_id = doctor_practice.id', 'left')
                ->findAll();

            $return['status'] = true;
            $return['msg'] = 'Successful!';
            // $return['practices'] = $data;

            foreach ($data as $value) {
                $return['practices'][$value['type']]['practice_id'] = $value['id'];
                $return['practices'][$value['type']]['type'] = $value['type'];
                $return['practices'][$value['type']]['fees'] = (int) $value['price'];
                $return['practices'][$value['type']]['isConsultationSwitched'] = (bool) $value['is_consultation_on'];

                $schedules[$value['type']][$value['start_time'] . '-' . $value['end_time']]['id'] = $value['details_id'];
                $schedules[$value['type']][$value['start_time'] . '-' . $value['end_time']]['days'][] = $value['day'];
                $schedules[$value['type']][$value['start_time'] . '-' . $value['end_time']]['startTime'] = date('h:i a', strtotime($value['start_time']));
                $schedules[$value['type']][$value['start_time'] . '-' . $value['end_time']]['endTime'] = date('h:i a', strtotime($value['end_time']));

                $return['practices'][$value['type']]['schedules'] = array_values($schedules[$value['type']]);
            }
            return $this->response->setJSON($return);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function updatePractice($id = '')
    {
        $input = $this->request->getJSON();
        $docPracticeModel = new DoctorPracticeModel();

        $isPracticeExists = $docPracticeModel->isPracticeExits($id);
        if (!$isPracticeExists) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid Operation!',
                'data' => [],
            ]);
        }

        $data['type'] = $input->practice_type;
        $data['price'] = $input->practice_fees;
        if (!empty($input->clinic_name)) {
            $data['clinic_name'] = $input->clinic_name;
        }
        if (!empty($input->contact)) {
            $data['clinic_mobile'] = $input->contact;
        }
        if (!empty($input->email)) {
            $data['clinic_email'] = $input->email;
        }
        if (!empty($input->clinic_address)) {
            $data['clinic_address'] = $input->clinic_address;
        }
        if (!empty($input->pincode)) {
            $data['clinic_pincode'] = $input->pincode;
        }

        $return['status'] = $docPracticeModel->update($id, $data);
        $return['msg'] = $return['status'] ? 'Practice updated successfully!' : 'Invalid Operation!';

        $return['data'] = $return['status'] ? [
            'id' => $id,
            'practice_type' => $data['type'],
            'email' => $data['clinic_email'] ?? '',
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ] : [];

        return $this->response->setJSON($return);
    }

    public function updatePracticeSchedule($id = '')
    {
        $input = $this->request->getJSON();
        $practiceDetailsModel = new DoctorPracticeDetailsModel();
        $practiceModel = new DoctorPracticeModel();
        $existingPractice = $practiceModel->find($id);

        if (empty($existingPractice)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid Operation!',
                'data' => [],
            ]);
        }

        $this->db->transStart();
        $data['practice_id'] = $id;
        $return['practiceId'] = $id;
        try {
            foreach ($input as $key => $schedule) {
                foreach ($schedule->days as $day) {
                    $data['day'] = $day;
                    $data['id'] = !empty($schedule->id) ? $schedule->id : '';

                    $data['start_time'] = date('H:i', strtotime($schedule->startTime));
                    $data['end_time'] = date('H:i', strtotime($schedule->endTime));

                    if ($data['start_time'] > $data['end_time']) {
                        $this->db->transRollback();
                        return $this->response->setJSON([
                            'status' => false,
                            'msg' => 'Start time should be less than end time!',
                            'data' => $data,
                        ]);
                    }

                    $isConflictingPracticeSchedule = $practiceModel->isConflictingPractice($existingPractice['doctor_id'], $data);

                    if (!empty($isConflictingPracticeSchedule)) {
                        $this->db->transRollback();
                        return $this->response->setJSON([
                            'status' => false,
                            'msg' => 'Time slot already exists!',
                            'data' => $isConflictingPracticeSchedule,
                        ]);
                    }

                    $status = $practiceDetailsModel->save($data, false);

                    $return['schedules'][$key]['days'][] = $data['day'];
                    $return['schedules'][$key]['startTime'] = $data['start_time'];
                    $return['schedules'][$key]['endTime'] = $data['end_time'];
                }
            }
            $this->db->transComplete();
            return $this->response->setJSON([
                'status' => $status,
                'msg' => $status ? 'Schedules updated successful!' : 'Invalid Operation!',
                'data' => $return,
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'status' => false,
                'msg' => $e->getMessage(),
                'data' => [],
            ]);
        }

    }

    public function deletePractice($id = '')
    {
        $docPracticeDetailsModel = new DoctorPracticeDetailsModel();
        $docPracticeModel = new DoctorPracticeModel();
        $isPracticeExists = $docPracticeModel->isPracticeExits($id);
        if (!$isPracticeExists) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid Operation!',
                'data' => [],
            ]);
        }

        $this->db->transStart();
        try {
            $docPracticeModel->delete($id);
            $docPracticeDetailsModel->where('practice_id', $id)->delete();
            $this->db->transComplete();
            $return['status'] = true;
            $return['msg'] = 'Practice deleted successfully!';
            return $this->response->setJSON($return);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'status' => false,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    public function getAppointmentsFilters()
    {
        $appointmentsFilterModel = new AppointmentsFilterModel();

        $filters = $appointmentsFilterModel->getFilters();

        if ($filters) {
            return $this->response->setJSON([
                'status' => true,
                'msg' => 'Filters fetched successfully!',
                'data' => $filters,
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid Operation!',
                'data' => [],
            ]);
        }
    }

    public function getExpertise()
    {
        $expertiseTable = $this->db->table('expertise');

        $data = $expertiseTable
            ->select('id, expertise_name')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status' => !empty($data) ? true : false,
            'msg' => !empty($data) ? 'Expertise fetched successfully!' : 'Invalid Operation!',
            'data' => $data,
        ]);
    }

    public function getDoctorsByExpertise($id = '')
    {
        $input = $this->request->getJSON();

        $where = '';
        if (!empty($input->location)) {
            $locationArr = explode(',', $input->location);
            $pincode = [];
            $location = [];
            foreach ($locationArr as $loc) {
                if ((int) $loc !== 0) {
                    $pincode[] = $loc;
                } else {
                    $location[] = $loc;
                }
            }
            $where = "d.expertise_id = {$id} AND (dp.clinic_address IN(" . '"' . join(',', $location) . '"' . ") OR dp.clinic_pincode IN(" . '"' . join(',', $pincode) . '"' . "))";
        } else {
            $where = "d.expertise_id = {$id}";
        }

        try {
            $data = $this->db->table('doctors as d')
                ->distinct()
                ->select('d.id as docid, d.name as doc_name, dp.clinic_address, dp.type as practice_type, dp.clinic_name, dp.price as fees, dp.clinic_pincode as pin, dr.total_rating ,dr.total_review')
                ->where($where)
                ->join('doctor_practice as dp', 'dp.doctor_id = d.id', 'left')
                ->join("doctor_rating dr", "d.id=dr.doctor_id", "left")
                ->get()->getResultArray();

            foreach ($data as $key => $d) {
                $res[$d['docid']]['docid'] = $d['docid'];
                $res[$d['docid']]['doc_name'] = $d['doc_name'];
                $res[$d['docid']]['doc_offered_appointment_type'][] = $d['practice_type'];
                if ($d['total_rating'] && $d['total_review']) {
                    $res[$d['docid']]['rating'] = round($d['total_rating'] / $d['total_review'], 2);
                } else {
                    $res[$d['docid']]['rating'] = 0;
                }
            }

            return $this->response->setJSON([
                'status' => true,
                "msg" => "Data fetch Successful!",
                'data' => $res,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                "msg" => $e->getMessage(),
                'data' => [],
            ]);
        }
    }
}
