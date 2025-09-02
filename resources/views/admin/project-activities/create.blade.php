<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ trans('global.projectActivity.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.create') }} {{ trans('global.projectActivity.title_singular') }}
        </p>
    </div>

    <div
        class="mb-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Project Selection -->
            <div class="w-full md:w-1/2">
                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ trans('global.project.title_singular') }}
                </label>
                <select id="project_id" name="project_id"
                    class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <option value="">{{ trans('global.pleaseSelect') }}</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">
                            {{ $project->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Fiscal Year Selection -->
            <div class="w-full md:w-1/2">
                <label for="fiscal_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ trans('global.fiscalYear.title') }}
                </label>
                <select id="fiscal_year" name="fiscal_year"
                    class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <option value="">{{ trans('global.pleaseSelect') }}</option>
                    @foreach ($fiscalYears as $fiscalYear)
                        <option value="{{ $fiscalYear->id }}">
                            {{ $fiscalYear->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form id="projectActivity-form" class="w-full" action="{{ route('admin.project-activities.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="project_id" id="form-project-id">
            <input type="hidden" name="fiscal_year" id="form-fiscal-year">

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
                                        #</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                        {{ trans('global.projectActivity.fields.program_id') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Total Budget</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Expenses Till Date</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Planned Budget of this F/Y</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q1</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q2</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q3</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q4</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-24">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="capital-tbody">
                                <tr class="projectActivity-row" data-depth="0" data-index="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        1
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <input name="capital[0][program_id]" type="text"
                                            value="{{ old('capital.0.program_id') }}" class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][total_budget]" type="number" step="0.01"
                                            value="{{ old('capital.0.total_budget') }}"
                                            class="w-full border-0 p-1 text-right total-budget-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][expenses_till_date]" type="number" step="0.01"
                                            value="{{ old('capital.0.expenses_till_date') }}"
                                            class="w-full border-0 p-1 text-right" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][planned_budget_fy]" type="number" step="0.01"
                                            value="{{ old('capital.0.planned_budget_fy') }}"
                                            class="w-full border-0 p-1 text-right planned-budget-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][q1]" type="number" step="0.01"
                                            value="{{ old('capital.0.q1') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][q2]" type="number" step="0.01"
                                            value="{{ old('capital.0.q2') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][q3]" type="number" step="0.01"
                                            value="{{ old('capital.0.q3') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="capital[0][q4]" type="number" step="0.01"
                                            value="{{ old('capital.0.q4') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                        <span class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>
                                    </td>
                                </tr>
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

            <!-- Recurrent Expenditure Section -->
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
                                <tr class="bg-gray-200 dark:bg-gray-600">
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                        #</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                        {{ trans('global.projectActivity.fields.program_id') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Total Budget</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Expenses Till Date</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Planned Budget of this F/Y</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q1</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q2</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q3</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32 text-right">
                                        Q4</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-24">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recurrent-tbody">
                                <tr class="projectActivity-row" data-depth="0" data-index="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        1
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <input name="recurrent[0][program_id]" type="text"
                                            value="{{ old('recurrent.0.program_id') }}"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][total_budget]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.total_budget') }}"
                                            class="w-full border-0 p-1 text-right total-budget-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][expenses_till_date]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.expenses_till_date') }}"
                                            class="w-full border-0 p-1 text-right" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][planned_budget_fy]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.planned_budget_fy') }}"
                                            class="w-full border-0 p-1 text-right planned-budget-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][q1]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.q1') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][q2]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.q2') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][q3]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.q3') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                                        <input name="recurrent[0][q4]" type="number" step="0.01"
                                            value="{{ old('recurrent.0.q4') }}"
                                            class="w-full border-0 p-1 text-right quarter-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                        <span class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>
                                    </td>
                                </tr>
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
        </form>
    </div>

    @push('scripts')
        <style>
            .error-border {
                border: 2px solid red !important;
            }
        </style>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                const $ = jQuery;

                let capitalIndex = 1;
                let recurrentIndex = 1;

                // Update form action based on project selection
                $('#project_id').on('change', function() {
                    const projectId = $(this).val();
                    $('#form-project-id').val(projectId);
                    const actionUrl = projectId ? '{{ route('admin.project-activities.store') }}'.replace(
                        /\/[^\/]+$/, '/' + projectId) : '{{ route('admin.project-activities.store') }}';
                    $('#projectActivity-form').attr('action', actionUrl);
                });

                $('#fiscal_year').on('change', function() {
                    $('#form-fiscal-year').val($(this).val());
                });

                function addRow(section, parentIndex = null, depth = 0) {
                    if (depth > 2) return; // Restrict to max depth of 2
                    const type = section === 'capital' ? 'capital' : 'recurrent';
                    const index = type === 'capital' ? capitalIndex++ : recurrentIndex++;
                    const $tbody = $(`#${section}-tbody`);
                    const html = `
                    <tr class="projectActivity-row" data-depth="${depth}" data-index="${index}" ${parentIndex !== null ? `data-parent="${parentIndex}"` : ''}>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200"></td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                            <input name="${type}[${index}][program_id]" type="text" class="w-full border-0 p-1" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][total_budget]" type="number" step="0.01" class="w-full border-0 p-1 text-right total-budget-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][expenses_till_date]" type="number" step="0.01" class="w-full border-0 p-1 text-right expenses-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][planned_budget_fy]" type="number" step="0.01" class="w-full border-0 p-1 text-right planned-budget-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][q1]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][q2]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][q3]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">
                            <input name="${type}[${index}][q4]" type="number" step="0.01" class="w-full border-0 p-1 text-right quarter-input" />
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                            ${depth < 2 ? `<span class="add-sub-row cursor-pointer text-2xl text-blue-500">+</span>` : ''}
                            ${depth > 0 || index > 0 ? `<button type="button" class="remove-row bg-red-500 text-white px-2 py-1 rounded text-sm">Remove</button>` : ''}
                        </td>
                    </tr>
                `;
                    if (parentIndex !== null) {
                        $tbody.find(`tr[data-index="${parentIndex}"]`).after(html);
                    } else {
                        $tbody.append(html);
                    }
                    updateRowNumbers(section);
                    updateTotals();
                    validateRow(section, index);
                }

                function addSubRow($row) {
                    const section = $row.closest('table').attr('id').replace('-activities', '');
                    const parentIndex = $row.data('index');
                    const depth = $row.data('depth') + 1;
                    if (depth <= 2) {
                        addRow(section, parentIndex, depth);
                    }
                }

                $('#add-capital-row').on('click', function() {
                    addRow('capital');
                });

                $('#add-recurrent-row').on('click', function() {
                    addRow('recurrent');
                });

                $(document).on('click', '.add-sub-row', function() {
                    addSubRow($(this).closest('tr'));
                });

                $(document).on('click', '.remove-row', function() {
                    const $row = $(this).closest('tr');
                    const section = $row.closest('table').attr('id').replace('-activities', '');
                    const parentIndex = $row.data('parent');
                    const index = $row.data('index');
                    // Remove this row and its sub-rows
                    $(`tr[data-parent="${index}"]`).remove();
                    $row.remove();
                    updateRowNumbers(section);
                    updateTotals();
                    validateParentRows(section);
                });

                function updateRowNumbers(section) {
                    const $rows = $(`#${section}-activities tbody tr`);
                    let topLevelCount = 0;
                    let levelOneCounts = {};

                    $rows.each(function() {
                        const $row = $(this);
                        const depth = $row.data('depth');
                        let number = '';

                        if (depth === 0) {
                            topLevelCount++;
                            number = topLevelCount.toString();
                            levelOneCounts[topLevelCount] = 0;
                        } else if (depth === 1) {
                            const parentIndex = $row.data('parent');
                            const parentRow = $rows.filter(`[data-index="${parentIndex}"]`);
                            const parentNumber = parentRow.find('td:first').text();
                            levelOneCounts[parentNumber] = (levelOneCounts[parentNumber] || 0) + 1;
                            number = `${parentNumber}.${levelOneCounts[parentNumber]}`;
                            levelOneCounts[number] = 0;
                        } else if (depth === 2) {
                            const parentIndex = $row.data('parent');
                            const parentRow = $rows.filter(`[data-index="${parentIndex}"]`);
                            const parentNumber = parentRow.find('td:first').text();
                            levelOneCounts[parentNumber] = (levelOneCounts[parentNumber] || 0) + 1;
                            number = `${parentNumber}.${levelOneCounts[parentNumber]}`;
                        }
                        $row.find('td:first').text(number);
                    });
                }

                function updateTotals() {
                    let capitalTotal = 0;
                    $('#capital-activities .total-budget-input').each(function() {
                        const value = parseFloat($(this).val()) || 0;
                        capitalTotal += value;
                    });
                    $('#capital-total').text(capitalTotal.toFixed(2));

                    let recurrentTotal = 0;
                    $('#recurrent-activities .total-budget-input').each(function() {
                        const value = parseFloat($(this).val()) || 0;
                        recurrentTotal += value;
                    });
                    $('#recurrent-total').text(recurrentTotal.toFixed(2));
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
                    } else {
                        $plannedBudget.removeClass('error-border');
                        $quarters.removeClass('error-border');
                    }
                    validateParentRow(section, $row.data('parent'), $row);
                }

                function validateParentRow(section, parentIndex, $changedRow = null) {
                    if (!parentIndex) return;
                    const $parentRow = $(`#${section}-activities tr[data-index="${parentIndex}"]`);
                    if (!$parentRow.length) {
                        console.log(`Parent row with index ${parentIndex} not found in ${section}`);
                        return;
                    }
                    const $childRows = $(`#${section}-activities tr[data-parent="${parentIndex}"]`);
                    if ($childRows.length === 0) {
                        console.log(`No child rows found for parent index ${parentIndex} in ${section}`);
                        return;
                    }
                    const childInputs = {
                        'total_budget': '.total-budget-input',
                        'expenses_till_date': '.expenses-input',
                        'planned_budget_fy': '.planned-budget-input',
                        'q1': '.quarter-input',
                        'q2': '.quarter-input',
                        'q3': '.quarter-input',
                        'q4': '.quarter-input'
                    };

                    for (const [field, selector] of Object.entries(childInputs)) {
                        const $parentInput = $parentRow.find(`${selector}[name*="[${field}]"]`);
                        let childSum = 0;
                        $childRows.each(function() {
                            const $childInput = $(this).find(`${selector}[name*="[${field}]"]`);
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
                            $childRows.find(`${selector}[name*="[${field}]"]`).addClass('error-border');
                        } else {
                            $parentInput.removeClass('error-border');
                            if (!$changedRow || !$changedRow.is($parentInput.closest('tr'))) {
                                $childRows.find(`${selector}[name*="[${field}]"]`).removeClass('error-border');
                            }
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
                    parentIndexes.forEach(parentIndex => validateParentRow(section, parentIndex));
                }

                $(document).on('input', '.total-budget-input, .expenses-input, .planned-budget-input, .quarter-input',
                    function() {
                        const $row = $(this).closest('tr');
                        const section = $row.closest('table').attr('id').replace('-activities', '');
                        const index = $row.data('index');
                        console.log(`Input changed in ${section} row ${index}:`, $(this).attr('name'), $(this)
                        .val());
                        validateRow(section, index);
                        validateParentRow(section, $row.data('parent'), $row);
                    });

                // Form submission handling
                const $form = $('#projectActivity-form');
                const $submitButton = $('#submit-button');

                $form.on('submit', function(e) {
                    e.preventDefault();
                    if ($submitButton.prop('disabled')) return;

                    // Check for validation errors
                    let hasErrors = false;
                    ['capital', 'recurrent'].forEach(section => {
                        $(`#${section}-activities .planned-budget-input`).each(function() {
                            const $row = $(this).closest('tr');
                            const index = $row.data('index');
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

                    if (hasErrors) {
                        $("#error-message").removeClass("hidden");
                        $("#error-text").text("Please correct the budget validation errors before submitting.");
                        return;
                    }

                    $submitButton
                        .prop('disabled', true)
                        .addClass('opacity-50 cursor-not-allowed')
                        .text('{{ trans('global.saving') }}...');

                    // Assign parent_id based on data-index
                    $('tr[data-parent]').each(function() {
                        const $row = $(this);
                        const parentIndex = $row.data('parent');
                        $row.find('input[name$="[parent_id]"]').remove();
                        $row.find('td:first').after(`
                        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1" style="display:none;">
                            <input type="hidden" name="${$row.closest('table').attr('id').replace('-activities', '')}[${$row.data('index')}][parent_id]" value="${parentIndex}">
                        </td>
                    `);
                    });

                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: new FormData($form[0]),
                        processData: false,
                        contentType: false,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        success: function(response) {
                            window.location.href =
                                '{{ route('admin.project.show', ['project' => ':id']) }}'.replace(
                                    ':id', $('#form-project-id').val());
                        },
                        error: function(xhr) {
                            $submitButton
                                .prop('disabled', false)
                                .removeClass('opacity-50 cursor-not-allowed')
                                .text('{{ trans('global.save') }}');
                            $("#error-message").removeClass("hidden");
                            $("#error-text").text(xhr.responseJSON?.message ||
                                "Failed to create activities.");
                        }
                    });
                });

                $("#close-error").on('click', function() {
                    $("#error-message").addClass("hidden");
                    $("#error-text").text("");
                });

                updateRowNumbers('capital');
                updateRowNumbers('recurrent');
                updateTotals();
                validateParentRows('capital');
                validateParentRows('recurrent');
            });
        </script>
    @endpush
</x-layouts.app>
