<?php

namespace App\Http\Controllers\Next;

use Illuminate\Http\Request;
use App\Models\BookAppointment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BookAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|lowercase|email',
            'phone' => 'required',
            'dob' => 'required',
            'booking_date_time' => 'required',
            'description' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }
        $input = $request->only(['name', 'email', 'phone', 'dob', 'booking_date_time', 'description']);
        $BookAppointment = BookAppointment::create($input);

        return response()->json([
            'message' => 'Appointment saved, we will send mail shortly!',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
