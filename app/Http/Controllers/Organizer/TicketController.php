<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\Organizer\CreateEditTicketService;
use App\Http\Requests\TicketFormRequest;

class TicketController extends Controller
{
    protected $createEditTicketService;

    public function __construct(CreateEditTicketService $createEditTicketService)
    {
        $this->createEditTicketService = $createEditTicketService;
    }
    public function index()
    {

        $indexData = $this->createEditTicketService->getIndexData();

        return Inertia::render('organizer/ticket/Index', [
            'indexData' => $indexData,
        ]);
    }

    public function storeOrUpdate(TicketFormRequest $request)
    {
        $user = Auth::user();

        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile) {
            abort(403, 'Unauthorized');
        }

        $validate = $request->validated();

        $ticket = $this->createEditTicketService->createOrUpdateTicket($validate);

        return response()->json([
            'message' => 'Ticket saved successfully',
            'ticket' => $ticket
        ], 200);
    }

    public function destroy(Ticket $ticket)
    {
        $user = Auth::user();
        $organizerProfile = OrganizerProfile::where('user_id', $user->id)->first();

        if (!$organizerProfile || $ticket->event->organizer_id !== $organizerProfile->id) {
            return response()->json(['message' => 'Unauthorized or ticket not found'], 403);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully'
        ], 200);
    }
}
