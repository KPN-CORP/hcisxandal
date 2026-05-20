<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    public function handleCallback(Request $request)
    {
        if (!$request->has('data')) {
            return redirect('/login')->with('error', 'Parameter data tidak ditemukan.');
        }

        try {
            $encryptedData = urldecode($request->input('data'));
            $encryptedData = str_replace(' ', '+', $encryptedData);
            
            $decodedData = base64_decode($encryptedData);
            
            $key = env('DARWINBOX_XOR_KEY');
            $decryptedDataxor = $this->xorDecrypt($decodedData, $key);
            
            $decryptedData = base64_decode($decryptedDataxor);
            $json = json_decode($decryptedData, true);

            if (!$json || !isset($json['email']) || !isset($json['token'])) {
                return redirect('/login')->with('error', 'Format data SSO tidak valid.');
            }

            $email = $json['email'];
            $token = $json['token'];
            $firstName = $json['firstname'] ?? strstr($email, '@', true);
            $employeeId = $json['employee_no'] ?? null; 
            // dd($json);
            $response = Http::timeout(15)->withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => env('DARWINBOX_AUTH_HEADER'),
            ])->post('https://kpncorporation.darwinbox.com/checkToken', [
                'api_key' => env('DARWINBOX_API_KEY'),
                'token'   => $token,
            ]);

            session(['sso_token' => $token]);

            if ($response->successful() && isset($response['status']) && $response['status'] == 1) {
                
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $firstName,
                        'employee_id' => $employeeId,
                        'password' => bcrypt(Str::random(16)), 
                        'email_verified_at' => now(),
                    ]
                );
                
                $user->update([
                    'name' => $firstName,
                    'employee_id' => $employeeId
                ]);

                Auth::login($user);
                
                return redirect()->route('employees.list');

            } else {
                return redirect('/login')->with('error', 'Validasi Token Darwinbox Gagal.');
            }

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    private function xorDecrypt($data, $key)
    {
        $keyLength = strlen($key);
        $dataLength = strlen($data);
        $decrypted = '';

        for ($i = 0; $i < $dataLength; $i++) {
            $decrypted .= $data[$i] ^ $key[$i % $keyLength];
        }

        return $decrypted;
    }
}