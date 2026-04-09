<?php
use Livewire\Volt\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

new class extends Component {
  // List fetch karne ke liye
  public $selectedTicket = null;
  public $editedReply = '';
  public $filterMood = 'all';
  public $search = '';
  public $showClosed = false;

  public function updatingSearch()
  {

  }

  public function updatedEditedReply($value)
  {
    if ($this->selectedTicket) {
      Ticket::find($this->selectedTicket['id'])->update([
        'ai_suggestion' => $value
      ]);
    }
  }

  public function viewTicket($id)
  {
    $this->selectedTicket = Ticket::find($id);
    $this->editedReply = $this->selectedTicket->ai_suggestion;
  }

  public function closeTicket($id)
  {
    Ticket::find($id)->update(['status' => 'closed']);
    $this->selectedTicket = null;
    session()->flash('status', 'Ticket closed successfully.');
  }

  public function with(\App\Services\TicketService $query)
  {
    $staffMembers = \App\Models\User::where('company_id', Auth::user()->company_id)
      ->where('role', 'staff')->get();
    // Base Query fetch karein
    $query = Ticket::where('company_id', Auth::user()->company_id);

    // 1. Status Filter
    $query = $this->showClosed ? $query->where('status', 'closed') : $query->where('status', 'open');

    // 2. Mood Filter (Direct Database Column se)
    if ($this->filterMood !== 'all') {
      $query = $query->where('ai_sentiment', $this->filterMood);
    }

    // 3. Search Logic
    if ($this->search !== '') {
      $query = $query->where(function ($q) {
        $term = '%' . $this->search . '%';
        $q->where('subject', 'like', $term)
          ->orWhere('id', 'like', $term)
          ->orWhere('message', 'like', $term)
          ->orWhere('customer_name', 'like', $term)
          ->orWhere('customer_email', 'like', $term);
      });
    }

    // 4. Role-based restrictions (Staff vs Admin)
    if (Auth::user()->role !== 'admin') {
      $query->where(function ($q) {
        $q->where('user_id', Auth::id())
          ->orWhere('assigned_to', Auth::id())
          ->orWhereNull('assigned_to');
      });
    }

    return [
      'tickets' => $query->latest()->get(),
      'staffMembers' => $staffMembers
    ];
  }

  public function approveAndSend($ticketId)
  {
    $ticket = Ticket::find($ticketId);

    $customerName = $ticket->user ? $ticket->user->name : $ticket->customer_name;
    $customerEmail = $ticket->user ? $ticket->user->email : $ticket->customer_email;

    if (!$customerEmail) {
      session()->flash('error', 'No email address found for this ticket!');
      return;
    }

    // Asli Email Logic
    Mail::raw("Hi {$customerName}, \n\n{$this->editedReply}", function ($message) use ($ticket, $customerEmail) {
      $message->to($customerEmail)
        ->subject('Support Reply: ' . $ticket->subject);
    });

    $ticket->update([
      'status' => 'closed',
      'ai_suggestion' => $this->editedReply
    ]);

    $this->selectedTicket = null;
    session()->flash('status', 'Email sent to ' . $customerEmail . ' and ticket closed!');
  }

  public function deleteTicket($id)
  {
    // Sirf Admin hi delete kar sakay
    if (Auth::user()->role === 'admin') {
      Ticket::find($id)->delete();
      session()->flash('status', 'Ticket deleted successfully!');
    }
  }

  public function assignTicket($ticketId, $staffId)
  {
    // Debugging ke liye (Optional): Agar staffId 0 ya empty hai toh update na kare
    if (empty($staffId)) {
      $staffId = null;
    }

    $ticket = Ticket::find($ticketId);

    if ($ticket && $ticket->company_id === Auth::user()->company_id) {
      $ticket->assigned_to = $staffId;
      $ticket->save(); // Update ke bajaye save() use karein for better tracking

      session()->flash('status', 'Ticket updated successfully!');
    }
  }

  public function claimTicket($ticketId)
  {
    $ticket = Ticket::find($ticketId);
    if ($ticket && !$ticket->assigned_to) {
      $ticket->update(['assigned_to' => Auth::id()]);
      session()->flash('status', 'Ticket claimed! It is now in your list.');
    }
  }

  public function closeDetails()
  {
    $this->selectedTicket = null;
    $this->editedReply = '';
  }
}; ?>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
  <div class="bg-white dark:bg-gray-800 border-b border-gray-100">
    <div class="flex justify-center border-b border-gray-100 dark:border-gray-700 px-6">
      <button wire:click="$set('showClosed', false)"
        class="py-4 px-6 text-sm font-bold border-b-2 transition {{ !$showClosed ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-400' }}">
        📥 Active Inbox
      </button>
      <button wire:click="$set('showClosed', true)"
        class="py-4 px-6 text-sm font-bold border-b-2 transition {{ $showClosed ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-400' }}">
        📁 Closed Archive
      </button>
    </div>

    <div class="p-6 flex flex-col lg:flex-row justify-between items-center gap-4">
      <div class="relative w-full lg:w-1/2">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search customer, subject..."
          class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-xl leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-indigo-500 sm:text-sm transition">
      </div>

      <div class="inline-flex gap-1 bg-gray-100 dark:bg-gray-700 p-1 rounded-full shadow-inner">
        <button wire:click="$set('filterMood', 'all')"
          class="text-[10px] px-3 py-1.5 rounded-full font-bold transition {{ $filterMood == 'all' ? 'bg-white shadow text-indigo-600' : 'text-gray-500' }}">ALL</button>
        <button wire:click="$set('filterMood', 'negative')"
          class="text-[10px] px-3 py-1.5 rounded-full font-bold transition {{ $filterMood == 'negative' ? 'bg-red-500 text-white' : 'text-gray-500' }}">URGENT
          🔥</button>
        <button wire:click="$set('filterMood', 'neutral')"
          class="text-[10px] px-3 py-1.5 rounded-full font-bold transition {{ $filterMood == 'neutral' ? 'bg-blue-500 text-white' : 'text-gray-500' }}">NEUTRAL
          😐</button>
        <button wire:click="$set('filterMood', 'positive')"
          class="text-[10px] px-3 py-1.5 rounded-full font-bold transition {{ $filterMood == 'positive' ? 'bg-green-500 text-white' : 'text-gray-500' }}">HAPPY
          😊</button>
      </div>
    </div>
  </div>
  {{-- Table (Hidden on Mobile) --}}
  <div class="hidden md:block">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket Info</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mood</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        @foreach($tickets as $ticket)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-6 py-4">
              <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</div>
              <div class="text-xs text-gray-500 italic">From:
                {{ $ticket->user->name ?? $ticket->customer_name ?? 'Guest' }}
              </div>
            </td>

            <td class="px-6 py-4">
              @if(auth()->user()->role === 'admin')
                <select wire:change="assignTicket({{ $ticket->id }}, $event.target.value)"
                  class="text-xs rounded-lg border-gray-300 dark:bg-gray-700 dark:text-gray-300 py-1">
                  <option value="">Unassigned</option>
                  @foreach($staffMembers as $staff)
                    <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>
                      {{ $staff->name }}
                    </option>
                  @endforeach
                </select>
              @else
                @if($ticket->assigned_to == auth()->id())
                  <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Your Task</span>
                @else
                  <button wire:click="claimTicket({{ $ticket->id }})"
                    class="text-xs bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600">
                    Claim Ticket
                  </button>
                @endif
              @endif
            </td>

            <td class="px-6 py-4">
              <div class="flex flex-col gap-1">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold text-center uppercase {{ $ticket->status === 'open' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                  {{ $ticket->status }}
                </span>

                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold text-center uppercase border {{ $ticket->priority_color }}">
                  {{ $ticket->priority ?? 'Normal' }}
                </span>
              </div>
            </td>

            <td class="px-6 py-4">
              @if($ticket->mood === 'negative')
                <span class="text-red-600 text-xs font-bold">🔥 Urgent</span>
              @elseif($ticket->mood === 'positive')
                <span class="text-green-600 text-xs font-bold">😊 Happy</span>
              @elseif($ticket->mood === 'neutral')
                <span class="text-blue-600 text-xs font-bold">😐 Neutral</span>
              @else
                <span class="text-gray-400 text-xs">Unknown</span>
              @endif
            </td>

            <td class="px-6 py-4 text-right space-x-2">
              <button wire:click="viewTicket({{ $ticket->id }})"
                class="text-indigo-600 hover:text-indigo-900 text-xs font-bold uppercase">View</button>
              @if(auth()->user()->role === 'admin')
                <button wire:click="deleteTicket({{ $ticket->id }})" class="text-red-500 hover:text-red-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              @endif
            </td>
            <td class="px-6 py-4 text-xs font-medium">
              @php
                $hoursOld = $ticket->created_at->diffInHours(now());
              @endphp

              <span
                class="{{ ($hoursOld > 24 && $ticket->status == 'open') ? 'text-red-600 font-bold animate-pulse' : 'text-gray-500' }}">
                {{ $ticket->created_at->diffForHumans() }}
              </span>
            </td>
          </tr>
        @endforeach

      </tbody>
    </table>
  </div>
  {{-- Cards (Visible only on Mobile) --}}
  <div class="md:hidden space-y-4 p-4">
    @foreach($tickets as $ticket)
      <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 {{ $ticket->priority_color }}">
        <div class="flex justify-between items-start">
          <div>
            <h4 class="font-bold text-gray-800">{{ $ticket->subject }}</h4>
            <p class="text-[10px] text-gray-500 italic">From: {{ $ticket->customer_name ?? 'Guest' }}</p>
          </div>
          {{-- Mood Badge --}}
          <span class="text-xs">
            {{ $ticket->mood === 'negative' ? '🔥' : ($ticket->mood === 'positive' ? '😊' : '😐') }}
          </span>
        </div>

        <div class="mt-3 flex justify-between items-center text-[10px]">
          <span class="px-2 py-0.5 rounded-full bg-gray-100 font-bold uppercase">{{ $ticket->status }}</span>
          <button wire:click="viewTicket({{ $ticket->id }})" class="text-indigo-600 font-bold uppercase">View
            Details</button>
        </div>

        <div class="mt-3 flex justify-between items-center text-[10px]">
          <span class="px-2 py-0.5 rounded-full bg-gray-100 font-bold uppercase">{{ $ticket->priority }}</span>
          @if(auth()->user()->role === 'admin')
            <button wire:click="deleteTicket({{ $ticket->id }})" class="text-red-500 hover:text-red-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          @endif
        </div>

        <div class="mt-3 flex justify-end items-center text-[10px]">
          @if(auth()->user()->role === 'admin')
            <select wire:change="assignTicket({{ $ticket->id }}, $event.target.value)"
              class="text-xs rounded-lg border-gray-300 dark:bg-gray-700 dark:text-gray-300 py-1">
              <option value="">Unassigned</option>
              @foreach($staffMembers as $staff)
                <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>
                  {{ $staff->name }}
                </option>
              @endforeach
            </select>
          @else
            @if($ticket->assigned_to == auth()->id())
              <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Your Task</span>
            @else
              <button wire:click="claimTicket({{ $ticket->id }})"
                class="text-xs bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600">
                Claim Ticket
              </button>
            @endif
          @endif
        </div>
      </div>
    @endforeach
  </div>
  @if($selectedTicket)
    <div
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">

      <div
        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-8 border-t-2 border-indigo-600">

        <div class="flex justify-between items-start mb-6">
          <div>
            <h5 class="text-xl font-bold text-gray-900 dark:text-indigo-100 leading-tight">
              Ticket Details: {{ $selectedTicket->subject }}
            </h5>
            <p class="text-sm text-indigo-600 font-medium">Ticket #{{ $selectedTicket->id }}</p>
          </div>
          <button wire:click="closeDetails" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-6">
          <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-100">
            <span class="text-xs font-bold uppercase text-gray-400 tracking-wider">Customer Message</span>
            <p class="mt-2 text-gray-700 dark:text-gray-300 leading-relaxed italic">
              "{{ $selectedTicket->message }}"
            </p>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">
              ✍️ Official Response (AI Generated)
            </label>
            <textarea wire:model.live="editedReply" rows="8"
              class="block w-full rounded-xl border-gray-200 dark:bg-gray-900 dark:border-gray-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-4 mt-2 text-gray-700 dark:text-gray-300 leading-relaxed italic"></textarea>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <button wire:click="closeDetails"
              class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:bg-gray-100 rounded-xl transition">
              Cancel
            </button>
            <button wire:click="approveAndSend({{ $selectedTicket->id }})"
              class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition flex items-center">
              <span wire:loading.remove wire:target="approveAndSend">Approve & Send Email</span>
              <span wire:loading wire:target="approveAndSend">Sending...</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>