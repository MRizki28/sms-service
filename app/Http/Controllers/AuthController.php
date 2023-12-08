<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    public function createData(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'no_hp' => 'required|unique:users,no_hp',
                'password' => 'required|confirmed',
                'password_confirmation' => 'required'
            ]
           
        );
        if ($validation->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'check your valdiation',
                'errors' => $validation->errors()
            ]);
        }

        try {
            $data = new User;
            $data->name = $request->input('name');
            $data->no_hp = $request->input('no_hp');
            $data->password = Hash::make($request->input('password'));
            $data->save();

            $this->sendVerificationSms($data);
            return response()->json([
                'message' => 'success send sms',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'error',
                'errors' => $th->getMessage()
            ]);
        }
    }
    private function sendVerificationSms(User $user)
    {
        $twilioSid = getenv('TWILIO_SID');
        $twilioToken = getenv('TWILIO_AUTH_TOKEN');
        $twilioVerifySid = getenv('TWILIO_VERIFY_SID');
        $twilioFromNumber = getenv('TWILIO_FROM_NUMBER');
        $twilio = new Client($twilioSid, $twilioToken);
    
        $verificationCode = 'https://github.com/MRizki28'; 
    
        $message = $twilio->messages->create(
            $user->no_hp, 
            [
                'from' => $twilioFromNumber,
                'body' => "Succsess send : $verificationCode",
            ]
        );
    
        $user->nohp_verified_at = $verificationCode;
        $user->save();
    }
}
