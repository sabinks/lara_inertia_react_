<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\PayToCompany;
use App\Models\PayToPartner;
use Illuminate\Http\Request;
use App\Models\PartnerClient;
use App\Models\ClientReferral;
use App\Http\Controllers\Controller;
use App\Jobs\UserActiveStatusChange;
use App\Models\AssignedPartnerClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(['role:Super Admin|Admin|Partner'], ['only' => ['userActiveStatusChange', 'userActiveStatusChange']]);
        $this->middleware(['role:Super Admin|Admin|Partner'], ['only' => 'userProfile']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function userActivate($id)
    {
        $user = User::whereId($id)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }
        $user->is_active = true;
        $user->update();
        return response()->json([
            'message' => 'User activated!',
        ], 200);
    }
    public function userDeactivate($id)
    {
        $user = User::whereId($id)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }
        $user->is_active = false;
        $user->update();
        return response()->json([
            'message' => 'User deactivated!',
        ], 200);
    }
    public function user_password_change(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }
        $user = User::whereId($id)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }
        $password = $request->input('password');
        $user->password = Hash::make($password);
        $user->update();
        return response()->json([
            'message' => 'User password changed!',
        ], 200);
    }
    public function setUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile_image' => 'image|max:3072|mimes:jpg,jpeg,png,bmp',
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile_image' => 'image|max:3072|mimes:jpg,jpeg,png,bmp',
            // 'data' => ''
        ]);
        // $data = json_decode($request->data, true);
        // $dataValidator = Validator::make($data, [
        //     'mobile' => '',
        // ]);
        // if ($dataValidator->fails() || $validator->fails()) {
        //     $errors = $validator->messages()->merge($dataValidator->messages());
        //     return response($errors->messages(), 422);
        // }
        $user = User::find(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }
        $user->name = $request->input('name');
        // $user->data = $request->input('data') ?: '';
        DB::beginTransaction();
        try {
            $destination_path = '/public/profile_image/';
            $file = $request->file('profile_image');
            if ($request->hasFile('profile_image')) {
                $document_name = time() . rand(10000, 99999) .  "." . $file->getClientOriginalExtension();
                $result = $file->storeAs($destination_path, $document_name);
                if ($result) {
                    Storage::delete($destination_path . $user->profile_image);
                    $user->profile_image = $document_name;
                }
            }
            $user->update();
            DB::commit();

            return response()->json([
                'message' => 'User Updated!',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
