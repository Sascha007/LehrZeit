<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Billing Period: {{ DateTime::createFromFormat('!m', $billingPeriod->month)->format('F') }} {{ $billingPeriod->year }}
            </h2>
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                @if($billingPeriod->status === 'OPEN') bg-green-100 text-green-800
                @elseif($billingPeriod->status === 'SUBMITTED') bg-blue-100 text-blue-800
                @elseif($billingPeriod->status === 'APPROVED') bg-purple-100 text-purple-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ $billingPeriod->status }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Summary Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Hours</p>
                            <p class="text-2xl font-bold">{{ number_format($billingPeriod->total_hours, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Expenses</p>
                            <p class="text-2xl font-bold">€{{ number_format($billingPeriod->total_expenses, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Teaching Sessions</p>
                            <p class="text-2xl font-bold">{{ $billingPeriod->teachingSessions->count() }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-2">
                        @can('update', $billingPeriod)
                            @if($billingPeriod->canBeSubmitted())
                                <form method="POST" action="{{ route('billing-periods.submit', $billingPeriod) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Submit for Approval
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('approve', $billingPeriod)
                            <form method="POST" action="{{ route('admin.billing-periods.approve', $billingPeriod) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Approve
                                </button>
                            </form>
                        @endcan

                        @can('reopen', $billingPeriod)
                            <form method="POST" action="{{ route('admin.billing-periods.reopen', $billingPeriod) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Reopen
                                </button>
                            </form>
                        @endcan

                        @can('export', $billingPeriod)
                            <form method="POST" action="{{ route('admin.billing-periods.mark-exported', $billingPeriod) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Mark as Exported
                                </button>
                            </form>
                        @endcan

                        <a href="{{ route('billing-periods.export.xlsx', $billingPeriod) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Export XLSX
                        </a>

                        <a href="{{ route('billing-periods.export.csv', $billingPeriod) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Export CSV
                        </a>

                        @can('delete', $billingPeriod)
                            <form method="POST" action="{{ route('billing-periods.destroy', $billingPeriod) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this billing period?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Delete Period
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Teaching Sessions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Teaching Sessions</h3>
                        @if($billingPeriod->isEditable() && $billingPeriod->user_id === Auth::id())
                            <a href="{{ route('teaching-sessions.create', ['billing_period_id' => $billingPeriod->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Add Session
                            </a>
                        @endif
                    </div>

                    @if($billingPeriod->teachingSessions->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">No teaching sessions recorded.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hours</th>
                                        @if($billingPeriod->isEditable() && $billingPeriod->user_id === Auth::id())
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($billingPeriod->teachingSessions as $session)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $session->date->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $session->start_time }} - {{ $session->end_time }}</td>
                                            <td class="px-6 py-4">{{ $session->subject }}</td>
                                            <td class="px-6 py-4">{{ $session->location ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($session->hours, 2) }}</td>
                                            @if($billingPeriod->isEditable() && $billingPeriod->user_id === Auth::id())
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('teaching-sessions.edit', $session) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</a>
                                                    <form method="POST" action="{{ route('teaching-sessions.destroy', $session) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Expenses -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Expenses</h3>
                        @if($billingPeriod->isEditable() && $billingPeriod->user_id === Auth::id())
                            <a href="{{ route('expenses.create', ['billing_period_id' => $billingPeriod->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Add Expense
                            </a>
                        @endif
                    </div>

                    @if($billingPeriod->expenses->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">No expenses recorded.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Receipt</th>
                                        @if($billingPeriod->isEditable() && $billingPeriod->user_id === Auth::id())
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($billingPeriod->expenses as $expense)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $expense->date->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4">{{ $expense->category }}</td>
                                            <td class="px-6 py-4">{{ Str::limit($expense->description, 50) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">€{{ number_format($expense->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($expense->receipt_path)
                                                    <a href="{{ route('expenses.download-receipt', $expense) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Download</a>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                            @if($billingPeriod->isEditable() && $billingPeriod->user_id === Auth::id())
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Edit</a>
                                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
