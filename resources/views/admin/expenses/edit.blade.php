<x-layouts.app>
    <nav class="mb-6 flex items-center text-sm text-gray-600 dark:text-gray-400" aria-label="Breadcrumb">
        <a href="{{ route('admin.expense.index') }}"
            class="hover:text-blue-500 dark:hover:text-blue-400">{{ __('Expenses') }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('Edit Expense') }}</span>
    </nav>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Edit Expense for:') }}
            {{ $expense->project->title }}</h1>

        <form method="POST" action="{{ route('admin.expense.update', $expense) }}"
            class="mt-6 space-y-6 bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
            @csrf
            @method('PUT')

            @if (session('success'))
                <div
                    class="col-span-full mb-6 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg dark:bg-green-900 dark:text-green-200 dark:border-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="col-span-full mb-6 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg dark:bg-red-900 dark:text-red-200 dark:border-red-700">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <x-forms.select label="{{ __('Project') }}" name="project_id" id="project_id" :options="collect($projects)
                    ->map(fn($project) => ['value' => (int) $project->id, 'label' => $project->title])
                    ->values()
                    ->all()"
                    :selected="old('project_id', $expense->project_id)" placeholder="{{ __('Select project') }}" :error="$errors->first('project_id')"
                    class="js-single-select" />
            </div>

            <div>
                <x-forms.select label="{{ __('Fiscal Year') }}" name="fiscal_year_id" id="fiscal_year_id"
                    :options="collect($fiscalYears)
                        ->map(fn($year) => ['value' => (int) $year->id, 'label' => $year->title])
                        ->values()
                        ->all()" :selected="old('fiscal_year_id', $expense->fiscal_year_id)" placeholder="{{ __('Select fiscal year') }}" :error="$errors->first('fiscal_year_id')"
                    class="js-single-select" />
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" id="available-budget"></p>
            </div>

            <div>
                <x-forms.select label="{{ __('Budget Type') }}" name="budget_type" id="budget_type" :options="[
                    ['value' => 'internal', 'label' => __('Internal Budget')],
                    ['value' => 'foreign_loan', 'label' => __('Foreign Loan Budget')],
                    ['value' => 'foreign_subsidy', 'label' => __('Foreign Subsidy Budget')],
                ]"
                    :selected="old('budget_type', $expense->budget_type)" placeholder="{{ __('Select budget type') }}" :error="$errors->first('budget_type')"
                    class="js-single-select" />
            </div>

            <div>
                <x-forms.input label="{{ __('Amount') }}" name="amount" id="amount" type="number" step="0.01"
                    min="0" :value="old('amount', $expense->amount)" required
                    class="w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :error="$errors->first('amount')" />
            </div>

            <div>
                <label for="description"
                    class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Description') }}</label>
                <textarea name="description" id="description"
                    class="mt-1 w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    rows="4">{{ old('description', $expense->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-forms.select label="{{ __('Quarter') }}" name="quarter" id="quarter" :options="[
                    ['value' => 1, 'label' => 'Q1'],
                    ['value' => 2, 'label' => 'Q2'],
                    ['value' => 3, 'label' => 'Q3'],
                    ['value' => 4, 'label' => 'Q4'],
                ]"
                    :selected="$expense->quarter" placeholder="{{ __('Select a quarter') }}" class="js-single-select" required />
                @error('quarter')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-forms.input label="{{ __('Date') }}" name="date" id="date" type="date"
                    :value="old('date', $expense->date->format('Y-m-d'))" required
                    class="w-full p-2 border rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :error="$errors->first('date')" />
                sightseeing
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.expense.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    aria-label="{{ __('Cancel') }}">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 dark:focus:ring-offset-gray-900"
                    aria-label="{{ __('Update Expense') }}">
                    {{ __('Update Expense') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function waitForJQuery() {
                if (window.jQuery && document.readyState === 'complete') {
                    initializeExpenseForm();
                } else {
                    setTimeout(waitForJQuery, 50);
                }

                function initializeExpenseForm() {
                    const $ = window.jQuery;
                    const $dateInput = $('#date');
                    const $projectSelect = $('.js-single-select[data-name="project_id"]');
                    const $fiscalYearSelect = $('.js-single-select[data-name="fiscal_year_id"]');
                    const $budgetTypeSelect = $('.js-single-select[data-name="budget_type"]');
                    const $availableBudget = $('#available-budget');

                    function updateAvailableBudget() {
                        const projectId = JSON.parse($projectSelect.attr('data-selected') || 'null');
                        const fiscalYearId = JSON.parse($fiscalYearSelect.attr('data-selected') || 'null');
                        const budgetType = JSON.parse($budgetTypeSelect.attr('data-selected') || 'null');

                        if (projectId && fiscalYearId && budgetType) {
                            $.ajax({
                                url: '{{ route('admin.budgets.available') }}',
                                data: {
                                    project_id: projectId,
                                    fiscal_year_id: fiscalYearId,
                                    budget_type: budgetType
                                },
                                success: function(response) {
                                    if (response.available) {
                                        $availableBudget.text(
                                            `Available ${budgetType} budget: ${response.available.toFixed(2)}`
                                        );
                                    } else {
                                        $availableBudget.text(
                                            'No budget allocated for this fiscal year and budget type.');
                                    }
                                },
                                error: function(xhr) {
                                    console.error('Error fetching available budget:', xhr.responseText);
                                    $availableBudget.text('Error fetching budget information.');
                                }
                            });
                        } else {
                            $availableBudget.text('');
                        }
                    }

                    $dateInput.on('change', function() {
                        const date = $(this).val();
                        if (!date) return;

                        $.ajax({
                            url: '{{ route('admin.fiscal-years.by-date') }}',
                            data: {
                                date: date
                            },
                            success: function(response) {
                                if (response.fiscal_year_id) {
                                    $fiscalYearSelect.attr('data-selected', JSON.stringify(response
                                        .fiscal_year_id));
                                    $fiscalYearSelect.trigger('options-updated', {
                                        options: JSON.parse($fiscalYearSelect.attr(
                                            'data-options') || '[]'),
                                        selected: response.fiscal_year_id
                                    });
                                    console.log('Pre-selected fiscal year:', response.fiscal_year_id);
                                    updateAvailableBudget();
                                }
                            },
                            error: function(xhr) {
                                console.error('Error fetching fiscal year:', xhr.responseText);
                            }
                        });
                    });

                    $projectSelect.on('change', updateAvailableBudget);
                    $fiscalYearSelect.on('change', updateAvailableBudget);
                    $budgetTypeSelect.on('change', updateAvailableBudget);

                    // Initial update
                    updateAvailableBudget();
                }
            })();
        </script>
    @endpush
</x-layouts.app>
