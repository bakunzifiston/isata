@extends('layouts.dashboard')

@section('title', 'Edit sender · ' . config('app.name'))

@section('content')
<div class="mb-8">
    <a href="{{ route('email-senders.index') }}" class="text-sm text-ink/65 hover:text-ink">← Sender addresses</a>
    <h1 class="admin-page-title mt-2">Edit sender address</h1>
</div>

@include('email-senders._form', ['identity' => $identity, 'route' => route('email-senders.update', $identity), 'method' => 'PUT'])
@endsection
