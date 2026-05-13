<?php

namespace App\Services\Billing;

use App\Models\Venue;

class FeatureGate
{
    public function __construct(private BillingPlans $plans)
    {
    }

    public function canUse(Venue $venue, string $feature): bool
    {
        $plan = $this->currentPlan($venue);

        return in_array($feature, $plan['features'] ?? [], true);
    }

    public function currentPlan(Venue $venue): ?array
    {
        $subscription = $venue->subscription('default');

        if ($subscription && $subscription->valid()) {
            return $this->plans->findByPrice($subscription->stripe_price) ?: $this->plans->get('starter');
        }

        $localSubscription = $venue->activeSubscription;

        if ($localSubscription?->status === 'active') {
            return $this->plans->get($localSubscription->plan);
        }

        if ($localSubscription?->status === 'trialing'
            && (! $localSubscription->trial_ends_at || $localSubscription->trial_ends_at->isFuture())) {
            return $this->plans->get($localSubscription->plan);
        }

        return $this->plans->get('starter');
    }

    public function requiredPlanFor(string $feature): ?array
    {
        $featureConfig = $this->plans->feature($feature);

        return $featureConfig ? $this->plans->get($featureConfig['required_plan']) : null;
    }

    public function featureName(string $feature): string
    {
        return $this->plans->feature($feature)['name'] ?? str($feature)->replace('_', ' ')->title()->toString();
    }

    public function staffLimit(Venue $venue): ?int
    {
        return $this->currentPlan($venue)['staff_limit'] ?? null;
    }
}
