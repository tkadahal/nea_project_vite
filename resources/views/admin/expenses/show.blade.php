<x-layouts.app>
    <nav class="mb-6 flex items-center text-sm text-gray-600 dark:text-gray-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.project.index') }}"
            class="hover:text-blue-500 dark:hover:text-blue-400">{{ __('Projects') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.project.show', $expense->project) }}"
            class="hover:text-blue-500 dark:hover:text-blue-400">{{ $expense->project->title }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('Expense Details') }}</span>
    </nav>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Expense Details for:') }}
            {{ $expense->project->title }}</h1>

        <div
            class="mt-6 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Fiscal Year') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">
                        {{ $expense->fiscalYear->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Budget Type') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">
                        {{ str_replace(['internal', 'foreign_loan', 'foreign_subsidy'], ['Internal', 'Foreign Loan', 'Foreign Subsidy'], $expense->budget_type) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{ number_format($expense->amount, 2) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Date') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{ $expense->date->format('M d, Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Quarter') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">Q{{ $expense->quarter }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('User') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{ $expense->user->name ?? 'N/A' }}</p>
                </div>
                <div class="col-span-full">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</p>
                    <p class="mt-1 text-base text-gray-900 dark:text-gray-100">{{ $expense->description ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.expense.edit', $expense) }}"
                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-sm"
                    aria-label="{{ __('Edit Expense') }}">
                    {{ __('Edit Expense') }}
                </a>
                <form action="{{ route('admin.expense.destroy', $expense) }}" method="POST"
                    onsubmit="return confirm('{{ __('Are you sure you want to delete this expense?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-sm"
                        aria-label="{{ __('Delete Expense') }}">
                        {{ __('Delete Expense') }}
                    </button>
                </form>
                <a href="{{ route('admin.project.show', $expense->project) }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 text-sm"
                    aria-label="{{ __('Back to Project') }}">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
