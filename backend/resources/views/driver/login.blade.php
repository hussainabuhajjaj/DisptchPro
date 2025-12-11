<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/forms@0.5.7/dist/forms.min.css">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6 space-y-4">
        <div class="text-center space-y-2">
            <div class="text-sm uppercase tracking-wide text-slate-500">Driver Portal</div>
            <h1 class="text-2xl font-semibold text-slate-800">Login</h1>
            <p class="text-sm text-slate-500">Enter your Driver ID and phone to continue. We’ll issue your token automatically.</p>
        </div>

        <form method="POST" action="{{ route('driver.login.submit') }}" class="space-y-4">
            @csrf
            <label class="block">
                <span class="text-sm text-slate-700">Driver ID</span>
                <input name="driver_id" type="number" value="{{ old('driver_id') }}" required class="mt-1 block w-full rounded-lg border-slate-300">
            </label>
            <label class="block">
                <span class="text-sm text-slate-700">Phone</span>
                <input name="phone" type="text" value="{{ old('phone') }}" required class="mt-1 block w-full rounded-lg border-slate-300">
            </label>
            @if($errors->any())
                <div class="rounded-lg bg-rose-50 text-rose-700 text-sm px-3 py-2 border border-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif
            <button type="submit" class="w-full h-11 rounded-lg bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700">Login</button>
        </form>
    </div>
</body>
</html>
