<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScanSignUser;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $scanners = ScanSignUser::query()->with(['user'])
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return Inertia::render('admin/scanners/Index', [
            'scanners' => $scanners,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizers = User::where('type', 'organizer')
            ->orderBy('name', 'ASC')
            ->get(['id', 'name']);
        return Inertia::render('admin/scanners/CreateEdit', [
            'organizers' => $organizers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data =  $request->validate([
            'user_id' => 'nullable',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:scan_sign_users,email',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'scanner_image_object' => 'nullable',
            'org_id' => 'nullable',
        ]);
        if ($request->hasFile('scanner_image_object')) {
            $data['scanner_image_object'] = $request->file('scanner_image_object')->store('scanners', 'public');
        }
        $data['org_id'] = $data['user_id'] ?? 'null'; // Default to logged-in user's ID if not provided

        ScanSignUser::create($data);

        return redirect()->route('admin.scanners.index')->with('message', 'Scanner created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $scanner = ScanSignUser::with(['user'])->findOrFail($id);
        $organizers = User::where('type', 'organizer')
            ->orderBy('name', 'ASC')
            ->get(['id', 'name']);
        return Inertia::render('admin/scanners/CreateEdit', [
            'scanner' => $scanner,
            'organizers' => $organizers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $scanner = ScanSignUser::findOrFail($id);
        $data =  $request->validate([
            'user_id' => 'nullable',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'scanner_image_object' => 'nullable',
            'org_id' => 'nullable',
        ]);

        if ($request->hasFile('scanner_image_object')) {
            $data['scanner_image_object'] = $request->file('scanner_image_object')->store('scanners', 'public');
        }
        $data['org_id'] = $data['user_id'] ?? 'null'; // Default to logged-in user's ID if not provided
        $scanner->update($data);

        return redirect()->route('admin.scanners.index')->with('message', 'Scanner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $scanner = ScanSignUser::findOrFail($id);
        $scanner->delete();

        return redirect()->route('admin.scanners.index')->with('message', 'Scanner deleted successfully.');
    }
}
