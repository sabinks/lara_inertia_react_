<?php

namespace App\Http\Controllers\Next;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\BookAppointment;
use App\Jobs\BookAppointmentJob;
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
            'booking_date' => 'required',
            'booking_time' => 'required',
            'description' => 'required|max:500'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }
        $input = $request->only(['name', 'email', 'phone', 'dob', 'description']);
        $input['booking_date_time'] = $request->booking_date . " " . $request->booking_time;
        $BookAppointment = BookAppointment::create($input);

        // BookAppointmentJob::dispatch($BookAppointment);

        return response()->json([
            'message' => 'Thank you for booking appointment with us, we will contact you soon!',
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
    public function checkAppointmentAvailability()
    {
        $bookAppointment = BookAppointment::where('booking_date_time', '>=', Carbon::now()->toDateTimeString())->get()->pluck(['booking_date_time']);
        return $bookAppointment;
    }
}
