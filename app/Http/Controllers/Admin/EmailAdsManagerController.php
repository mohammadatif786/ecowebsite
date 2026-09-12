<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\EmailAdCategory;
use App\Models\EmailSponsorAd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class EmailAdsManagerController extends Controller
{
    public function page()
    {
        return Inertia::render('admin/ads/EmailAdsManager', [
            'categories' => EmailAdCategory::withCount('ads')->orderBy('label')->get(),
            'countries' => Country::orderBy('subregion')->orderBy('name')->get(),
        ]);
    }



    public function categoriesStore(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'short_label' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'icon_file' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        $data['key'] = Str::slug($data['label'], '_');
        $data['preview_title'] = $data['label'];
        $data['preview_subtitle'] = 'Your dynamic email content appears here.';

        if ($request->hasFile('icon_file')) {
            $data['icon'] = $request->file('icon_file')->store('category_icons', 'public');
        }

        unset($data['icon_file']);

        $category = EmailAdCategory::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category->loadCount('ads'),
            ], 201);
        }

        return back()->with('success', 'Category created successfully.');
    }

    public function categoriesDestroy(Request $request, EmailAdCategory $category)
    {
        if ($category->is_system) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'System categories cannot be deleted.'], 422);
            }

            return back()->with('error', 'System categories cannot be deleted.');
        }

        if ($category->ads()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete category with existing ads.'], 422);
            }

            return back()->with('error', 'Cannot delete category with existing ads.');
        }

        $deletedId = $category->id;
        $category->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'id' => $deletedId]);
        }

        return back()->with('success', 'Category deleted successfully.');
    }

    public function adsIndex(Request $request)
    {
        $ads = EmailSponsorAd::with(['category', 'countries'])
            ->when($request->filled('email_ad_category_id') && $request->email_ad_category_id !== 'all', function ($q) use ($request) {
                $q->where('email_ad_category_id', $request->email_ad_category_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($ads);
    }

    public function adsStore(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'email_ad_category_id' => 'required|exists:email_ad_categories,id',
            'country_ids' => 'nullable|array',
            'country_ids.*' => 'exists:countries,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:1|max:999',
            'status' => 'required|in:active,paused,draft',
            'headline' => 'required|string|max:255',
            'message' => 'required|string',
            'cta_text' => 'required|string|max:255',
            'cta_url' => 'required|url',
            'image_file' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
            'all_regions' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('sponsor_ads', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->image_url;
        }

        $ad = EmailSponsorAd::create($data);

        if ($request->boolean('all_regions')) {
            $ad->countries()->sync([]);
        } elseif ($request->filled('country_ids')) {
            $ad->countries()->sync($request->country_ids);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'ad' => $ad->load(['category', 'countries'])->loadCount(['impressions', 'clicks']),
            ], 201);
        }

        return back()->with('success', 'Ad created successfully.');
        // return response()->json(['data' => $ad->load(['category', 'countries'])], 201);
    }

    public function adsUpdate(Request $request, EmailSponsorAd $ad)
    {
        $data = $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'email_ad_category_id' => 'sometimes|required|exists:email_ad_categories,id',
            'country_ids' => 'nullable|array',
            'country_ids.*' => 'exists:countries,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:1|max:999',
            'status' => 'sometimes|required|in:active,paused,draft',
            'headline' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string',
            'cta_text' => 'sometimes|required|string|max:255',
            'cta_url' => 'sometimes|required|url',
            'image_file' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
            'all_regions' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('sponsor_ads', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->image_url;
        }

        $ad->update($data);

        if ($request->boolean('all_regions')) {
            $ad->countries()->sync([]);
        } elseif ($request->has('country_ids')) {
            $ad->countries()->sync($request->country_ids);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'ad' => $ad->load(['category', 'countries'])->loadCount(['impressions', 'clicks']),
            ]);
        }

        return back()->with('success', 'Ad updated successfully.');
        // return response()->json(['data' => $ad->load(['category', 'countries'])]);
    }

    public function adsDestroy(Request $request, EmailSponsorAd $ad)
    {
        $deletedId = $ad->id;
        $ad->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'id' => $deletedId]);
        }

        return back()->with('success', 'Ad deleted successfully.');
    }

    public function trackClick(Request $request, EmailSponsorAd $ad)
    {
        \App\Models\SponsorClick::create([
            'email_sponsor_ad_id' => $ad->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'clicked_at' => now(),
        ]);

        return redirect($ad->cta_url);
    }
}
