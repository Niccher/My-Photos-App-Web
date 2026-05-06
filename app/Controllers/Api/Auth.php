<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\User;
use App\Models\PhotoModel;

class Auth extends BaseController
{
    /**
     * Authenticate user and return access token.
     *
     * @return ResponseInterface
     */
    public function login()
    {
        try {
            log_message('debug', 'API Login attempt for: ' . $this->request->getPost('email'));
            $rules = [
                'email'       => 'required|valid_email',
                'password'    => 'required',
                'device_name' => 'required',
            ];

            if (! $this->validate($rules)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $this->validator->getErrors(),
                ])->setStatusCode(400);
            }

            $credentials = [
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
            ];

            // Authenticate (use session authenticator for email/password check)
            $authenticator = auth('session')->getAuthenticator();
            $result = $authenticator->check($credentials);

            if (! $result->isOK()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $result->reason(),
                ])->setStatusCode(401);
            }

            $user = $result->extraInfo();
            $token = $user->generateAccessToken($this->request->getPost('device_name'));

            $photoModel = new PhotoModel();
            $lastPhoto = $photoModel->where('user_id', $user->id)
                                    ->orderBy('created_at', 'DESC')
                                    ->first();

            return $this->response->setJSON([
                'status'       => 'success',
                'access_token' => $token->raw_token,
                'user'         => [
                    'id'          => $user->id,
                    'email'       => $user->email,
                    'username'    => $user->username,
                    'created_at'  => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                    'last_upload' => $lastPhoto ? ($lastPhoto['created_at'] ?? null) : null,
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[API Login Error] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Server Error: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}
