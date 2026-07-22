@extends('layouts.dashboard')

@section('title', 'Edit Contact - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Edit contact</h1>
</div>

<x-admin.card class="max-w-2xl">
    @include('contacts._form', ['contact' => $contact, 'route' => route('contacts.update', $contact), 'method' => 'PUT'])
</x-admin.card>
@endsection
