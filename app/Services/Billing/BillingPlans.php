<?php

namespace App\Services\Billing;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class BillingPlans
{
    public function all(): Collection
    {
        return collect(config('resora_billing.plans'))->map(fn (array $plan) => $plan);
    }

    public function get(string $slug): array
    {
        $plan = config("resora_billing.plans.$slug");

        if (! $plan) {
            throw new InvalidArgumentException("Unknown billing plan [$slug].");
        }

        return $plan;
    }

    public function findByPrice(?string $priceId): ?array
    {
        if (! $priceId) {
            return null;
        }

        return $this->all()->first(fn (array $plan) => $plan['stripe_price_id'] === $priceId);
    }

    public function feature(string $feature): ?array
    {
        return config("resora_billing.features.$feature");
    }

    public function trialDays(): int
    {
        return (int) config('resora_billing.trial_days', 14);
    }

    public function slugs(): array
    {
        return $this->all()->keys()->all();
    }
}
