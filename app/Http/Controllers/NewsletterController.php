<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\Newsletter;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use App\Jobs\SendNewsletterJob;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        // $this->middleware('role:Superadmin', ['only' => 'store']);
        // $this->middleware('role:Superadmin', ['only' => 'destroy']);
        // $this->middleware('role:Superadmin', ['only' => 'sendNewsletter']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $publish = Helper::booleanCheck($request->input('publish'));
        $pagination = $request->input('pagination') ? $request->input('pagination') : 10;
        $query = Newsletter::query();
        if ($publish) {
            $query->wherePublish(true);
        }
        // $user = User::find(Auth::id());
        // if ($this->roleCheck('Client')) {
        // $query->whereCreatedBy($user->id);
        // }
        $query->with([]);
        $newsletters = $query->orderBy('created_at', 'desc')->paginate($pagination);

        return $newsletters;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {

            return response($validator->errors(), 422);
        }
        $input = $request->only(['name', 'content']);
        $input['created_by'] = Auth::id();
        $input['created_at'] = Carbon::now();
        $input['updated_at'] = Carbon::now();
        DB::beginTransaction();
        try {
            $newsletter = Newsletter::create($input);
            DB::commit();

            return response()->json([
                'message' => 'Newsletter Created!',
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
        $newsletter = Newsletter::find($id);
        if (!$newsletter) {
            return response()->json([
                'message' => 'Newsletter not found!',
            ], 404);
        }

        return $newsletter;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Newsletter $newsletter)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {

            return response($validator->errors(), 422);
        }
        $newsletter->name = $request->input('name');
        $newsletter->content = $request->input('content');
        $newsletter->update();

        return response()->json([
            'message' => 'Newsletter updated!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $newsletter = Newsletter::whereId($id)->first();
        if (!$newsletter) {
            return response()->json([
                'message' => 'Newsletter not found!',
            ], 404);
        }
        $newsletter->delete();
        return response()->json([
            'message' => 'Newsletter deleted!',
        ], 204);
    }

    public function sendNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_ids' => '',
            'newsletter_id' => 'required',
        ]);
        if ($validator->fails()) {

            return response($validator->errors(), 422);
        }
        $newsletter = Newsletter::find($request->input('newsletter_id'));
        $clients = $request->input('client_ids') ? User::whereIn('id', json_decode($request->input('client_ids'), true))->get() : [];
        $send_mail = false;
        if (count($clients) && $newsletter) {
            $send_mail = true;
            foreach ($clients as $key => $client) {
                SendNewsletterJob::dispatch($client, $newsletter);
            }
        }
        return response()->json([
            'message' => 'Sending newsletter to client(s) is on queue process!',
        ], 200);
    }

    public function clientList()
    {
        $query = User::query();
        $query = $query->whereHas('roles', function ($query) {
            return $query->whereIn('name', ['Client']);
        });

        return $query
            ->get(['id', 'id as value', 'name', 'name as label', 'email']);
    }
}
