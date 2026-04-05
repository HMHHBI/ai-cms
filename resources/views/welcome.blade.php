<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AI-CMS | Smart Support System</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    </head>
    <body class="antialiased bg-white text-gray-900">
        
        <nav class="flex justify-between items-center px-8 py-6 max-w-7xl mx-auto">
            <div class="text-2xl font-extrabold text-indigo-600 tracking-tighter">AI-CMS.</div>
            <div class="space-x-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-bold text-gray-700 hover:text-indigo-600">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-bold text-gray-700 hover:text-indigo-600">Log in</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-full font-bold hover:bg-indigo-700 transition">Get Started</a>
                    @endauth
                @endif
            </div>
        </nav>

        <header class="max-w-7xl mx-auto px-8 py-20 text-center">
            <span class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-full text-sm font-bold uppercase tracking-widest">Powered by Gemini 2.5 Flash</span>
            <h1 class="text-6xl md:text-7xl font-extrabold mt-8 leading-tight tracking-tighter">
                Smart Support for <br>
                <span class="text-indigo-600">Modern Businesses.</span>
            </h1>
            <p class="mt-6 text-xl text-gray-500 max-w-2xl mx-auto">
                Automatic AI replies, sentiment analysis, and business insights—all in one place. Scale your customer support without the headache.
            </p>
            <div class="mt-10 flex justify-center space-x-4">
                <a href="{{ route('register') }}" class="bg-gray-900 text-white px-8 py-4 rounded-full font-bold text-lg hover:scale-105 transition-transform">Start Free Trial</a>
                <button class="border border-gray-300 px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-50">View Demo</button>
            </div>
        </header>

        <section class="bg-gray-50 py-24">
            <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                <div class="p-8 bg-white rounded-3xl shadow-sm border border-gray-100">
                    <div class="text-4xl mb-4">🤖</div>
                    <h3 class="text-xl font-bold mb-2">AI Auto-Reply</h3>
                    <p class="text-gray-500">Gemini analyzes tickets and drafts professional replies in seconds.</p>
                </div>
                <div class="p-8 bg-white rounded-3xl shadow-sm border border-gray-100">
                    <div class="text-4xl mb-4">🎭</div>
                    <h3 class="text-xl font-bold mb-2">Sentiment Analysis</h3>
                    <p class="text-gray-500">Know your customer's mood (Angry, Happy, Neutral) before you even open the ticket.</p>
                </div>
                <div class="p-8 bg-white rounded-3xl shadow-sm border border-gray-100">
                    <div class="text-4xl mb-4">📈</div>
                    <h3 class="text-xl font-bold mb-2">Business Insights</h3>
                    <p class="text-gray-500">Get weekly AI reports on common issues and product improvements.</p>
                </div>
            </div>
        </section>

        <footer class="py-12 border-t border-gray-100 text-center text-gray-400 text-sm">
            &copy; {{ date('Y') }} AI-CMS Developed by HMHHBI. Built with Laravel, Livewire & Gemini.
        </footer>

    </body>
</html>