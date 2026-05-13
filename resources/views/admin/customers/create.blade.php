@extends('layouts.app', ['title' => 'New customer', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Guest CRM</div>
        <h1>New customer</h1>
        <p>Create a customer profile for phone bookings, regulars, VIPs and guests with important preferences.</p>
    </div>
</section>

@include('admin.customers._form', [
    'action' => route('admin.customers.store'),
    'method' => 'post',
    'submitLabel' => 'Create customer',
])
@endsection
