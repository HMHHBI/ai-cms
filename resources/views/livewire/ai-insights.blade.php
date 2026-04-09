<?php
use Livewire\Volt\Component;
use App\Models\Ticket;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $analysis = '';
    public bool $loading = false;

    public function getInsightProperty()
    {
        $companyId = Auth::user()->company_id;
        $cacheKey = "ai_insight_company_{$companyId}";

        // 1. Pehle check karein kya cache mein kuch para hai?
        return cache()->remember($cacheKey, now()->addHours(1), function () use ($companyId) {

            // 2. Agar cache khali hai (ya 1 ghanta guzar gaya), tab ye code chalay ga:
            $tickets = Ticket::where('company_id', $companyId)
                ->where('status', 'open')
                ->get();

            if ($tickets->isEmpty()) {
                return "Sab set hai! Filhal koi naya masla nahi hai. ☕";
            }

            $dataSummary = $tickets->map(fn($t) => "Sub: {$t->subject}, Mood: {$t->ai_sentiment}")->implode('; ');

            $prompt = "Support manager summary for tickets: [{$dataSummary}]. 2 lines only. Hinglish tone.";

            try {
                // Sirf tab call hoga jab cache expire hogi
                return App(GeminiService::class)->generateResponse($prompt);
            } catch (\Exception $e) {
                return "AI is busy. Try again later.";
            }
        });
    }

    public function refreshInsight()
    {
        $cacheKey = "ai_insight_company_" . Auth::user()->company_id;
        cache()->forget($cacheKey); // Purana cache urao
        // Refresh hote hi Livewire khud dobara fetch kar lega
    }
}; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mt-8">
    <div
        class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 opacity-10">
            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2a10 10 0 1010 10A10 10 0 0012 2z" />
            </svg>
        </div>

        <h3 class="text-lg font-bold flex items-center gap-2">
            <span>✨</span> AI Manager Insight
            <button wire:click="refreshInsight" class="text-white/50 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </h3>
        {{-- Blade mein heading ke saath --}}


        <div
            class="mt-4 text-sm leading-relaxed font-medium bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/20">
            {{ $this->insight }}
        </div>

        <div class="mt-4 flex items-center gap-2">
            <span class="flex h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>
            <span class="text-[10px] uppercase tracking-widest opacity-70">Live Analysis</span>
        </div>
    </div>
</div>