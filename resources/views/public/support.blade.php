<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - {{ $company->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900">Contact Support</h2>
            <p class="text-indigo-600 font-medium">{{ $company->name }}</p>
        </div>

        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('public.support.store', $company->slug) }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-semibold text-gray-700">Full Name</label>
                <input type="text" name="name" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Email Address</label>
                <input type="email" name="email" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Subject</label>
                <input type="text" name="subject" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Message</label>
                <textarea name="message" rows="4" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2.5 border"></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-indigo-700 transition duration-200 shadow-lg shadow-indigo-200">
                Submit Support Request
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-400 italic">
            Powered by Hassan AI-CMS & Gemini
        </p>
    </div>

</body>
</html>