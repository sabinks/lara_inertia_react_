<?php

namespace App\Http\Controllers\Next;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ContactFormSendMailJob;
use Illuminate\Support\Facades\Validator;

class FormSendMailController extends Controller
{
    public function contactFormSendMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|lowercase',
            'phone' => 'required',
            'subject' => 'required',
            'message' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        ContactFormSendMailJob::dispatch($request->only('name', 'email', 'phone', 'subject', 'message'));

        return response()->json([
            'message' => 'Mail sent successfully, we will reach out to you soon!'
        ], 200);
    }
}
