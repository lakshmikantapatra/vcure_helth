<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\TempOtpModel;

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
        if (!$isEmailExists) {
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
        $dob = $input->dob;
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
}
