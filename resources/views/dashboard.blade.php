<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ auth()->user()->company->name ?? 'User Dashboard' }}
            </h2>
            <div class="flex items-center space-x-2">
                <span class="relative flex h-3 w-3">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-sm font-medium text-gray-600">AI Support Active</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase">Total Tickets</p>
                    <p class="text-2xl font-extrabold text-indigo-600">{{ \App\Models\Ticket::count() }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase">Organization</p>
                    <p class="text-lg font-bold text-gray-800 truncate">{{ auth()->user()->company->name ?? 'Freelancer / No Company' }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase">User Role</p>
                    <p class="text-lg font-bold text-purple-600 capitalize">{{ auth()->user()->role ?? 'Staff' }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-xs font-bold text-gray-500 uppercase">AI Model</p>
                    <p class="text-sm font-bold text-green-600">Gemini 2.5 Flash</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800">Support Management</h3>
                            <span class="text-xs text-gray-400">Real-time Updates</span>
                        </div>

                        <div class="lg:col-span-8 space-y-6">
                            <div class="bg-white rounded-2xl shadow-sm border p-0 overflow-hidden">
                                <livewire:ticket-list />
                            </div>

                            <livewire:ai-insights />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-md font-bold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Support Case
                        </h3>
                        <livewire:create-ticket />
                    </div>

                    <div class="bg-gray-900 rounded-2xl shadow-xl p-6 text-white overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-4 opacity-10">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 18a8 8 0 118-8 8 8 0 01-8 8z" />
                                <path
                                    d="M12 6a1 1 0 00-1 1v3H8a1 1 0 000 2h3v3a1 1 0 002 0v-3h3a1 1 0 000-2h-3V7a1 1 0 00-1-1z" />
                            </svg>
                        </div>
                        <h3 class="text-md font-bold mb-4 flex items-center">
                            <span class="mr-2">✨</span> AI Intelligence
                        </h3>
                        <livewire:ai-chat />
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>