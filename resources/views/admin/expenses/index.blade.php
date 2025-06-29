<x-layouts.app>
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Expenses') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage project expenses') }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.expense.create') }}"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-sm"
                aria-label="{{ __('Add New Expense') }}">
                {{ __('Add Expense') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 p-4 sm:p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <div class="mb-4">
            <x-forms.select label="{{ __('Filter by Project') }}" name="project_id" id="project_id" :options="collect(\App\Models\Project::all())
                ->map(fn($project) => ['value' => (int) $project->id, 'label' => $project->title])
                ->values()
                ->all()"
                placeholder="{{ __('Select project to filter') }}" class="js-single-select" :clearable="true" />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm sm:text-base">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Project') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Fiscal Year') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Budget Type') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Amount') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Description') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Date') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Quarter') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('User') }}</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($expenses as $expense)
                        <tr>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ $expense->project->title ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ $expense->fiscalYear->title ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ str_replace(['internal', 'foreign_loan', 'foreign_subsidy'], ['Internal', 'Foreign Loan', 'Foreign Subsidy'], $expense->budget_type) }}
                            </td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ number_format($expense->amount, 2) }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ Str::limit($expense->description, 50, '...') }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                {{ $expense->date->format('M d, Y') }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">Q{{ $expense->quarter }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $expense->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                <a href="{{ route('admin.expense.show', $expense) }}"
                                    class="inline-flex items-center px-2 py-1 bg-indigo-500 text-white text-xs rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ __('View') }}
                                </a>
                                <a href="{{ route('admin.expense.edit', $expense) }}"
                                    class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-xs rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 ml-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    {{ __('Edit') }}
                                </a>
                                <form action="{{ route('admin.expense.destroy', $expense) }}" method="POST"
                                    class="inline-flex ml-2"
                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this expense?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-2 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm"
            role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm"
            role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @push('scripts')
        <script>
            (function waitForJQuery() {
                if (window.jQuery && document.readyState === 'complete') {
                    initializeFilter();
                } else {
                    setTimeout(waitForJQuery, 50);
                }

                function initializeFilter() {
                    const $ = window.jQuery;
                    const $projectSelect = $('.js-single-select[data-name="project_id"]');

                    $projectSelect.on('change', function() {
                        const projectId = JSON.parse($(this).attr('data-selected') || 'null');
                        const baseUrl = '{{ route('admin.expense.index') }}';
                        window.location.href = projectId ? `${baseUrl}?project_id=${projectId}` : baseUrl;
                    });
                }
            })();
        </script>
    @endpush
</x-layouts.app>
