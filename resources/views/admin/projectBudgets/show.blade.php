<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Project Budget Details') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Details for budget of project: ' . $projectBudget->project->title) }}</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    {{ __('Budget Information') }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Project') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $projectBudget->project->title }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fiscal Year') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $projectBudget->fiscalYear->title }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total Budget') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">
                            {{ number_format($projectBudget->total_budget, 2) }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Internal Budget') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">
                            {{ number_format($projectBudget->internal_budget, 2) }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Foreign Loan Budget') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">
                            {{ number_format($projectBudget->foreign_loan_budget, 2) }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Foreign Subsidy Budget') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">
                            {{ number_format($projectBudget->foreign_subsidy_budget, 2) }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Budget Revision') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">{{ $projectBudget->budget_revision }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Remaining Budget') }}</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-200">
                            {{ number_format($projectBudget->remaining_budget, 2) }}</p>
                    </div>
                </div>

                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-gray-100 mt-8 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    {{ __('Budget Revision History') }}
                </h3>

                @if ($revisions->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400">{{ __('No revision history available.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Revision') }}</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Internal Budget') }}</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Foreign Loan Budget') }}</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Foreign Subsidy Budget') }}</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Total Budget') }}</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Allocated At') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($revisions as $index => $revision)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ $index + 1 }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ number_format($revision->internal_budget, 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ number_format($revision->foreign_loan_budget, 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ number_format($revision->foreign_subsidy_budget, 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ number_format($revision->total_budget, 2) }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ $revision->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="mt-8 flex">
                    <a href="{{ route('admin.projectBudget.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                        {{ __('Back to Budgets') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
