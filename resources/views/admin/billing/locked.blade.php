@extends('layouts.app', ['title' => $featureName.' locked', 'venue' => $venue])

@section('content')
<section class="shell locked-feature-wrap">
    <div class="panel locked-feature-card">
        <div class="eyebrow">Upgrade required</div>
        <h1>{{ $featureName }}</h1>
        <p>{{ $featureName }} is available on the {{ $requiredPlan['name'] }} plan and above. Your venue is currently on {{ $currentPlan['name'] ?? 'Starter' }}.</p>
        <div class="actions">
            <a class="button primary" href="{{ route('admin.billing.index') }}">View plans</a>
            <a class="button subtle" href="{{ route('admin.dashboard') }}">Back to dashboard</a>
        </div>
    </div>
</section>
@endsection
