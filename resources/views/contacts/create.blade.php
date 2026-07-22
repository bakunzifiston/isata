@extends('layouts.dashboard')

@section('title', 'Add Contact - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Add contact</h1>
    <p class="admin-page-subtitle">Contacts are shared across all events in your organization.</p>
</div>

<x-admin.card class="max-w-2xl">
    @include('contacts._form', ['contact' => null, 'route' => route('contacts.store'), 'method' => 'POST'])
</x-admin.card>
@endsection
