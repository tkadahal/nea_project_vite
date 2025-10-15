{{-- resources/views/admin/project-activities/edit.blade.php --}}
<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ trans('global.projectActivity.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.edit') }} {{ trans('global.projectActivity.title_singular') }}
        </p>

        <!-- Excel Actions - Optional for edit, or remove if not needed -->
        <div class="mb-6 flex flex-wrap items-center gap-4">
            <a href="{{ route('admin.projectActivity.template') }}"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600"
                title="Download Excel Template">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Download Excel Template
            </a>
            <a href="{{ route('admin.projectActivity.uploadForm') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                title="Upload Excel">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                    </path>
                </svg>
                Upload Excel
            </a>
        </div>
    </div>

    <form id="projectActivity-form" class="w-full"
        action="{{ route('admin.projectActivity.update', [$project->id, $fiscalYear->id]) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT') {{-- For update, if needed; adjust route if PATCH/PUT --}}

        <div
            class="mb-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Project Selection - Pre-selected -->
                <div class="w-full md:w-1/2 relative z-50">
                    <x-forms.select label="{{ trans('global.budget.fields.project_id') }}" name="project_id"
                        id="project_id" :options="$projectOptions" :selected="$projectId"
                        placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('project_id')" class="js-single-select"
                        required />
                </div>

                <!-- Fiscal Year Selection - Pre-selected -->
                <div class="w-full md:w-1/2 relative z-50">
                    <x-forms.select label="{{ trans('global.budget.fields.fiscal_year_id') }}" name="fiscal_year_id"
                        id="fiscal_year_id" :options="$fiscalYears" :selected="$fiscalYearId"
                        placeholder="{{ trans('global.pleaseSelect') }}" :error="$errors->first('fiscal_year_id')" class="js-single-select"
                        required />
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">

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

            <div id="error-message"
                class="col-span-full mb-6 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative dark:bg-red-900 dark:border-gray-700 dark:text-red-200">
                <span id="error-text"></span>
                <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20">
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </button>
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
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-24">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="capital-tbody">
                                @php $capitalFormIndex = 1; @endphp
                                @foreach ($capitalActivities->whereNull('parent_id') as $topActivity)
                                    <tr class="projectActivity-row" data-depth="0"
                                        data-index="{{ $capitalFormIndex }}">
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
                                            <input name="capital[{{ $capitalFormIndex }}][total_expense]"
                                                type="number" step="0.01"
                                                value="{{ old('capital.' . $capitalFormIndex . '.total_expense', $topActivity->total_expense) }}"
                                                class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                            <input name="capital[{{ $capitalFormIndex }}][planned_budget]"
                                                type="number" step="0.01"
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
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                            <span class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>
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
                                                <input type="hidden"
                                                    name="capital[{{ $capitalFormIndex }}][parent_id]"
                                                    value="{{ $capitalFormIndex - 1 }}">
                                                <input name="capital[{{ $capitalFormIndex }}][program]"
                                                    type="text"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.program', $level1Activity->program) }}"
                                                    class="w-full border-0 p-1 tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][total_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.total_budget', $level1Activity->total_budget) }}"
                                                    class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][total_expense]"
                                                    type="number" step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.total_expense', $level1Activity->total_expense) }}"
                                                    class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][planned_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.planned_budget', $level1Activity->planned_budget) }}"
                                                    class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q1]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q1', $level1Activity->q1) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q2]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q2', $level1Activity->q2) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q3]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q3', $level1Activity->q3) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="capital[{{ $capitalFormIndex }}][q4]" type="number"
                                                    step="0.01"
                                                    value="{{ old('capital.' . $capitalFormIndex . '.q4', $level1Activity->q4) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                                <div class="flex space-x-2 justify-center">
                                                    <span
                                                        class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>
                                                    <span class="remove-row cursor-pointer text-2xl text-red-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </span>
                                                </div>
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
                                                <td
                                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                                    <div class="flex space-x-2 justify-center">
                                                        <span class="remove-row cursor-pointer text-2xl text-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </span>
                                                    </div>
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
                    <button type="button" id="add-capital-row"
                        class="mt-4 bg-green-500 text-white px-4 py-2 rounded">
                        Add New Row
                    </button>
                    <div class="mt-4 text-lg font-bold">
                        Total Capital Budget: <span id="capital-total">0.00</span>
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
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-24">
                                        Actions
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
                                            <input name="recurrent[{{ $recurrentFormIndex }}][program]"
                                                type="text"
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
                                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                            <span class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>
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
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][total_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.total_budget', $level1Activity->total_budget) }}"
                                                    class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][total_expense]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.total_expense', $level1Activity->total_expense) }}"
                                                    class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][planned_budget]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.planned_budget', $level1Activity->planned_budget) }}"
                                                    class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q1]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q1', $level1Activity->q1) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q2]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q2', $level1Activity->q2) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q3]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q3', $level1Activity->q3) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                                <input name="recurrent[{{ $recurrentFormIndex }}][q4]"
                                                    type="number" step="0.01"
                                                    value="{{ old('recurrent.' . $recurrentFormIndex . '.q4', $level1Activity->q4) }}"
                                                    class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                                            </td>
                                            <td
                                                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                                <div class="flex space-x-2 justify-center">
                                                    <span
                                                        class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>
                                                    <span class="remove-row cursor-pointer text-2xl text-red-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </span>
                                                </div>
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
                                                    <input
                                                        name="recurrent[{{ $recurrentFormIndex }}][planned_budget]"
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
                                                <td
                                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                                    <div class="flex space-x-2 justify-center">
                                                        <span class="remove-row cursor-pointer text-2xl text-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </span>
                                                    </div>
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
                    <button type="button" id="add-recurrent-row"
                        class="mt-4 bg-green-500 text-white px-4 py-2 rounded">
                        Add New Row
                    </button>
                    <div class="mt-4 text-lg font-bold">
                        Total Recurrent Budget: <span id="recurrent-total">0.00</span>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <x-buttons.primary id="submit-button" type="submit" :disabled="false">
                    {{ trans('global.save') }}
                </x-buttons.primary>
            </div>
        </div>
    </form>

    @push('scripts')
        <style>
            .error-border {
                border: 2px solid red !important;
            }

            .tooltip-error {
                position: relative;
            }

            .tooltip-error .tippy-box {
                background-color: #ef4444;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
            }

            .tooltip-error .tippy-arrow {
                color: #ef4444;
            }

            .projectActivity-row[data-depth="1"] td:nth-child(2) {
                padding-left: 20px;
            }

            .projectActivity-row[data-depth="2"] td:nth-child(2) {
                padding-left: 40px;
            }
        </style>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://unpkg.com/@popperjs/core@2"></script>
        <script src="https://unpkg.com/tippy.js@6"></script>
        <script>
            $(document).ready(function() {
                const $ = jQuery;

                let capitalIndex = {{ $capitalMaxIndex + 1 }};
                let recurrentIndex = {{ $recurrentMaxIndex + 1 }};
                const tippyInstances = new WeakMap();

                $('#project_id').on('change', function() {
                    $('#projectActivity-form').attr('action',
                        '{{ route('admin.projectActivity.update', [$project->id, $fiscalYear->id]) }}');
                });

                // Check if all rows in a section have valid planned_budget = Q1 + Q2 + Q3 + Q4
                function isTableValid(section) {
                    let isValid = true;
                    $(`#${section}-activities .projectActivity-row`).each(function() {
                        const $row = $(this);
                        const index = $row.data('index');
                        const plannedBudget = parseFloat($row.find('.planned-budget-input').val()) || 0;
                        const quarterSum = Array.from($row.find('.quarter-input')).reduce((sum, input) => {
                            return sum + (parseFloat($(input).val()) || 0);
                        }, 0);
                        if (Math.abs(plannedBudget - quarterSum) > 0.01) {
                            isValid = false;
                            validateRow(section, index); // Update error state
                        }
                    });
                    console.log(`Table ${section} validation: ${isValid}`);
                    return isValid;
                }

                function addRow(section, parentIndex = null, depth = 0) {
                    // Prevent adding row if table has invalid planned_budget sums
                    if (!isTableValid(section)) {
                        $("#error-message").removeClass("hidden");
                        $("#error-text").text(
                            "Cannot add row: Please ensure all rows have Planned Budget equal to the sum of Q1 + Q2 + Q3 + Q4."
                        );
                        console.warn(`Cannot add row in ${section}: Invalid planned budget sums`);
                        return;
                    }
                    if (depth > 2) {
                        console.warn(`Cannot add row in ${section}: Maximum depth (2) reached`);
                        return;
                    }
                    const type = section === 'capital' ? 'capital' : 'recurrent';
                    const index = type === 'capital' ? capitalIndex++ : recurrentIndex++;
                    const $tbody = $(`#${section}-tbody`);
                    if (!$tbody.length) {
                        console.error(`tbody for ${section} not found`);
                        return;
                    }
                    let hiddenParentInput = '';
                    if (parentIndex !== null) {
                        hiddenParentInput =
                            `<input type="hidden" name="${type}[${index}][parent_id]" value="${parentIndex}">`;
                    }
                    const html = `
                        <tr class="projectActivity-row" data-depth="${depth}" data-index="${index}" ${parentIndex !== null ? `data-parent="${parentIndex}"` : ''}>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200"></td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                ${hiddenParentInput}
                                <input name="${type}[${index}][program]" type="text" class="w-full border-0 p-1 tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][total_budget]" type="number" step="0.01" class="w-full border-0 p-1 text-right total-budget-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][total_expense]" type="number" step="0.01" class="w-full border-0 p-1 text-right expenses-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][planned_budget]" type="number" step="0.01" class="w-full border-0 p-1 text-right planned-budget-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][q1]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][q2]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][q3]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                <input name="${type}[${index}][q4]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input tooltip-error" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                <div class="flex space-x-2 justify-center">
                                    ${depth < 2 ? `<span class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>` : ''}
                                    ${(depth > 0 || index > 1) ? `
                                                                                        <span class="remove-row cursor-pointer text-2xl text-red-500">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                            </svg>
                                                                                        </span>
                                                                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `;
                    if (parentIndex !== null) {
                        const $parentRow = $tbody.find(`tr[data-index="${parentIndex}"]`);
                        if (!$parentRow.length) {
                            console.error(`Parent row ${parentIndex} not found for insertion in ${section}`);
                            return;
                        }
                        // Find all rows in the parent's subtree (direct children and their descendants)
                        const subTreeRows = [];
                        const collectSubTree = (index) => {
                            const $children = $tbody.find(`tr[data-parent="${index}"]`);
                            $children.each(function() {
                                const childIndex = $(this).data('index');
                                subTreeRows.push($(this));
                                collectSubTree(childIndex);
                            });
                        };
                        collectSubTree(parentIndex);
                        // Insert after the last row in the subtree, or after the parent if no children
                        const $lastRow = subTreeRows.length ? subTreeRows[subTreeRows.length - 1] : $parentRow;
                        console.log(
                            `Inserting row ${index} in ${section} after row with index ${$lastRow.data('index')} (depth: ${depth}, parent: ${parentIndex})`
                        );
                        $lastRow.after(html);
                    } else {
                        console.log(`Appending row ${index} to ${section} tbody (depth: ${depth}, no parent)`);
                        $tbody.append(html);
                    }
                    const $newRow = $tbody.find(`tr[data-index="${index}"]`);
                    console.log(`New row ${index} added to DOM in ${section}, depth: ${depth}, parent: ${parentIndex}`);
                    updateRowNumbers(section);
                    updateTotals();
                    validateRow(section, index);
                    initializeTooltips($newRow.find('.tooltip-error'));
                    if (parentIndex !== null) {
                        restrictChildInputs(section, index, parentIndex);
                        validateParentRow(section, parentIndex);
                    }
                }

                function addSubRow($row) {
                    const section = $row.closest('table').attr('id').replace('-activities', '');
                    const parentIndex = $row.data('index');
                    const depth = $row.data('depth') + 1;
                    if (depth > 2) {
                        console.warn(
                            `Cannot add sub-row in ${section}: Maximum depth (2) reached for parent ${parentIndex}`);
                        return;
                    }
                    if (!isTableValid(section)) {
                        $("#error-message").removeClass("hidden");
                        $("#error-text").text(
                            "Cannot add sub-row: Please ensure all rows have Planned Budget equal to the sum of Q1 + Q2 + Q3 + Q4."
                        );
                        console.warn(`Cannot add sub-row in ${section}: Invalid planned budget sums`);
                        return;
                    }
                    console.log(`Adding sub-row in ${section} under parent ${parentIndex} at depth ${depth}`);
                    addRow(section, parentIndex, depth);
                }

                // Ensure event delegation for dynamically added rows
                $(document).off('click', '.add-sub-row').on('click', '.add-sub-row', function(e) {
                    e.preventDefault();
                    console.log('Add Sub Row clicked');
                    addSubRow($(this).closest('tr'));
                });

                $('#add-capital-row').on('click', function() {
                    console.log('Add Capital Row clicked');
                    addRow('capital');
                });

                $('#add-recurrent-row').on('click', function() {
                    console.log('Add Recurrent Row clicked');
                    addRow('recurrent');
                });

                $(document).on('click', '.remove-row', function() {
                    const $row = $(this).closest('tr');
                    const section = $row.closest('table').attr('id').replace('-activities', '');
                    const parentIndex = $row.data('parent');
                    const index = $row.data('index');
                    // Remove this row and its sub-rows
                    $(`tr[data-parent="${index}"]`).remove();
                    $row.remove();
                    console.log(`Removed row ${index} in ${section}, parent: ${parentIndex}`);
                    updateRowNumbers(section);
                    updateTotals();
                    validateParentRows(section);
                    if (parentIndex) {
                        validateParentRow(section, parentIndex);
                        // After removal, update parents up the chain for all fields
                        updateParentAfterRemoval(section, parentIndex);
                    }
                });

                function updateParentAfterRemoval(section, parentIndex) {
                    const fields = ['total_budget', 'total_expense', 'planned_budget', 'q1', 'q2', 'q3', 'q4'];
                    fields.forEach(field => updateParentField(section, parentIndex, field));
                }

                function updateRowNumbers(section) {
                    const $rows = $(`#${section}-activities tbody tr`);
                    let topLevelCount = 0;
                    let levelOneCounts = {};
                    let levelTwoCounts = {};

                    $rows.each(function() {
                        const $row = $(this);
                        const depth = $row.data('depth');
                        const parentIndex = $row.data('parent');
                        let number = '';

                        if (depth === 0) {
                            topLevelCount++;
                            number = topLevelCount.toString();
                            levelOneCounts[topLevelCount] = 0;
                        } else if (depth === 1) {
                            const parentRow = $rows.filter(`[data-index="${parentIndex}"]`);
                            const parentNumber = parentRow.find('td:first').text();
                            levelOneCounts[parentNumber] = (levelOneCounts[parentNumber] || 0) + 1;
                            number = `${parentNumber}.${levelOneCounts[parentNumber]}`;
                            levelTwoCounts[number] = 0;
                        } else if (depth === 2) {
                            const parentRow = $rows.filter(`[data-index="${parentIndex}"]`);
                            const parentNumber = parentRow.find('td:first').text();
                            levelTwoCounts[parentNumber] = (levelTwoCounts[parentNumber] || 0) + 1;
                            number = `${parentNumber}.${levelTwoCounts[parentNumber]}`;
                        }
                        $row.find('td:first').text(number);
                    });
                    console.log(`Updated row numbers for ${section}:`, $rows.map((i, el) => $(el).find('td:first')
                        .text()).get());
                }

                function updateTotals() {
                    let capitalTotal = 0;
                    $('#capital-activities .projectActivity-row[data-depth="0"] .total-budget-input').each(function() {
                        const value = parseFloat($(this).val()) || 0;
                        capitalTotal += value;
                    });
                    $('#capital-total').text(capitalTotal.toFixed(2));

                    let recurrentTotal = 0;
                    $('#recurrent-activities .projectActivity-row[data-depth="0"] .total-budget-input').each(
                        function() {
                            const value = parseFloat($(this).val()) || 0;
                            recurrentTotal += value;
                        });
                    $('#recurrent-total').text(recurrentTotal.toFixed(2));
                }

                function updateParentField(section, rowIndex, field) {
                    const $row = $(`#${section}-activities tr[data-index="${rowIndex}"]`);
                    const parentIndex = $row.data('parent');
                    if (!parentIndex) {
                        console.log(`No parent for row ${rowIndex} in ${section}`);
                        return;
                    }
                    const $parentRow = $(`#${section}-activities tr[data-index="${parentIndex}"]`);
                    if (!$parentRow.length) {
                        console.error(`Parent row ${parentIndex} not found in ${section}`);
                        return;
                    }
                    const $childRows = $(`#${section}-activities tr[data-parent="${parentIndex}"]`);
                    if ($childRows.length === 0) {
                        console.log(`No child rows for parent ${parentIndex} in ${section}`);
                        return;
                    }
                    let childSum = 0;
                    let selector;
                    switch (field) {
                        case 'total_budget':
                            selector = '.total-budget-input';
                            break;
                        case 'total_expense':
                            selector = '.expenses-input';
                            break;
                        case 'planned_budget':
                            selector = '.planned-budget-input';
                            break;
                        case 'q1':
                            selector = 'input[name*="[q1]"].quarter-input';
                            break;
                        case 'q2':
                            selector = 'input[name*="[q2]"].quarter-input';
                            break;
                        case 'q3':
                            selector = 'input[name*="[q3]"].quarter-input';
                            break;
                        case 'q4':
                            selector = 'input[name*="[q4]"].quarter-input';
                            break;
                    }
                    $childRows.each(function() {
                        const $childInput = $(this).find(selector);
                        const childValue = parseFloat($childInput.val()) || 0;
                        childSum += childValue;
                    });
                    const $parentInput = $parentRow.find(selector);
                    $parentInput.val(childSum.toFixed(2));
                    console.log(`Updated parent ${parentIndex} ${field} to ${childSum.toFixed(2)} in ${section}`);
                    // Recurse to grandparent
                    updateParentField(section, parentIndex, field);
                }

                function validateRow(section, index) {
                    const $row = $(`#${section}-activities tr[data-index="${index}"]`);
                    const $plannedBudget = $row.find('.planned-budget-input');
                    const $quarters = $row.find('.quarter-input');
                    let quarterSum = 0;
                    $quarters.each(function() {
                        quarterSum += parseFloat($(this).val()) || 0;
                    });
                    const plannedBudget = parseFloat($plannedBudget.val()) || 0;
                    if (Math.abs(quarterSum - plannedBudget) > 0.01) {
                        $plannedBudget.addClass('error-border');
                        $quarters.addClass('error-border');
                        updateTooltip($plannedBudget,
                            `Quarters sum (${quarterSum.toFixed(2)}) does not match planned budget (${plannedBudget.toFixed(2)})`
                        );
                        $quarters.each(function() {
                            updateTooltip($(this),
                                `Quarters sum (${quarterSum.toFixed(2)}) does not match planned budget (${plannedBudget.toFixed(2)})`
                            );
                        });
                    } else {
                        $plannedBudget.removeClass('error-border');
                        $quarters.removeClass('error-border');
                        updateTooltip($plannedBudget, '');
                        $quarters.each(function() {
                            updateTooltip($(this), '');
                        });
                    }
                    console.log(
                        `Validated row ${index} in ${section}, quarters sum: ${quarterSum}, planned: ${plannedBudget}`
                    );
                }

                function getFieldFromInput($input) {
                    const name = $input.attr('name');
                    if (name.includes('[total_budget]')) return 'total_budget';
                    if (name.includes('[total_expense]')) return 'total_expense';
                    if (name.includes('[planned_budget]')) return 'planned_budget';
                    if (name.includes('[q1]')) return 'q1';
                    if (name.includes('[q2]')) return 'q2';
                    if (name.includes('[q3]')) return 'q3';
                    if (name.includes('[q4]')) return 'q4';
                    return null;
                }

                function validateParentRow(section, parentIndex, $changedRow = null) {
                    if (!parentIndex) {
                        console.log(`No parent for row in ${section}`);
                        return;
                    }
                    const $parentRow = $(`#${section}-activities tr[data-index="${parentIndex}"]`);
                    if (!$parentRow.length) {
                        console.error(`Parent row with index ${parentIndex} not found in ${section}`);
                        return;
                    }
                    const $childRows = $(`#${section}-activities tr[data-parent="${parentIndex}"]`);
                    if ($childRows.length === 0) {
                        console.log(`No child rows found for parent index ${parentIndex} in ${section}`);
                        return;
                    }
                    console.log(
                        `Validating parent ${$parentRow.find('td:first').text()} in ${section} with ${$childRows.length} children`
                    );

                    const childInputs = {
                        'total_budget': '.total-budget-input',
                        'total_expense': '.expenses-input',
                        'planned_budget': '.planned-budget-input',
                        'q1': 'input[name*="[q1]"].quarter-input',
                        'q2': 'input[name*="[q2]"].quarter-input',
                        'q3': 'input[name*="[q3]"].quarter-input',
                        'q4': 'input[name*="[q4]"].quarter-input'
                    };

                    for (const [field, selector] of Object.entries(childInputs)) {
                        const $parentInput = $parentRow.find(selector);
                        if ($parentInput.length === 0) {
                            console.error(`Parent input for ${field} not found in row ${parentIndex}`);
                            continue;
                        }
                        let childSum = 0;
                        $childRows.each(function() {
                            const $childInput = $(this).find(selector);
                            if ($childInput.length === 0) {
                                console.error(
                                    `Child input for ${field} not found in row ${$(this).find('td:first').text()}`
                                );
                                return;
                            }
                            const childValue = parseFloat($childInput.val()) || 0;
                            childSum += childValue;
                            console.log(`Child ${$(this).find('td:first').text()} ${field}: ${childValue}`);
                        });
                        const parentValue = parseFloat($parentInput.val()) || 0;
                        console.log(
                            `Parent ${$parentRow.find('td:first').text()} ${field}: ${parentValue}, Child Sum: ${childSum}`
                        );
                        if (Math.abs(childSum - parentValue) > 0.01) {
                            $parentInput.addClass('error-border');
                            $childRows.find(selector).addClass('error-border');
                            updateTooltip($parentInput,
                                `Parent ${field} (${parentValue.toFixed(2)}) must equal sum of children (${childSum.toFixed(2)})`
                            );
                            $childRows.each(function() {
                                const $childInput = $(this).find(selector);
                                updateTooltip($childInput,
                                    `Parent ${field} (${parentValue.toFixed(2)}) must equal sum of children (${childSum.toFixed(2)})`
                                );
                            });
                        } else {
                            $parentInput.removeClass('error-border');
                            $childRows.find(selector).removeClass('error-border');
                            updateTooltip($parentInput, '');
                            $childRows.each(function() {
                                const $childInput = $(this).find(selector);
                                updateTooltip($childInput, '');
                            });
                        }
                    }

                    // Validate grandparent if parent is a sub-row
                    validateParentRow(section, $parentRow.data('parent'), $changedRow);
                }

                function validateParentRows(section) {
                    const $rows = $(`#${section}-activities tr[data-parent]`);
                    const parentIndexes = new Set();
                    $rows.each(function() {
                        parentIndexes.add($(this).data('parent'));
                    });
                    console.log(`Validating ${parentIndexes.size} parent rows in ${section}`);
                    parentIndexes.forEach(parentIndex => validateParentRow(section, parentIndex));
                }

                function initializeTooltips($elements) {
                    $elements.each(function() {
                        if (!tippyInstances.has(this)) {
                            tippyInstances.set(this, tippy(this, {
                                content: '',
                                trigger: 'manual',
                                placement: 'top',
                                arrow: true,
                                duration: [200, 0]
                            }));
                        }
                    });
                }

                function updateTooltip($element, message) {
                    const tippyInstance = tippyInstances.get($element[0]);
                    if (tippyInstance) {
                        if (message) {
                            tippyInstance.setContent(message);
                            tippyInstance.show();
                        } else {
                            tippyInstance.hide();
                        }
                    }
                }

                function restrictChildInputs(section, childIndex, parentIndex) {
                    const $childRow = $(`#${section}-activities tr[data-index="${childIndex}"]`);
                    const $parentRow = $(`#${section}-activities tr[data-index="${parentIndex}"]`);
                    const childInputs = {
                        'total_budget': '.total-budget-input',
                        'total_expense': '.expenses-input',
                        'planned_budget': '.planned-budget-input',
                        'q1': 'input[name*="[q1]"].quarter-input',
                        'q2': 'input[name*="[q2]"].quarter-input',
                        'q3': 'input[name*="[q3]"].quarter-input',
                        'q4': 'input[name*="[q4]"].quarter-input'
                    };

                    $childRow.find('.tooltip-error').on('input', function() {
                        const $input = $(this);
                        const field = getFieldFromInput($input);
                        if (!field) return;

                        const $parentInput = $parentRow.find(childInputs[field]);
                        const parentValue = parseFloat($parentInput.val()) || 0;
                        let childValue = parseFloat($input.val()) || 0;

                        if (childValue > parentValue && parentValue !== 0) {
                            childValue = parentValue;
                            $input.val(childValue.toFixed(2));
                            $input.addClass('error-border');
                            updateTooltip($input,
                                `Value (${childValue.toFixed(2)}) cannot exceed parent value (${parentValue.toFixed(2)})`
                            );
                            updateTooltip($parentInput,
                                `Child value (${childValue.toFixed(2)}) cannot exceed parent value (${parentValue.toFixed(2)})`
                            );
                        } else {
                            $input.removeClass('error-border');
                            updateTooltip($input, '');
                            updateTooltip($parentInput, '');
                        }

                        validateParentRow(section, parentIndex);
                    });
                }

                $(document).on('input', '.total-budget-input, .expenses-input, .planned-budget-input, .quarter-input',
                    function() {
                        const $input = $(this);
                        const $row = $input.closest('tr');
                        const section = $row.closest('table').attr('id').replace('-activities', '');
                        const index = $row.data('index');
                        const field = getFieldFromInput($input);
                        console.log(`Input changed in ${section} row ${index}:`, $input.attr('name'), $input
                            .val());
                        if ($input.hasClass('quarter-input')) {
                            // Auto-update planned_budget to sum of quarters
                            const quarters = $row.find('.quarter-input');
                            let sum = 0;
                            quarters.each(function() {
                                sum += parseFloat($(this).val()) || 0;
                            });
                            $row.find('.planned-budget-input').val(sum.toFixed(2));
                            // Trigger input on planned_budget to propagate if needed
                            $row.find('.planned-budget-input').trigger('input');
                        }
                        validateRow(section, index);
                        validateParentRows(section); // Validate all parents to ensure recursive sum checks
                        // If this row has a parent, update the parent field and propagate up
                        if ($row.data('depth') > 0 && field) {
                            updateParentField(section, index, field);
                        }
                        updateTotals();
                    });

                // Initialize tooltips for existing inputs
                initializeTooltips($('.tooltip-error'));

                // Restrict existing child inputs
                $('.projectActivity-row[data-parent]').each(function() {
                    const $row = $(this);
                    const section = $row.closest('table').attr('id').replace('-activities', '');
                    const index = $row.data('index');
                    const parentIndex = $row.data('parent');
                    restrictChildInputs(section, index, parentIndex);
                });

                // Form submission handling
                const $form = $('#projectActivity-form');
                const $submitButton = $('#submit-button');

                $form.on('submit', function(e) {
                    e.preventDefault();
                    if ($submitButton.prop('disabled')) return;

                    let hasErrors = false;
                    ['capital', 'recurrent'].forEach(section => {
                        $(`#${section}-activities .projectActivity-row`).each(function() {
                            const $row = $(this);
                            const index = $row.data('index');
                            const $inputs = $row.find(
                                'input[name*="[program]"], input[name*="[total_budget]"], input[name*="[total_expense]"], input[name*="[planned_budget]"], input[name*="[q1]"], input[name*="[q2]"], input[name*="[q3]"], input[name*="[q4]"]'
                            );
                            $inputs.each(function() {
                                const $input = $(this);
                                const value = $input.val();
                                const isNumericField = $input.hasClass(
                                    'total-budget-input') || $input.hasClass(
                                    'expenses-input') || $input.hasClass(
                                    'planned-budget-input') || $input.hasClass(
                                    'quarter-input');

                                if (!value || (isNumericField && (isNaN(parseFloat(
                                        value)) || parseFloat(value) < 0))) {
                                    $input.addClass('error-border');
                                    updateTooltip($input, isNumericField ?
                                        'Please enter a valid non-negative number' :
                                        'This field is required');
                                    hasErrors = true;
                                } else {
                                    $input.removeClass('error-border');
                                    updateTooltip($input, '');
                                }
                            });
                            validateRow(section, index);
                            if ($row.find('.error-border').length > 0) {
                                hasErrors = true;
                            }
                        });
                        validateParentRows(section);
                        if ($(`#${section}-activities .error-border`).length > 0) {
                            hasErrors = true;
                        }
                    });

                    // if (!$('#project_id').val()) {
                    //     $('#project_id').addClass('error-border');
                    //     hasErrors = true;
                    //     $("#error-message").removeClass("hidden");
                    //     $("#error-text").text("Please select a project.");
                    // }
                    // if (!$('#fiscal_year_id').val()) {
                    //     $('#fiscal_year_id').addClass('error-border');
                    //     hasErrors = true;
                    //     $("#error-message").removeClass("hidden");
                    //     $("#error-text").text("Please select a fiscal year.");
                    // }

                    // if (hasErrors) {
                    //     $("#error-message").removeClass("hidden");
                    //     $("#error-text").text("Please correct the validation errors before submitting.");
                    //     return;
                    // }

                    $submitButton
                        .prop('disabled', true)
                        .addClass('opacity-50 cursor-not-allowed')
                        .text('{{ trans('global.saving') }}...');

                    // The hidden parent_id inputs are now added at row creation, so no need to add them here anymore
                    // But keep this for any potential edge cases or existing rows without it
                    $('tr[data-parent]').each(function() {
                        const $row = $(this);
                        if ($row.find('input[name$="[parent_id]"]').length === 0) {
                            const parentIndex = $row.data('parent');
                            const type = $row.closest('table').attr('id').replace('-activities', '');
                            $row.find('td:nth-child(2)').append(
                                `<input type="hidden" name="${type}[${$row.data('index')}][parent_id]" value="${parentIndex}">`
                            );
                        }
                    });

                    $.ajax({
                        url: '{{ route('admin.projectActivity.update', [$project->id, $fiscalYear->id]) }}',
                        method: 'POST',
                        data: new FormData($form[0]),
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        success: function(response) {
                            window.location.href = '{{ route('admin.projectActivity.index') }}';
                        },
                        error: function(xhr) {
                            $submitButton
                                .prop('disabled', false)
                                .removeClass('opacity-50 cursor-not-allowed')
                                .text('{{ trans('global.save') }}');
                            let errorMessage = xhr.responseJSON?.message ||
                                "Failed to update activities.";
                            if (xhr.responseJSON?.errors) {
                                const errors = xhr.responseJSON.errors;
                                let errorText = errorMessage + ":<br>";
                                $('input.tooltip-error').removeClass('error-border');
                                for (const [index, messages] of Object.entries(errors)) {
                                    const section = messages.some(msg => msg.includes('capital')) ?
                                        'capital' : 'recurrent';
                                    const $row = $(
                                        `#${section}-activities tr[data-index="${index}"]`);
                                    messages.forEach(msg => {
                                        errorText +=
                                            `Row ${parseInt(index)}: ${msg}<br>`;
                                        const fieldMatch = msg.match(
                                            /(program|total_budget|total_expense|planned_budget|q[1-4])/i
                                        );
                                        if (fieldMatch) {
                                            const field = fieldMatch[1];
                                            $row.find(`input[name*="[${field}]"]`).addClass(
                                                'error-border');
                                            updateTooltip($row.find(
                                                `input[name*="[${field}]"]`), msg);
                                        }
                                    });
                                }
                                $("#error-text").html(errorText);
                            } else {
                                $("#error-text").text(errorMessage);
                            }
                            $("#error-message").removeClass("hidden");
                        }
                    });
                });

                $("#close-error").on('click', function() {
                    $("#error-message").addClass("hidden");
                    $("#error-text").text("");
                    $('.tooltip-error').removeClass('error-border');
                    $('.tooltip-error').each(function() {
                        updateTooltip($(this), '');
                    });
                });

                updateRowNumbers('capital');
                updateRowNumbers('recurrent');
                updateTotals();
                validateParentRows('capital');
                validateParentRows('recurrent');
            });
        </script>

        <script>
            // Auto-reload on project change: Append project_id to URL and reload
            document.addEventListener('DOMContentLoaded', function() {
                const projectSelect = document.getElementById('project_id');
                if (projectSelect) {
                    projectSelect.addEventListener('change', function() {
                        const url = new URL(window.location);
                        if (this.value) {
                            url.searchParams.set('project_id', this.value);
                        } else {
                            url.searchParams.delete('project_id');
                        }
                        window.location.href = url.toString();
                    });
                }

                // Optional: Same for fiscal year if needed
                const fiscalSelect = document.getElementById('fiscal_year_id');
                if (fiscalSelect) {
                    fiscalSelect.addEventListener('change', function() {
                        // If fiscal year change also needs reload, implement similarly
                        // const url = new URL(window.location);
                        // url.searchParams.set('fiscal_year_id', this.value);
                        // window.location.href = url.toString();
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>
