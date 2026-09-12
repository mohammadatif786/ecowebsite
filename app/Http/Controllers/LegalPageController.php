<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Inertia\Inertia;
use Illuminate\Support\Str;

class LegalPageController extends Controller
{
    public function showPrivacy()
    {
        return $this->show('privacy-policy');
    }

    public function showTerms()
    {
        return $this->show('terms-of-service');
    }

    public function showAcceptableUse()
    {
        return $this->show('acceptable-use-policy');
    }

    public function showProhibited()
    {
        return $this->show('prohibited-activities');
    }

    public function showRefund()
    {
        return $this->show('refund-policy');
    }

    public function showLawEnforcement()
    {
        return $this->show('law-enforcement-guidelines');
    }

    public function showPricing()
    {
        return $this->show('pricing-and-fees');
    }

    public function showSupport()
    {
        return $this->show('contact-and-customer-support');
    }

    public function showVibesPolicy()
    {
        return $this->show('vibes-acceptable-use-policy');
    }

    public function show($slug)
    {
        // Try to find the page in settings
        $settingKey = 'legal_page_' . $slug;
        $page = Settings::where('key', $settingKey)->first();

        if ($page) {
            $data = $page->value;
            
            // Check page visibility
            $isVisible = is_array($data) ? ($data['is_visible'] ?? true) : true;
            if (!$isVisible) {
                abort(404);
            }

            // Handle case if data is stored as a string or array
            $title = is_array($data) ? ($data['title'] ?? null) : null;
            $content = is_array($data) ? ($data['content'] ?? '') : (is_string($data) ? $data : '');
            $updatedAt = is_array($data) ? ($data['updated_at'] ?? null) : null;

            return Inertia::render('Legal/DynamicPage', [
                'title' => $title ?? Str::title(str_replace('-', ' ', $slug)),
                'content' => $content,
                'updated_at' => $updatedAt,
            ]);
        }

        // Fallback to static Vue templates if they exist
        $componentMap = [
            'privacy-policy' => 'Legal/PrivacyPolicy',
            'terms-of-service' => 'Legal/TermsOfService',
            'acceptable-use-policy' => 'Legal/AcceptableUsePolicy',
            'prohibited-activities' => 'Legal/ProhibitedActivities',
            'refund-policy' => 'Legal/RefundPolicy',
            'law-enforcement-guidelines' => 'Legal/LawEnforcementGuidelines',
            'pricing-and-fees' => 'Legal/PricingAndFees',
            'contact-and-customer-support' => 'Legal/ContactAndCustomerSupport',
            'vibes-acceptable-use-policy' => 'Legal/VibesAcceptableUsePolicy',
        ];

        if (array_key_exists($slug, $componentMap)) {
            return Inertia::render($componentMap[$slug]);
        }

        abort(404);
    }
}
