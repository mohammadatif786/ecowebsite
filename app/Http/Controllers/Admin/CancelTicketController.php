<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CancellationRequests;
use App\Models\TicketSale;
use App\Models\User;
use Illuminate\Http\Request;
use O21\LaravelWallet\Models\Custodian;
use Inertia\Inertia;

class CancelTicketController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $requests = TicketSale::where('ticket_status', 'cancelled')->with('event', 'user')->whereDoesntHave('cancellationRequest')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                        ->orWhereHas('event', function ($q2) use ($search) {
                            $q2->where('title', 'like', "%{$search}%");
                        });
                });
            })->get();
        $requestOrders = CancellationRequests::with('user', 'event')->get();
        $user_transactions = CancellationRequests::where('status', 'Approve + Refund')->with('user')->get()->groupBy('user_id');
        $formatted_transactions = $user_transactions->map(function ($data) {
            $transactions = $data->map(function ($item) {
                return [
                    'amount' => $item->refund_amount
                ];
            });

            return [
                'name' => $data[0]?->user?->name,
                'email' => $data[0]?->user?->email,
                'transactions' => $transactions
            ];
        })->values();

        return Inertia::render('admin/events/cancelTicket/Index', [
            'requests' =>  $requests,
            'orders' =>  $requestOrders,
            'users' => $formatted_transactions
        ]);
    }

    public function CreateRequest(Request $request)
    {
        $request->validate([
            "type" => "required",
            "admin_note" => "nullable",
            "ticket" => "required|array",
            "admin_controll" => "required",
            "ammount" => "required",
        ]);
        $ticket = $request->ticket ?? [];
        $deduct = (float) $ticket['total'] - (float) $request->ammount;
        CancellationRequests::create([
            'user_id' => $ticket['user_id'],
            'ticket_id' => $ticket['id'],
            'event_id' => $ticket['link_up_event_id'],
            'admin_reason' => $request->admin_note,
            'status' => $request->type,
            'admin_controll' => $request->admin_controll,
            'refund_amount' => $request->ammount,
            'deduct_ammount' => $deduct,
        ]);
        $user = User::find($ticket['user_id']);
        if ($request->type == 'Approve + Refund') {
            deposit($request->ammount, 'USD')->from(Custodian::of('e_money'))->to($user)->overcharge()->commit();
        }
        return back()->withSuccess('Request Created successfully.');
    }
}
