<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketTypesController extends Controller
{
    public function index() {}

    public function ticketTypes()
    {
        return Inertia::render('User/TicketType');
    }
}
