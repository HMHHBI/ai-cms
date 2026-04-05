<?php
use Livewire\Volt\Component;
use App\Models\Ticket;
use App\Services\GeminiService;

new class extends Component {
    public string $analysis = '';
    public bool $loading = false;

    public function generateInsights(GeminiService $ai)
    {
        $this->loading = true;

        // 1. Saare tickets ka data jama karein
        $ticketsData = Ticket::latest()->take(10)->pluck('message')->implode(' | ');

        if (empty($ticketsData)) {
            $this->analysis = "No tickets found to analyze.";
            $this->loading = false;
            return;
        }

        // 2. AI ko prompt bhejein
        $prompt = "Analyze these customer support tickets: '{$ticketsData}'. 
                   Provide a short bullet-point summary of:
                   1. Common problems users are facing.
                   2. Overall customer sentiment.
                   3. One suggestion to improve the product.";

        $this->analysis = $ai->generateResponse($prompt);
        $this->loading = false;
    }
}; ?>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mt-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center">
            <span class="mr-2">📊</span> AI Business Insights
        </h3>
        <button wire:click="generateInsights"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 shadow-md"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Generate Report</span>
            <span wire:loading>Analyzing Data...</span>
        </button>
    </div>

    @if($analysis)
        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded text-gray-700">
            <div class="prose prose-sm max-w-none">
                {!! nl2br(e($analysis)) !!}
            </div>
        </div>
    @else
        <p class="text-gray-500 text-sm italic text-center py-4">
            Click the button to let AI scan your recent tickets for patterns.
        </p>
    @endif
</div>