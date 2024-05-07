<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $order_by = $request->has('order_by') ? $request->input('order_by') : 'created_at';
        $order = $request->has('order') ?  $request->input('order') : 'desc';
        $pagination = $request->has('pagination') ? $request->input('pagination') : 10;
        $client_list_ids = [];
        $query = User::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                return $query->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $query = $this->withModel($query);
        $query = $query->orderBy($order_by, $order);
        $data =  $query->paginate($pagination);

        return response()->json([
            'data' => $data,
            'document_storage_path' => "/storage/client/profile_image/",
            'domain' => Config::get('app.url')
        ], 200);
    }

    public function withModel($query)
    {
        return $query->whereHas('roles', function ($query) {
            return $query->whereIn('name', ['Client']);
        })->with([

            // 'user_documents',

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|lowercase|unique:users|regex:/^\S*$/u',
            'phone' => '',
            'profile_image' => 'image|max:3072|mimes:jpg,jpeg,png,bmp',
        ], [
            'email.regex' => 'Email should not contain empty space.',
        ]);
        $data = json_decode($request->data, true);
        $dataValidator = Validator::make($data, [], []);
        if ($dataValidator->fails() || $validator->fails()) {
            $errors = $validator->messages()->merge($dataValidator->messages());

            return response($errors->messages(), 422);
        }

        $input = $request->only(['name', 'email']);
        $random_password = Str::random(8);
        $input['password'] = Hash::make($random_password);
        $input['email_verified_at'] = Carbon::now();
        $input['is_active'] = true;
        $input['verification_token'] =  '';
        $input['data'] = $request->data;
        DB::beginTransaction();
        try {
            $destination_path = '/public/profile_image/';
            $file = $request->file('profile_image');
            if ($request->hasFile('profile_image')) {
                $document_name = time() . rand(10000, 99999) .  "." . $file->getClientOriginalExtension();
                $result = $file->storeAs($destination_path, $document_name);
                if ($result) {
                    $input['profile_image'] = $document_name;
                }
            }
            $client = User::create($input);
            $result = $client->assignRole('Client');

            DB::commit();
            return response()->json([
                'message' => 'Client Created!',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = User::whereId($id)->with([])->first();
        return response()->json([
            'data' => $client,
            'image_path' =>  Config::get('app.url') . "/storage/profile_image/",
        ], 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'is_active' => '',
            'profile_image' => 'image|max:3072|mimes:jpg,jpeg,png,bmp',
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $client = User::find($id);
        if (!$client) {
            return response()->json([
                'message' => 'No record found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $client->name = $request->input('name');
            $client->phone = $request->input('phone') ?: '';
            $client->dob = $request->input('dob') ?: '';
            $client->is_active = Helper::booleanCheck($request->input('is_active'));
            if ($request->hasFile('profile_image')) {
                $destination_path = '/public/profile_image/';
                $file = $request->file('profile_image');
                $document_name = time() . rand(10000, 99999) .  "." . $file->getClientOriginalExtension();
                $result = $file->storeAs($destination_path, $document_name);

                if ($result) {
                    Storage::delete($destination_path . $client->profile_image);
                    $client->profile_image = $document_name;
                }
            }
            $client->update();
            DB::commit();

            return response()->json([
                'message' => 'Client information updated!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }

    /**
`     * Remove the specified resource from storage.
`     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'Client not found!',
            ], 404);
        }
        $result = $user->delete();
        DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();
        if ($result) {
            return response()->json([
                'message' => 'Client deleted!',
            ], 200);
        }
    }
    public function getProfile()
    {
        $data = [];
        $data['user'] = User::whereId(Auth::id())->with([''])
            ->first(['name', 'email', 'data', 'profile_image', 'is_active', 'profile_image']);
        $data['path'] = Config::get('app.url')  . "/storage/profile_image/";

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile_image' => 'image|max:3072|mimes:jpg,jpeg,png,bmp',
        ], []);
        $data = json_decode($request->data, true);
        $dataValidator = Validator::make($data, [
            'mobile' => 'required'
        ], [
            'mobile.required' => 'Mobile number is required.',
        ]);
        if ($dataValidator->fails() || $validator->fails()) {
            $errors = $validator->messages()->merge($dataValidator->messages());

            return response($errors->messages(), 422);
        }

        $user = User::find(Auth::id());
        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
            ], 404);
        }
        $user->name = $request->name;
        $user->data = $request->data;
        DB::beginTransaction();
        try {
            $destination_path = '/public/profile_image/';
            $file = $request->file('profile_image');
            if ($request->hasFile('profile_image')) {
                $document_name = time() . rand(10000, 99999) .  "." . $file->getClientOriginalExtension();
                $result = $file->storeAs($destination_path, $document_name);
                if ($result) {
                    $user->profile_image = $document_name;
                }
            }

            $user->update();
            DB::commit();

            return response()->json([
                'message' => 'Client Created!',
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
    public function clientList()
    {
        $query = User::query();
        $query = $query->whereHas('roles', function ($query) {
            return $query->whereIn('name', ['Client']);
        });

        return $query->where('is_active', true)
            ->get(['id', 'id as value', 'name', 'name as label', 'email']);
    }
}
