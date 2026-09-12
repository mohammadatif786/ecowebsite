<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FlaggedUser;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class UserReportsController extends Controller
{

    public function index(Request $request)
    {
        $flaggedusers = FlaggedUser::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/reportedusers/Index', [
            'users' => $flaggedusers,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function review($id)
    {
        $report = $this->db->collection('UserReports')->document($id)->snapshot();
        return view('user_reports.review', compact('report'));
    }

    public function saveReview(Request $request, $id)
    {
        $report = $this->db->collection('UserReports')->document($id)->snapshot();
    }

    public function changeStatus(FlaggedUser $flagged_user, Request $request)
    {
        // dd(flag);
        $flagged_user->update([
            'status' => (bool) $request->status
        ]);
        return response()->json(['message' => 'Status changed successfully']);
    }
}
