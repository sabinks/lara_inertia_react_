<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ClientReferral;
use App\Models\UserPartnerType;
use App\Jobs\UserVerificationJob;
use App\Models\ClientReferralCode;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\Referral\AccountReferrerClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function clientRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|lowercase|unique:users|regex:/^\S*$/u',
            'password' => 'required|confirmed|min:8|regex:/^\S*$/u',
            'referral_code' => '',
        ], [
            'email.regex' => 'Email should not contain empty space.',
        ]);

        $data = json_decode($request->data, true);
        $dataValidator = Validator::make($data, [
            'mobile' => 'required|numeric'
        ], [
            'mobile.required' => 'Mobile number is required.',
            'mobile.numeric' => 'Mobile number should be numeric.',
        ]);
        if ($dataValidator->fails() || $validator->fails()) {
            $errors = $validator->messages()->merge($dataValidator->messages());

            return response($errors->messages(), 422);
        }
        $input = $request->only(['name', 'email', 'password']);
        $input['password'] = Hash::make($input['password']);
        $input['data'] = $request->data;
        $input['verification_token'] =  Str::random(60);
        $input['is_active'] = false;
        $input['self_signup'] = true;
        DB::beginTransaction();
        try {
            $client = User::create($input);
            // assign role
            $client->assignRole('Client');
            if ($request->input('referral_code')) {
                //if referral code on request check on db
                $client_referral_code = ClientReferralCode::whereReferralCode($request->input('referral_code'))->first();
                if (!$client_referral_code) {
                    return response()->json([
                        'message' => 'Referral code do not match!',
                    ], 404);
                }
                //if referral code match then create new referral code for client
                $code = ClientReferralCode::create([
                    'client_id' => $client->id,
                    'referral_code' => Str::random(10)
                ]);
                //reward comission to referrer client
                $result = ClientReferral::create([
                    'referrer_id' => $client_referral_code->client_id,
                    'user_id' => $client->id,
                    'referral_code_id' => $code->id,
                    'comission_amount' => 0,
                ]);
                if ($result) {
                    // send mail to referrer for account created
                    // return $client_referral_code->referrer;
                    AccountReferrerClient::dispatch($client_referral_code->referrer, $client);
                    // ReferralClientJob::dispatch($client_referral_code->referrer, $client);
                }
            } else {
                // if no referral code then create new for this client
                ClientReferralCode::create([
                    'client_id' => $client->id,
                    'referral_code' => Str::random(10)
                ]);
                // if ($this->roleCheck('Partner')) {
                //     PayToPartner::create([
                //         'partner_id' => Auth::id(),
                //         'client_id' =>  $client->id
                //     ]);
                // }
            }
            // client verification mail
            UserVerificationJob::dispatch($client, '', 'client');
            // UserSelfSignupJob::dispatch($client, 'Client');
            DB::commit();

            return response()->json([
                'message' => 'User created, check email for user verification!',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
    public function partnerRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|lowercase|unique:users|regex:/^\S*$/u',
            'password' => 'required|confirmed|min:8|regex:/^\S*$/u',
            'partner_type_id' => 'required|numeric',
        ], [
            'email.regex' => 'Email should not contain empty space.',
            'partner_type_id' => 'Partner Type is required.'
        ]);
        $data = json_decode($request->data, true);
        $dataValidator = Validator::make($data, [
            'mobile' => 'required|numeric'
        ], [
            'mobile.required' => 'Mobile number is required.',
            'mobile.numeric' => 'Mobile number should be numeric.',
        ]);

        if ($dataValidator->fails() || $validator->fails()) {
            $errors = $validator->messages()->merge($dataValidator->messages());

            return response($errors->messages(), 422);
        }
        $input = $request->only(['name', 'email', 'password']);
        $input['data'] = $request->data;
        $input['password'] = Hash::make($input['password']);
        $input['verification_token'] =  Str::random(60);
        $input['is_active'] = false;
        $data['self_signup'] = true;
        DB::beginTransaction();
        try {
            $user = User::create($input);
            $user->assignRole('Partner');
            UserPartnerType::create([
                'partner_id' => $user->id,
                'partner_type_id' => $request->partner_type_id
            ]);
            UserVerificationJob::dispatch($user, '', 'partner');
            // UserSelfSignupJob::dispatch($user, 'Partner');
            DB::commit();

            return response()->json([
                'message' => 'User created, check email for user verification!',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
