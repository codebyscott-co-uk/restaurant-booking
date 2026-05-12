<?php

namespace App\Http\Controllers;

use App\Services\Billing\FeatureGate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFeatureController extends Controller
{
    public function locked(Request $request, string $feature, FeatureGate $features): View
    {
        $venue = $this->currentVenue($request);
        $requiredPlan = $features->requiredPlanFor($feature);

        abort_unless($requiredPlan, 404);

        return view('admin.billing.locked', [
            'venue' => $venue,
            'feature' => $feature,
            'featureName' => $features->featureName($feature),
            'requiredPlan' => $requiredPlan,
            'currentPlan' => $features->currentPlan($venue),
        ]);
    }

    public function customers(Request $request): View
    {
        $venue = $this->currentVenue($request);

        return view('admin.billing.feature', [
            'venue' => $venue,
            'title' => 'Customer CRM',
            'eyebrow' => 'Professional feature',
            'message' => 'Customer notes, guest history and CRM workflows are available on Professional and Premium plans.',
        ]);
    }

    public function reports(Request $request): View
    {
        return view('admin.billing.feature', [
            'venue' => $this->currentVenue($request),
            'title' => 'Analytics & reporting',
            'eyebrow' => 'Professional feature',
            'message' => 'Track booking trends, guest demand, service mix and operational performance.',
        ]);
    }

    public function waitlist(Request $request): View
    {
        return view('admin.billing.feature', [
            'venue' => $this->currentVenue($request),
            'title' => 'Premium waitlist',
            'eyebrow' => 'Premium feature',
            'message' => 'Waitlist and premium demand-management modules are reserved for Premium venues.',
        ]);
    }
}
