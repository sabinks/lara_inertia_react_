<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\ClientNoteController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\User\RegisterController;
use App\Http\Controllers\BookAppointmentController;
use App\Http\Controllers\Next\FormSendMailController;
use App\Http\Controllers\User\VerificationController;
use App\Http\Controllers\Next\BookAppointmentController as NextBookAppointmentController;

// Route::post('/client-register', [RegisterController::class, 'clientRegister']);
// Route::post('/partner-register', [RegisterController::class, 'partnerRegister']);
// Route::post('/generate-access-token', [AuthController::class, 'refreshToken']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{verification_token}', [VerificationController::class, 'email_verification']);
Route::post('next/book-appointment', [NextBookAppointmentController::class, 'store']);
Route::get('next/book-appointment-slot-available', [NextBookAppointmentController::class, 'bookAppointmentSlotAvailable']);
Route::get('next/check-appointment-availablity', [NextBookAppointmentController::class, 'checkAppointmentAvailability']);
Route::post('next/contact-form-send-mail', [FormSendMailController::class, 'contactFormSendMail']);


Route::group([], function () {
    Route::post('get-user', [AuthController::class, 'getUser'])->middleware(['auth:api']);
    Route::post('set-user', [UserController::class, 'setUser'])->middleware(['auth:api']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:api']);
    Route::post('/user-detail', [AuthController::class, 'userDetail'])->middleware('auth:api');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware(['auth:api']);
    Route::post('get-role-permissions', [AuthController::class, 'getRolePermissions'])->middleware(['auth:api']);
    Route::post('/user-activate/{id}', [UserController::class, 'userActivate'])->middleware(['auth:api']);
    Route::post('/user-deactivate/{id}', [UserController::class, 'userDeactivate']);
    Route::post('/user-status-change', [UserController::class, 'userActiveStatusChange']);
    Route::post('user-password-change/{id}', [UserController::class, 'user_password_change'])->middleware('auth:api');

    Route::resource('book-appointment', BookAppointmentController::class);
    Route::post('book-appointment/{id}/status', [BookAppointmentController::class, 'statusChange']);

    Route::resource('newsletter', NewsletterController::class);
    Route::post('send-newsletter', [NewsletterController::class, 'sendNewsletter']);
    Route::get('client-list', [NewsletterController::class, 'clientList']);
    Route::resource('clients', ClientController::class);
    Route::resource('clients/{client_id}/notes', ClientNoteController::class);
    Route::post('/user-status-change/{id}', [UserController::class, 'userActiveStatusChange']);
    Route::get('user-profile/{id}', [UserController::class, 'userProfile']);    //general

});
