<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
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
        $data['expertise_name'] = $input->expertise_field;

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
}
