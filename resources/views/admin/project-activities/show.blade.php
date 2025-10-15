<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ trans('global.projectActivity.title') }} - {{ $project->title }} ({{ $fiscalYear->title }})
        </h1>
        <div class="flex flex-wrap items-center gap-4 mt-4">
            <a href="{{ route('admin.projectActivity.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ trans('global.back_to_list') }}
            </a>
            <a href="{{ route('admin.projectActivity.create', [$project->id, $fiscalYear->id]) }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ trans('global.create') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Activities</h3>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalActivities }}</p>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Total Planned Budget</h3>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format($totalPlanned, 2) }}
                </p>
            </div>
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Total Expenses</h3>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                    {{ number_format($totalExpense, 2) }}
                </p>
            </div>
        </div>


        <!-- Capital Expenditure Section -->
        <div class="mb-8">
            <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    {{ trans('global.projectActivity.headers.capital') }}
                </h3>
                <div class="overflow-x-auto">
                    <table id="capital-activities"
                        class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-600">
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                    #
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                    {{ trans('global.projectActivity.fields.program') }}
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Total Budget
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Expenses Till Date
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Planned Budget of this F/Y
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q1
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q2
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q3
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q4
                                </th>
                            </tr>
                        </thead>
                        <tbody id="capital-tbody">
                            @php $capitalFormIndex = 1; @endphp
                            @foreach ($capitalActivities->whereNull('parent_id') as $topActivity)
                                <tr class="projectActivity-row" data-depth="0" data-index="{{ $capitalFormIndex }}">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        {{-- Numbering handled by JS --}}
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <input name="capital[{{ $capitalFormIndex }}][program]" type="text"
                                            value="{{ old('capital.' . $capitalFormIndex . '.program', $topActivity->program) }}"
                                            class="w-full border-0 p-1 tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][total_budget]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.total_budget', $topActivity->total_budget) }}"
                                            class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][total_expense]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.total_expense', $topActivity->total_expense) }}"
                                            class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][planned_budget]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.planned_budget', $topActivity->planned_budget) }}"
                                            class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][q1]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.q1', $topActivity->q1) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][q2]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.q2', $topActivity->q2) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][q3]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.q3', $topActivity->q3) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[{{ $capitalFormIndex }}][q4]" type="number"
                                            step="0.01"
                                            value="{{ old('capital.' . $capitalFormIndex . '.q4', $topActivity->q4) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                </tr>
                                @php $capitalFormIndex++; @endphp

                                {{-- Level 1 Children --}}
                                @foreach ($topActivity->children as $level1Activity)
                                    <tr class="projectActivity-row" data-depth="1"
                                        data-index="{{ $capitalFormIndex }}"
                                        data-parent="{{ $capitalFormIndex - 1 }}">
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                            {{-- Numbering by JS --}}
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                            <input type="hidden" name="capital[{{ $capitalFormIndex }}][parent_id]"
                                                value="{{ $capitalFormIndex - 1 }}">
                                            <input name="capital[{{ $capitalFormIndex }}][program]" type="text"
                                                value="{{ old('capital.' . $capitalFormIndex . '.program', $level1Activity->program) }}"
                                                class="w-full border-0 p-1 tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][total_budget]"
                                                type="number" step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.total_budget', $level1Activity->total_budget) }}"
                                                class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][total_expense]"
                                                type="number" step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.total_expense', $level1Activity->total_expense) }}"
                                                class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][planned_budget]"
                                                type="number" step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.planned_budget', $level1Activity->planned_budget) }}"
                                                class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][q1]" type="number"
                                                step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.q1', $level1Activity->q1) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][q2]" type="number"
                                                step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.q2', $level1Activity->q2) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][q3]" type="number"
                                                step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.q3', $level1Activity->q3) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][q4]" type="number"
                                                step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.q4', $level1Activity->q4) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                    </tr>
                                    @php $capitalFormIndex++; @endphp

                                    {{-- Level 2 Children --}}
                                    @foreach ($level1Activity->children as $level2Activity)
                                        <tr class="projectActivity-row" data-depth="2"
                                            data-index="{{ $capitalFormIndex }}"
                                            data-parent="{{ $capitalFormIndex - 2 }}">
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                                {{-- Numbering by JS --}}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                                <input type="hidden"
                                                    name="capital[{{ $capitalFormIndex }}][parent_id]"
                                                    value="{{ $capitalFormIndex - 2 }}">
                                                <input name="capital[{{ $capitalFormIndex }}][program]"
                                                    type="text"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.program', $level2Activity->program) }}"
                                                    class="w-full border-0 p-1 tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][total_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.total_budget', $level2Activity->total_budget) }}"
                                                    class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][total_expense]"
                                                    type="number" step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.total_expense', $level2Activity->total_expense) }}"
                                                    class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][planned_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.planned_budget', $level2Activity->planned_budget) }}"
                                                    class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q1]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q1', $level2Activity->q1) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q2]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q2', $level2Activity->q2) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q3]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q3', $level2Activity->q3) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q4]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q4', $level2Activity->q4) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                        </tr>
                                        @php $capitalFormIndex++; @endphp
                                    @endforeach
                                @endforeach
                            @endforeach
                            @php $capitalMaxIndex = $capitalFormIndex - 1; @endphp
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recurrent Expenditure Section - Similar structure -->
        <div class="mb-8">
            <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <h3
                    class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    {{ trans('global.projectActivity.headers.recurrent') }}
                </h3>
                <div class="overflow-x-auto">
                    <table id="recurrent-activities"
                        class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <!-- Same thead as capital -->
                            <tr class="bg-gray-200 dark:bg-gray-600">
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                    #
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                    {{ trans('global.projectActivity.fields.program') }}
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Total Budget
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Expenses Till Date
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Planned Budget of this F/Y
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q1
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q2
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q3
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                    Q4
                                </th>
                            </tr>
                        </thead>
                        <tbody id="recurrent-tbody">
                            @php $recurrentFormIndex = 1; @endphp
                            @foreach ($recurrentActivities->whereNull('parent_id') as $topActivity)
                                <tr class="projectActivity-row" data-depth="0"
                                    data-index="{{ $recurrentFormIndex }}">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        {{-- Numbering handled by JS --}}
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][program]" type="text"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.program', $topActivity->program) }}"
                                            class="w-full border-0 p-1 tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][total_budget]"
                                            type="number" step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.total_budget', $topActivity->total_budget) }}"
                                            class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][total_expense]"
                                            type="number" step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.total_expense', $topActivity->total_expense) }}"
                                            class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][planned_budget]"
                                            type="number" step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.planned_budget', $topActivity->planned_budget) }}"
                                            class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][q1]" type="number"
                                            step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.q1', $topActivity->q1) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][q2]" type="number"
                                            step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.q2', $topActivity->q2) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][q3]" type="number"
                                            step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.q3', $topActivity->q3) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[{{ $recurrentFormIndex }}][q4]" type="number"
                                            step="0.01"
                                            value="{{ old('recurrent.' . $recurrentFormIndex . '.q4', $topActivity->q4) }}"
                                            class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                    </td>
                                </tr>
                                @php $recurrentFormIndex++; @endphp

                                {{-- Level 1 Children --}}
                                @foreach ($topActivity->children as $level1Activity)
                                    <tr class="projectActivity-row" data-depth="1"
                                        data-index="{{ $recurrentFormIndex }}"
                                        data-parent="{{ $recurrentFormIndex - 1 }}">
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                            {{-- Numbering by JS --}}
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                            <input type="hidden"
                                                name="recurrent[{{ $recurrentFormIndex }}][parent_id]"
                                                value="{{ $recurrentFormIndex - 1 }}">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][program]"
                                                type="text"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.program', $level1Activity->program) }}"
                                                class="w-full border-0 p-1 tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][total_budget]"
                                                type="number" step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.total_budget', $level1Activity->total_budget) }}"
                                                class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][total_expense]"
                                                type="number" step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.total_expense', $level1Activity->total_expense) }}"
                                                class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][planned_budget]"
                                                type="number" step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.planned_budget', $level1Activity->planned_budget) }}"
                                                class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][q1]" type="number"
                                                step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.q1', $level1Activity->q1) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][q2]" type="number"
                                                step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.q2', $level1Activity->q2) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][q3]" type="number"
                                                step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.q3', $level1Activity->q3) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="recurrent[{{ $recurrentFormIndex }}][q4]" type="number"
                                                step="0.01"
                                                value="{{ old('recurrent.' . $recurrentFormIndex . '.q4', $level1Activity->q4) }}"
                                                class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                        </td>
                                    </tr>
                                    @php $recurrentFormIndex++; @endphp

                                    {{-- Level 2 Children --}}
                                    @foreach ($level1Activity->children as $level2Activity)
                                        <tr class="projectActivity-row" data-depth="2"
                                            data-index="{{ $recurrentFormIndex }}"
                                            data-parent="{{ $recurrentFormIndex - 2 }}">
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                                {{-- Numbering by JS --}}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                                <input type="hidden"
                                                    name="recurrent[{{ $recurrentFormIndex }}][parent_id]"
                                                    value="{{ $recurrentFormIndex - 2 }}">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][program]"
                                                    type="text"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.program', $level2Activity->program) }}"
                                                    class="w-full border-0 p-1 tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][total_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.total_budget', $level2Activity->total_budget) }}"
                                                    class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][total_expense]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.total_expense', $level2Activity->total_expense) }}"
                                                    class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][planned_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.planned_budget', $level2Activity->planned_budget) }}"
                                                    class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q1]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q1', $level2Activity->q1) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q2]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q2', $level2Activity->q2) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q3]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q3', $level2Activity->q3) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q4]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q4', $level2Activity->q4) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                        </tr>
                                        @php $recurrentFormIndex++; @endphp
                                    @endforeach
                                @endforeach
                            @endforeach
                            @php $recurrentMaxIndex = $recurrentFormIndex - 1; @endphp
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
