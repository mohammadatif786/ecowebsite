<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmailTemplateRequest;
use App\Http\Requests\Admin\SendEmailRequest;
use App\Mail\CustomMail;
use App\Mail\SendEmailToUsers;
use App\Models\CaribbeanIsland;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Symfony\Component\Mailer\Exception\TransportException;

use function Termwind\render;

class EmailController extends Controller
{
    public function emailTemplatesIndex(Request $request)
    {
        // $emailTemplates = EmailTemplate::all();
        // return Inertia::render('admin/email/Index', compact('emailTemplates'));


        $emailTemplates = EmailTemplate::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/email/Index', [
            'emailTemplates' => $emailTemplates,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function emailTemplatesCreate()
    {
        return inertia::render('admin/email/CreateOrEdit');
    }

    public function emailTemplatesStore(EmailTemplateRequest $request)
    {
        $data = $request->Validated();
        EmailTemplate::create($data);


        $emailTemplates = EmailTemplate::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/email/Index', [
            'emailTemplates' => $emailTemplates,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function emailTemplatesEdit($id)
    {
        $emailTemplate = EmailTemplate::find($id);
        return inertia::render('admin/email/CreateOrEdit', compact('emailTemplate'));
    }

    public function emailTemplatesUpdate(EmailTemplateRequest $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validated();
        $emailTemplate->update($data);
        logger($emailTemplate);
        $emailTemplates = EmailTemplate::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/email/Index', [
            'emailTemplates' => $emailTemplates,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }
    public function emailTemplatesDestroy($id)
    {
        $emailTemplate = EmailTemplate::findOrfail($id);
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates-index')->with('messages', ['title' => 'User Updated successfully.']);
    }

    public function sendEmail()
    {
        $caribbean_islands = CaribbeanIsland::select('id as value', 'name as label')->get();
        $dropdownOptions = EmailTemplate::select('id as value', 'template_name as label')->get();
        $emailTemplates = EmailTemplate::all();
        return inertia::render('admin/email/sendEmail', [
            'dropdownOptions' => $dropdownOptions,
            'emailTemplates' => $emailTemplates,
            'caribbeanIslands' => $caribbean_islands
        ]);
    }

    public function postSendEmail(SendEmailRequest $request)
    {
        $data = $request->all();

        try {
            if ($data['send_to_bulk'] === false) {
                Mail::to($data['send_to_email'])->send(new SendEmailToUsers(_subject: $data['subject'], _content: $data['body']));
            }else{
                $users = [];
                //Send to all users
                if($data['send_to_by']==='all'){
                    $users = User::role('user')->get();
                }
                //Send to users from a specific country
                if ($data['send_to_by'] === 'country') {
                    $users = User::where('country', 'like', '%' . $data['send_to_by_value'] . '%')->get();
                }

                //Send to users from a specific state
                if ($data['send_to_by'] === 'state') {
                    $users = User::where('state', 'like', '%' . $data['send_to_by_value'] . '%')->get();
                }

                //Send to users from a specific city
                if ($data['send_to_by'] === 'city') {
                    $users = User::where('city', 'like', '%' . $data['send_to_by_value'] . '%')->get();
                }
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        Mail::to($user->email)->send(new SendEmailToUsers(_subject: $data['subject'], _content: $data['body']));
                    }
                }
            }
        } catch (TransportException $e) {
            Log::info($e->getMessage());
            return back()->with('error', 'Email could not be sent.');
        }
    }
}
