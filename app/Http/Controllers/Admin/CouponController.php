<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\LinkUpEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $events = LinkUpEvent::select('id as value', 'title as label')->get();
        $paginator = Coupon::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->with('event')
            ->paginate(10);
        return Inertia::render('admin/coupons/Index', [
            'paginator' => $paginator,
            'filters' => $request->only('search'),
            'events' => $events,
        ]);
    }

    public function store(CouponRequest $request)
    {
        $data = $request->validated();
        $coupon = Coupon::create($data);
        $this->saveImage($request, $coupon);
        return redirect()->back()->withSuccess('Coupon Created Successfully');
    }
    //CouponRequest
    public function update(CouponRequest $request, Coupon $coupon)
    {
        // Log::info($request->all());
        $data = $request->validated();
        $coupon->update($data);
        $this->saveImage($request, $coupon);
        return redirect()->back()->withSuccess('Coupon Updated Successfully');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->back()->withSuccess('Coupon Deleted Successfully');
    }

    private function saveImage(Request $request, Coupon $coupon)
    {
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('images/eventCoupons', 'public');
            $coupon->image_object = $path;
            $coupon->save();
        }
    }
}
