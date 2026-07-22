@extends('layouts.marketing')

@section('title', config('app.name') . ' — Reach everyone. Even offline.')

@section('content')
    @include('marketing.sections.header')

    <main>
        @include('marketing.sections.hero')
        <x-pulse-divider />
        @include('marketing.sections.trust-bar')
        <x-pulse-divider />
        @include('marketing.sections.inclusion')
        <x-pulse-divider />
        @include('marketing.sections.how-it-works')
        <x-pulse-divider />
        @include('marketing.sections.channels')
        <x-pulse-divider />
        @include('marketing.sections.offline-table')
        <x-pulse-divider />
        @include('marketing.sections.pricing')
        <x-pulse-divider />
        @include('marketing.sections.testimonial')
        <x-pulse-divider variant="dusk" />
        @include('marketing.sections.final-cta')
    </main>

    @include('marketing.sections.footer')
@endsection
