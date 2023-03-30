<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel as Users;
use \Firebase\JWT\JWT;

class AuthController extends BaseController
{
    use ResponseTrait;
    public function login(){
        $userModel = new Users();
  
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
          
        $user = $userModel->where('user_email', $email)->first();
  
        if(is_null($user)) {
            return $this->respond(['error' => 'Invalid username or password.'], 200);
        }
  
        $pwd_verify = password_verify($password, $user['user_password']);
  
        if(!$pwd_verify) {
            return $this->respond(['error' => 'Invalid username or password.'], 200);
        }
 
        $key = getenv('JWT_SECRET');
        $iat = time(); // current timestamp value
        $exp = $iat + 3600;
 
        $payload = array(
            "iss" => "http://mtcticket.loc",
            "aud" => "Mobile Apps",
            "sub" => "Auth Tokens",
            "iat" => $iat, //Time the JWT issued at
            "exp" => $exp, // Expiration time of token
            "email" => $user['user_email'],
        );
         
        $token = JWT::encode($payload, $key, 'HS256');
 
        $response = [
            'status' => "200",
            'message' => 'Login Succesful',
            'token' => $token
        ];
         
        return $this->respond($response, 200);
    }

    public function forgot_password(){

        $userModel = new Users();
  
        $email = $this->request->getVar('email');
        $user = $userModel->where('email', $email)->first();
  
        if(is_null($user)) {
            $response = [
                'status'=> 'failed',
                'error' => 'Invalid or No registered email'
            ];
            return $this->respond($response, 200);
        }

        $otp = rand(100000,999999);
        $forgot_password_token = sha1($otp);

        $userModel->set('user_fp_token', $forgot_password_token);
        $userModel->where('user_id', $user['id']);
        $userModel->update();

        $this->otp_email($user['email'], $otp);

        $response = [
            'status' => 'success',
            'message' => "Password reset otp sent to Email",
            'email' => $user['email']
        ];

        return $this->respond($response, 200);

    }

    public function verify_otp(){

        $email = $this->request->getVar('email');
        $otp = $this->request->getVar('otp');

        $userModel = new Users();
        $user = $userModel->where('email', $email)->first();
  
        if(is_null($user)) {
            $response = [
                'status' => "failed",
                'error' => "Something went wrong please try again",
                'verified' => false,
            ];
            return $this->respond($response, 200);
        }

        if(sha1($otp) === $user['fp_token']){
            $response = [
                'status' => "success",
                'message' => "OTP verified successfully",
                'verified' => true,
                'auth_token' => $user['user_fp_token']
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => "failed",
                'error' => "OTP verification failed",
                'verified' => false,
            ];
            return $this->respond($response, 200);
        }
    }

    public function reset_password(){

        $rules = [
            'authtoken' => ['rules' => 'required'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'confirm_password'  => [ 'label' => 'confirm password', 'rules' => 'required|matches[password]']
        ];

        if ($this->validate($rules)) {
            $userModel = new Users();
            $authtoken = $this->request->getVar('authtoken');
            $hashed_password = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
            $user = $userModel->where('user_fp_token', $authtoken)->first();
            if (!is_null($user)) {
                $data = [
                    'user_fp_token' => null,
                    'user_password' => $hashed_password
                ];

                $userModel->set($data);
                $userModel->update();
                $response = [
                    'status' => "success",
                    'message' => "Password reset successfully",
                ];
                return $this->respond($response, 200);
            } else {
                $response = [
                    'status' => "failed",
                    'error' => "Something went wrong please try again",
                ];
                return $this->respond($response, 200);
            }
            
        } else {
            $response = [
                'status' => "200",
                'errors' => [
                    "errPassword" => $this->validator->getError('password'),
                    "errConfirmPassword" => $this->validator->getError('confirm_password'),
                ],
            ];
            return $this->respond($response, 200);
        }

    }

    public function register(){
        $rules = [
            'name' => ['rules' => 'required|min_length[4]|max_length[255]'],
            'email' => ['rules' => 'required|min_length[4]|max_length[255]|valid_email|is_unique[users.user_email]'],
            'password' => ['rules' => 'required|min_length[8]|max_length[255]'],
            'confirm_password'  => [ 'label' => 'confirm password', 'rules' => 'required|matches[password]']
        ];
            
  
        if($this->validate($rules)){
            $model = new Users();
            $data = [
                'user_name'     => $this->request->getVar('name'),
                'user_email'    => $this->request->getVar('email'),
                'user_password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'user_deleted'  => 0
            ];
            $model->save($data);
             
            return $this->respond([
                'status' => 'success',
                'message' => 'Registered Successfully'
            ], 200);
        }else{
            $response = [
                'status' => 'failed',
                'messages' => [
                    "errName" => $this->validator->getError('name'),
                    "errEmail" => $this->validator->getError('email'),
                    "errPassword" => $this->validator->getError('password'),
                    "errConfirmPassword" => $this->validator->getError('confirm_password'),
                ],
            ];
            return $this->respond($response, 200);
             
        }
            
    }

    private function otp_email($toEmail, $otp){
        $email = \Config\Services::email();

        $email->setFrom('test@angular-crud.loc', 'Angular Crud');
        $email->setTo($toEmail);

        $email->setSubject('Email OTP to reset password');
        $message = "Your OTP to reset password is: ". $otp;
        $email->setMessage($message);

        try {
            $email->send();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        
    }
}
