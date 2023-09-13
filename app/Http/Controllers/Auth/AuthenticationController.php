<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Email;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AuthenticationController extends Controller
{
    public $bucket;
    public $client;
    public $authToken;

    public function __construct()
    {
        $this->client = Http::withBasicAuth('00582ea9563c5e60000000002', 'K005gLZt0Sqn4Z1mtT/LN8edvfcsBmI')
            ->get('https://api.backblazeb2.com/b2api/v3/b2_authorize_account');
        $this->authToken = $this->client['authorizationToken'];
        //dd($this->client['apiInfo']);
    }

    public function get_upload_url()
    {
        $req = Http::withHeaders(['Authorization' => $this->authToken])
            ->get('https://api005.backblazeb2.com/b2api/v2/b2_get_upload_url?bucketId=' . getenv('BUCKET_ID'));
        return [
            'authToken' => $req['authorizationToken'],
            'uploadUrl' => $req['uploadUrl'],
        ];
    }

    public function register(RegisterRequest $registerRequest)
    {
        $email = Email::where('email', '=', $registerRequest->email)->first();
        if($email->verify_timestamp != $registerRequest->token)
        {
            return response([
                'message' => "Invalid token"
            ], 404);
        }
        $username = '';
        try {
            if($registerRequest->username && strstr($registerRequest->username, 'playerId:')){
                $ch = curl_init();
                $playerId = explode(':', $registerRequest->username)[1];
                curl_setopt($ch, CURLOPT_URL, 'https://order-sg.codashop.com/initPayment.action');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "voucherPricePoint.id=308088&voucherPricePoint.price=450.0&voucherPricePoint.variablePrice=0&email&userVariablePrice=0&user.userId=$playerId&user.zoneId&msisdn&voucherTypeName=CALL_OF_DUTY_ACTIVISION&voucherTypeId=183&gvtId=231&shopLang=en_NG&affiliateTrackingId&impactClickId&anonymousId&fullUrl=https%3A%2F%2Fwww.codashop.com%2Fen-ng%2Fcall-of-duty-mobile&userEmailConsent=false&userMobileConsent=false&verifiedMsisdn&promoId&promoCode&clevertapId&promotionReferralCode");

                $headers = array();
                $headers[] = 'Accept: application/json';
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                $headers[] = 'X-Session-Country2name: NG';
                $headers[] = 'Host: order-sg.codashop.com';
                $headers[] = 'Origin: https://www.codashop.com';
                $headers[] = 'Referer: https://www.codashop.com/';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    return response([
                        'message' => curl_error($ch)
                    ], 500);
                }
                curl_close($ch);
                $username = json_decode($result)->confirmationFields->username;
            }
        } catch (\Throwable $th) {
            //throw $th;
            // return response([
            //     'message' => $th
            // ], 500);
            $username = $registerRequest->username;
        }
        DB::beginTransaction();
        try {
            // $uploadUrl = $this->get_upload_url();
            // $url = $uploadUrl['uploadUrl'];
            // $authT = $uploadUrl['authToken'];
            //dd($uploadUrl);
            // validate request
            //$registerRequest->validated();
            // data to insert to table
            $data = [
                'email' => $registerRequest->email,
                'username' => $username,
                'password' => Hash::make($registerRequest->password)
            ];
            // check if image is passed and add to data
            if ($registerRequest->hasFile('profile_photo')) {
                $registerRequest->validate([
                    'profile_photo' => 'mimes:jpeg,png,jpg'
                ]);
                //$file = $registerRequest->file('profile_photo');


                $imagePath = 'public/images/profile_photo';
                $image = $registerRequest->file('profile_photo');
                $image_name = $image->getClientOriginalName();
                $path = $registerRequest->file('profile_photo')->storeAs($imagePath, rand(0, 50) . $image_name);
                //$originalFileName = $file->getClientOriginalName();
                //$filePath = $file->getRealPath();

                // Calculate SHA1 checksum of the file's content
                //$sha1Checksum = sha1_file($filePath);

                // Sanitize and URL-encode the original filename
                //$encodedFileName = urlencode($originalFileName);
                //dd($encodedFileName);

                //$fileSize = $file->getSize();

                // Calculate the total Content-Length including checksum
                //$totalContentLength = $fileSize + 40;
                /*$send = Http::withHeaders([
                    'Authorization' => $authT,
                    'X-Bz-File-Name' => $encodedFileName,
                    'Content-Type' => $file->getMimeType(),
                    'Content-Length' => $totalContentLength,
                    'X-Bz-Content-Sha1' => 'do_not_verify',
                ])
                    ->post($url, [
                        'body' => $registerRequest->file('profile_photo')
                    ]);

                dd($send);
                die(); */

                $data['profile_photo'] = $path;

                //dd($data);
            }

            $user = User::create($data);
            $token = $user->createToken('triads')->plainTextToken;
            if ($user) {
                $names = str_getcsv($email->full_name, " ");
                $fn = array_shift($names);
                $ln = implode(' ', $names);
                Profile::create(['user_id' => $user->id, 'firstname' => $fn, 'lastname' => $ln]);
                DB::commit();
                return response([
                    'message' => 'success',
                    'user' => $user,
                    'token' => $token
                ], 201);
            } else {
                return response([
                    'message' => 'Error creating account'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $loginRequest)
    {
        try {
            $loginRequest->validated();
            // check user
            $user = User::whereUsername($loginRequest->username)->first();
            if (!$user || !Hash::check($loginRequest->password, $user->password)) {
                return response([
                    'message' => 'Invalid Credentials'
                ], 400);
            }
            $token = $user->createToken('triads')->plainTextToken;
            return response([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkUsername(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'token' => 'required|number',
            ]);
            $token = Email::where('verify_timestamp', '=', $request->token)->first();
            $username = User::whereUsername($request->username)->first();
            if ($username) {
                return response([
                    'message' => $request->username . ' ' . 'has been taken'
                ], 409);
            } else {
                return response([
                    'message' => $request->username . ' ' . ' is available'
                ], 200);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'full_name' => 'required',
            ]);
            $user = User::whereEmail($request->email)->first();
            if ($user) {
                return response([
                    'message' => $request->email . ' ' . 'has been taken'
                ], 409);
            } else {
                $token = random_int(111111,999999);
                Email::where('email', '=', $request->email)->delete();
                $email = new Email;
                $email->email = $request->email;
                $email->full_name = $request->full_name;
                $email->verify_token = $token;
                $email->expires_at = time() + (60*15); // Expires in 15 minutes
                $email->save();
                $this->token_email($request->full_name, $request->email, $token);
                return response([
                    'message' => $request->email . ' ' . ' is available, token has been sent to email address'
                ], 200);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function token_email($full_name, $email, $token) {
        $data = array('token'=>$token, 'full_name'=>$full_name);

        $stat = Mail::send(['html'=>'mail.verify'], $data, function($message) use ($email, $full_name) {
           $message->to($email, $email.' on Triads')->subject
              ("Verify Your email, $full_name");
           $message->from('noreply@triads.ng','Triads Entertainment');
        });
        return $stat;
     }

    public function verifyEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users',
                'token' => 'required|numeric',
            ]);
            $email = Email::where('email', '=', $request->email)->first();
            $user = User::whereEmail($request->email)->first();
            if ($user || !$email || $email->verify_token != $request->token) {
                return response([
                    'message' => 'Invalid token'
                ], 201);
            } elseif (time() > $email->expires_at) {

            } else {
                $email->verify_timestamp = time() + random_int(111111,999999);
                $email->save();
                return response([
                    'message' => $request->email . ' ' . ' is now verified',
                    'token' => $email->verify_timestamp,
                ], 200);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
