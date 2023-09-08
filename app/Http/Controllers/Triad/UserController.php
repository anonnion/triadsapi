<?php

namespace App\Http\Controllers\Triad;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        try {
            $user = User::whereId(auth()->id())->with('profile')->first();
            $user = new UserResource($user);
            return response([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getUser($id)
    {
        try {
            $user = User::whereId($id)->with('profile')->first();
            $user = new UserResource($user);
            return response([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateProfile(Request $request)
    {
        DB::beginTransaction();
        $username = '';
        try {
            if($request->username && strstr($request->username, 'playerId:')){
                $ch = curl_init();
                $playerId = explode(':', $request->username)[1];
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
            return response([
                'message' => $th
            ], 500);
        }
        try {
            $uData = [
                'username' => $username != '' ? $playerId.PHP_EOL.$username : $request->username
            ];
            if ($request->hasFile('profile_photo')) {
                $request->validate([
                    'profile_photo' => 'mimes:jpeg,png,jpg'
                ]);
                //$file = $request->file('profile_photo');
                $imagePath = 'public/images/profile_photo';
                $image = $request->file('profile_photo');
                $image_name = $image->getClientOriginalName();
                $path = $request->file('profile_photo')->storeAs($imagePath, rand(0, 50) . $image_name);
                $uData['profile_photo'] = $path;
            }
            $user = User::whereId(auth()->id())->update($uData);
            if ($user) {
                $data = [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'website' => $request->website,
                    'bio' => $request->bio,
                ];
                Profile::whereUserId(auth()->id())->update($data);
                DB::commit();
                return response([
                    'message' => 'success'
                ], 201);
            } else {
                return response([
                    'message' => 'Error'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }
}
