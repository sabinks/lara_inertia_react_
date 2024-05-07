<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ClientNote;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class ClientNoteController extends Controller
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
        $query = ClientNote::query();

        if ($search) {
            $query->where(function ($query) use ($search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $query = $query->orderBy($order_by, $order);
        $data =  $query->paginate($pagination);

        return response()->json([
            'data' => $data,
            'domain' => Config::get('app.url')
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $client_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'note' => 'required'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $input = $request->only(['name', 'note']);
        $input['user_id'] = $client_id;
        DB::beginTransaction();
        try {
            $client = ClientNote::create($input);
            DB::commit();
            return response()->json([
                'message' => 'Client Note Created!',
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
    public function show(string $client_id, string $id)
    {
        $clientNote = ClientNote::whereId($id)->first();
        if (!$clientNote) {
            return response()->json([
                'message' => 'Client note not found!',
            ],);
        }

        return $clientNote;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $client_id, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'note' => 'required'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $clientNote = ClientNote::find($id);
        if (!$clientNote) {
            return response()->json([
                'message' => 'No record found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $clientNote->name = $request->input('name');
            $clientNote->note = $request->input('note');
            $clientNote->update();
            DB::commit();

            return response()->json([
                'message' => 'Client note updated!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $client_id, string $id)
    {
        $clientNote = ClientNote::find($id);
        if (!$clientNote) {
            return response()->json([
                'message' => 'Client note not found!',
            ], 404);
        }
        $result = $clientNote->delete();

        if ($result) {
            return response()->json([
                'message' => 'Client note deleted!',
            ], 200);
        }
    }
}
