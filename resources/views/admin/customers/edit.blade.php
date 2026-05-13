@extends('layouts.app', ['title' => 'Edit customer', 'venue' => $venue])

@section('content')
<section class="hero compact">
    <div class="shell">
        <div class="eyebrow">Guest CRM</div>
        <h1>Edit {{ $customer->full_name }}</h1>
        <p>Update contact details, VIP status, guest preferences and private staff notes.</p>
    </div>
</section>

@include('admin.customers._form', [
    'action' => route('admin.customers.update', $customer),
    'method' => 'put',
    'submitLabel' => 'Save customer',
])
@endsection
