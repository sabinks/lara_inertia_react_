<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\BookAppointment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Mail\BookAppointmentStatusChangeMail;

class BookAppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        // $this->middleware('role:Superadmin', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
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
        $query = BookAppointment::query();
        if ($search) {
            $query->where(function ($query) use ($search) {
                return $query->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('dob', 'like', "%{$search}%")
                    ->orWhere('booking_date_time', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }
        $query = $query->orderBy($order_by, $order);
        $data =  $query->paginate($pagination);
        return $data;
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
        $appointment = BookAppointment::find($id);
        if (!$appointment) {
            return response()->json([
                'message' => 'Book Appointment Not Found!',
            ], 404);
        }
        return $appointment;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = BookAppointment::find($id);
        if (!$appointment) {
            return response()->json([
                'message' => 'Book Appointment Not Found!',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|lowercase|email',
            'phone' => 'required',
            'dob' => 'required',
            'booking_date_time' => 'required',
            'description' => 'required|max:255',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }
        $input = $request->only(['name', 'email', 'phone', 'dob', 'booking_date_time', 'description', 'status']);
        foreach ($input as $key => $value) {
            $appointment[$key] = $value;
        }
        $appointment->update();

        return response()->json([
            'message' => 'Appointment updated!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = BookAppointment::find($id);
        if (!$appointment) {
            return response()->json([
                'message' => 'Book Appointment Not Found!',
            ], 404);
        }
        $appointment->delete();

        return response()->json([
            'message' => 'Book appointment status deleted!',
        ], 200);
    }
    public function statusChange(string $id, Request $request)
    {
        $appointment = BookAppointment::find($id);
        if (!$appointment) {
            return response()->json([
                'message' => 'Book Appointment Not Found!',
            ], 404);
        }
        $appointment->status = $request->status;
        $appointment->update();
        if (in_array($appointment->status, ['Confirmed', 'Cancelled'])) {
            Mail::to($appointment->email)->later(
                now(),
                new BookAppointmentStatusChangeMail($appointment)
            );
        }

        return response()->json([
            'message' => 'Book appointment status updated!',
        ], 200);
    }
}
