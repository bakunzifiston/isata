<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feedback - {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-dawn-2/30 font-sans antialiased flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg border border-dawn-2/80 max-w-lg w-full p-8">
        <h1 class="text-xl font-bold text-ink">{{ $survey->name }}</h1>
        <p class="mt-2 text-ink/65">{{ $event->name }}</p>
        @if($survey->description)
        <p class="mt-2 text-sm text-ink/50">{{ $survey->description }}</p>
        @endif

        <form method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('feedback.store', ['event' => $event, 'attendee' => $attendee], now()->addDays(90)) }}" class="mt-6 space-y-6">
            @csrf
            @foreach($survey->questions as $q)
            <div>
                <label class="admin-label mb-1">
                    {{ $q['label'] }}
                    @if($q['required'] ?? false)
                    <span class="text-red-500">*</span>
                    @endif
                </label>
                @if(($q['type'] ?? 'text') === 'text')
                <input type="text" name="responses[{{ $q['id'] }}]" {{ ($q['required'] ?? false) ? 'required' : '' }}
                    class="admin-input">
                @elseif($q['type'] === 'rating')
                <div class="flex gap-2">
                    @foreach([1,2,3,4,5] as $r)
                    <label class="flex items-center">
                        <input type="radio" name="responses[{{ $q['id'] }}]" value="{{ $r }}" {{ ($q['required'] ?? false) ? 'required' : '' }}
                            class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                        <span class="ml-1 text-sm">{{ $r }}</span>
                    </label>
                    @endforeach
                </div>
                @elseif($q['type'] === 'select')
                <select name="responses[{{ $q['id'] }}]" {{ ($q['required'] ?? false) ? 'required' : '' }}
                    class="admin-input">
                    <option value="">— Select —</option>
                    @foreach($q['options'] ?? [] as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
                @elseif($q['type'] === 'multiple')
                <div class="space-y-2">
                    @foreach($q['options'] ?? [] as $opt)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="responses[{{ $q['id'] }}][]" value="{{ $opt }}"
                            class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                        <span class="text-sm">{{ $opt }}</span>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach

            <button type="submit" class="admin-btn-primary w-full px-4 py-3">
                Submit feedback
            </button>
        </form>
    </div>
</body>
</html>
