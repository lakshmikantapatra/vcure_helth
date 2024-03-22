<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\AppointmentsModel;
use App\Models\PatientModel;
use App\Models\TempOtpModel;
use DateTime;

class Patient extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function register()
    {
        $input = $this->request->getJSON();
        $patientModel = new PatientModel();
        $tempOtpModel = new TempOtpModel();

        $email = $input->email;
        $isEmailExists = $patientModel->isEmailExits($email);
        if ($isEmailExists) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Email already exists.',
                'data' => [],
            ]);
        }
        $data = [
            'first_name' => $input->first_name,
            'last_name' => $input->last_name,
            'email' => $email,
            'mobile' => $input->mobile,
            'gender' => $input->gender,
        ];

        if (!empty($input->memberId)) {
            $data['member_id'] = $input->memberId;
        }
        $dob = $input->date_of_birth;
        [$d, $m, $y] = explode('/', $dob);
        $data['date_of_birth'] = $y . '-' . $m . '-' . $d;

        $password = $input->password;
        $data['hashed_password'] = password_hash($password, PASSWORD_BCRYPT);

        $temp['token'] = unique_token($data['email'] . time());
        $temp['email'] = $data['email'];
        $temp['mobile'] = $data['mobile'];
        $temp['otp'] = random_int(100000, 999999);
        $temp['expiry'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $this->db->transStart();
        try {
            $patientModel->insert($data);
            $tempOtpModel->insert($temp);
            $this->db->transComplete();
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Registered successfully.',
                'data' => [
                    'token' => $temp['token'],
                    'otp' => $temp['otp'],
                ],
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    public function verifyOTP($token)
    {
        $input = $this->request->getJSON();
        $tempOtpModel = new TempOtpModel();
        $patientModel = new PatientModel();

        $otp = $input->verify_code;
        if (empty($otp)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Please enter OTP!',
                'data' => [],
            ]);
        }

        $data = $tempOtpModel
            ->where('token', $token)
            ->where('expiry >', date('Y-m-d H:i:s'))
            ->where('otp', $otp)
            ->first();

        if (!empty($data)) {
            $patient = $patientModel->where('email', $data['email'])->first();

            return $this->response->setJSON([
                'status' => true,
                'data' => [
                    'patient_id' => $patient['id'],
                ],
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'data' => [],
            ]);
        }
    }

    public function login()
    {
        $input = $this->request->getPost();
        $patientModel = new PatientModel();

        $email = $input['email'];
        $password = $input['password'];

        if (empty($email) || empty($password)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Please enter email and password!',
                'data' => [],
            ]);
        }

        $patient = $patientModel->where('email', $email)->first();
        if (empty($patient)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid credentials!',
                'data' => [],
            ]);
        }

        $isPasswordMatched = password_verify($password, $patient['hashed_password']);
        if (!$isPasswordMatched) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid credentials!',
                'data' => [],
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Logged in successfully!',
            'data' => [
                'id' => $patient['id'],
                'email' => $patient['email'],
            ],
        ]);
    }

    public function addMember($id = '')
    {

        $input = $this->request->getJSON();
        $patientModel = new PatientModel();

        $isPatientExits = $patientModel->isPatientExits($id);

        if (!$isPatientExits) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Patient not found!',
                'data' => [],
            ]);
        }

        $data = [
            'member_id' => $input->memberId,
            'first_name' => $input->first_name,
            'last_name' => $input->last_name,
            'email' => $input->email,
            'mobile' => $input->mobile,
            'gender' => $input->gender,
            'hashed_password' => password_hash($input->password, PASSWORD_BCRYPT),
        ];
        $dob = $input->date_of_birth;
        [$d, $m, $y] = explode('/', $dob);
        $data['date_of_birth'] = $y . '-' . $m . '-' . $d;

        $return['status'] = (bool) $patientModel->insert($data);
        $return['msg'] = $return['status'] ? 'Patient added successfully!' : 'Failed to add patient!';
        $return['data'] = $return['status'] ? [
            'id' => $patientModel->getInsertID(),
        ] : [];

        return $this->response->setJSON($return);
    }

    public function getAccounts($id)
    {
        $patientModel = new PatientModel();
        $patients = $patientModel
            ->where('id', $id)
            ->orWhere('member_id', $id)
            ->findAll();

        if (empty($patients)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Patients not found!',
                'data' => [],
            ]);
        }

        foreach ($patients as $key => $patient) {
            $acc[$key]['patient_name'] = $patient['first_name'] . ' ' . $patient['last_name'];
            $acc[$key]['gender'] = $patient['gender'];
            $acc[$key]['age'] = (new DateTime($patient['date_of_birth']))->diff(new DateTime())->y;
        }
        return $this->response->setJSON([
            'status' => true,
            'data' => $acc,
        ]);
    }

    public function addDetails($id)
    {
        $input = $this->request->getJSON();
        $patientModel = new PatientModel();

        $isPatientExits = $patientModel->isPatientExits($id);

        if (!$isPatientExits) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Patient not found!',
                'data' => [],
            ]);
        }

        $data = [
            'first_name' => $input->first_name,
            'last_name' => $input->last_name,
            'email' => $input->email,
            'mobile' => $input->mobile,
            'gender' => $input->gender,
            'address' => $input->address,
            'city' => $input->city,
            'state' => $input->state,
            'pincode' => $input->pincode,
            'landmark' => $input->landmark,
        ];
        $dob = $input->date_of_birth;
        [$d, $m, $y] = explode('/', $dob);
        $data['date_of_birth'] = $y . '-' . $m . '-' . $d;

        if (!empty($input->insuranceFile)) {
            $file = $this->request->getFile('insuranceFile');
            if (!$file->hasMoved()) {
                $newName = 'insurance_file_' . time() . $file->getExtension();
                $moved = $file->move('uploads/patient/insurance', $newName);
                if (!$moved) {
                    return $this->response->setJSON([
                        'status' => false,
                        'msg' => 'Insurance file upload failed!',
                        'data' => [],
                    ]);
                }
                $data['insurance_file'] = $newName;
            }
        }

        $return['status'] = $patientModel->update($id, $data, false);
        $return['msg'] = $return['status'] ? "Updated Successfully!" : "Internal Server Error!";
        $return['data'] = $return['status'] ? [
            'id' => $id,
        ] : [];

        $this->response->setJSON($return);
    }

    public function addAppointment($id = '')
    {
        $input = $this->request->getJSON(true);
        $appointmentModel = new AppointmentsModel();

        $data = [
            "department" => $input["department"],
            "symptoms" => $input["symptoms"],
            "booking_time" => $input["booking_time"],
            "patient_id" => $input["patient_id"],
            "clinic_name" => $input["clinic_name"],
            "clinic_address" => $input["clinic_address"],
            "fees" => $input["fees"],
            "payment_mode" => $input["payment_mode"],
        ];
        [$d, $m, $y] = explode('/', $input["booking_date"]);
        $data['booking_date'] = "{$y}-{$m}-{$d}";

        if ((int) $id !== (int) $input['patient_id']) {
            $data['member_id'] = $id;
        }

        $return['status'] = $appointmentModel->insert($data);
        $return['msg'] = $return['status'] ? "Appointment add Successfully!" : "Internal Server Error!";
        $return['data'] = [
            'id' => $appointmentModel->getInsertID(),
        ];

        return $this->response->setJSON($return);
    }

    public function getAppointment($id = '')
    {
        $appointData = $this->db->table('appointments a')
            ->select("a.id, a.booking_date, a.booking_time time, a.symptoms, a.department, a.clinic_name, a.clinic_address, d.name doctor_name, d.education doctor_degree, ex.expertise_name doctor_specialization, d.experience_year doctor_experience, dr.total_rating ,dr.total_review doctor_total_review")
            ->where("a.patient_id", $id)
            ->where('a.status', "active")
            ->join('doctors d', "a.doctor_id=d.id", "left")
            ->join("expertise ex", "d.expertise_id=ex.id", "left")
            ->join("doctor_rating dr", "d.id=dr.doctor_id", "left")
            ->get()->getResultArray();

        foreach ($appointData as &$app) {
            $rating = $app['total_rating'] / $app['doctor_total_review'];
            unset($app['total_rating']);
            $app['doctor_average_review'] = round($rating, 2);

            [$y, $m, $d] = explode('-', $app['booking_date']);
            $app['date'] = "{$d}/{$m}/{$y}";
            unset($app['booking_date']);
        }

        return $this->response->setJSON($appointData);
    }

    public function cancelAppointment($id = '')
    {
        $input = $this->request->getJSON(true);
        $appointmentModel = new AppointmentsModel();

        $appointment = $appointmentModel->find($id);

        if (!empty($appointment)) {
            if (!empty($input['reason'])) {
                $data['remarks'] = $input['reason'];
            }
            $data['status'] = "canceled";

            $this->db->transStart();
            try {
                $return['status'] = $appointmentModel->update($id, $data);
                $return['status'] = $appointmentModel->delete($id);
                $this->db->transComplete();
                $return['msg'] = "Appointment canceled Successfully!";
                $return['data'] = [
                    'id' => $appointment['id'],
                    "canceled_at" => date("Y-m-d H:i:s"),
                ];
                return $this->response->setJSON($return);
            } catch (\Exception $e) {
                $this->db->transRollback();
                return $this->response->setJSON([
                    'status' => false,
                    "msg" => $e->getMessage(),
                    'data' => [],
                ]);
            }
        } else {
            return $this->response->setJSON([
                'status' => false,
                "msg" => "Invalid appointment data",
                'data' => [],
            ]);
        }
    }
}
