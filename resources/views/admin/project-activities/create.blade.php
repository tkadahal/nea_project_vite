<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            {{ trans('global.activity.title') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ trans('global.create') }} {{ trans('global.activity.title_singular') }} for Project: {{ $project->name }}
        </p>
    </div>

    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form id="activity-form" class="w-full" action="{{ route('admin.project-activities.store', $project) }}"
            method="POST" enctype="multipart/form-data">
            @csrf

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
                        {{ trans('global.activity.headers.capital') }} {{ trans('global.activity.title') }}
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
                                        {{ trans('global.activity.fields.programs') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.unit') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.total_quantity') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.total_cost') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.weight_percentage') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                        {{ trans('global.activity.fields.description') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-24">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="capital-tbody">
                                <tr class="activity-row" data-depth="0" data-index="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        1
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="capital[0][programs]" type="text" :value="old('capital.0.programs')"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="capital[0][unit]" type="text" :value="old('capital.0.unit')"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="capital[0][total_quantity]" type="number" step="0.01"
                                            :value="old('capital.0.total_quantity')" class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="capital[0][total_cost]" type="number" step="0.01"
                                            :value="old('capital.0.total_cost')" class="w-full border-0 p-1 total-cost-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="capital[0][weight_percentage]" type="number"
                                            step="0.01" :value="old('capital.0.weight_percentage')" class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.text-area name="capital[0][description]" :value="old('capital.0.description')"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                        <button type="button"
                                            class="add-sub-row bg-blue-500 text-white px-2 py-1 rounded text-sm">Add
                                            Sub-Row</button>
                                        <button type="button"
                                            class="remove-row bg-red-500 text-white px-2 py-1 rounded text-sm">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="add-capital-row" class="mt-4 bg-green-500 text-white px-4 py-2 rounded">
                        Add New Row
                    </button>
                    <div class="mt-4 text-lg font-bold">
                        Total Capital Cost: <span id="capital-total">0.00</span>
                    </div>
                </div>
            </div>

            <!-- Recurrent Expenditure Section -->
            <div class="mb-8">
                <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                        {{ trans('global.activity.headers.recurrent') }} {{ trans('global.activity.title') }}
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
                                        {{ trans('global.activity.fields.programs') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.unit') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.total_quantity') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.total_cost') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-32">
                                        {{ trans('global.activity.fields.weight_percentage') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                        {{ trans('global.activity.fields.description') }}</th>
                                    <th
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-24">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recurrent-tbody">
                                <tr class="activity-row" data-depth="0" data-index="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        1
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="recurrent[0][programs]" type="text" :value="old('recurrent.0.programs')"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="recurrent[0][unit]" type="text" :value="old('recurrent.0.unit')"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="recurrent[0][total_quantity]" type="number"
                                            step="0.01" :value="old('recurrent.0.total_quantity')" class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="recurrent[0][total_cost]" type="number" step="0.01"
                                            :value="old('recurrent.0.total_cost')" class="w-full border-0 p-1 total-cost-input" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.input name="recurrent[0][weight_percentage]" type="number"
                                            step="0.01" :value="old('recurrent.0.weight_percentage')" class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                        <x-forms.text-area name="recurrent[0][description]" :value="old('recurrent.0.description')"
                                            class="w-full border-0 p-1" />
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                        <button type="button"
                                            class="add-sub-row bg-blue-500 text-white px-2 py-1 rounded text-sm">Add
                                            Sub-Row</button>
                                        <button type="button"
                                            class="remove-row bg-red-500 text-white px-2 py-1 rounded text-sm">Remove</button>
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
                        Total Recurrent Cost: <span id="recurrent-total">0.00</span>
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
        <script>
            // Pass project activities to JavaScript
            window.projectActivities = @json($project->projectActivities);

            function waitForJQuery(callback, retries = 50) {
                if (
                    typeof jQuery !== "undefined" &&
                    jQuery.fn.jquery &&
                    document.readyState === "complete"
                ) {
                    callback();
                } else if (retries > 0) {
                    setTimeout(function() {
                        waitForJQuery(callback, retries - 1);
                    }, 100);
                } else {
                    console.error("Failed to load jQuery or DOM after maximum retries.");
                }
            }

            waitForJQuery(function() {
                const $ = jQuery;

                let capitalIndex = 1;
                let recurrentIndex = 1;

                function addRow(section, parentIndex = null, depth = 0) {
                    const type = section === 'capital' ? 'capital' : 'recurrent';
                    const index = type === 'capital' ? capitalIndex++ : recurrentIndex++;
                    const $tbody = $(`#${section}-tbody`);
                    const html = `
                        <tr class="activity-row" data-depth="${depth}" data-index="${index}" ${parentIndex !== null ? `data-parent="${parentIndex}"` : ''}>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                <!-- Number will be updated by updateRowNumbers -->
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                <x-forms.input name="${type}[${index}][programs]" type="text" class="w-full border-0 p-1" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                <x-forms.input name="${type}[${index}][unit]" type="text" class="w-full border-0 p-1" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                <x-forms.input name="${type}[${index}][total_quantity]" type="number" step="0.01" class="w-full border-0 p-1" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                <x-forms.input name="${type}[${index}][total_cost]" type="number" step="0.01" class="w-full border-0 p-1 total-cost-input" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                <x-forms.input name="${type}[${index}][weight_percentage]" type="number" step="0.01" class="w-full border-0 p-1" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">
                                <x-forms.text-area name="${type}[${index}][description]" class="w-full border-0 p-1" />
                            </td>
                            <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center">
                                <button type="button" class="add-sub-row bg-blue-500 text-white px-2 py-1 rounded text-sm">Add Sub-Row</button>
                                <button type="button" class="remove-row bg-red-500 text-white px-2 py-1 rounded text-sm">Remove</button>
                            </td>
                        </tr>
                    `;
                    if (parentIndex !== null) {
                        $tbody.find(`tr[data-index="${parentIndex}"]`).after(html);
                    } else {
                        $tbody.append(html);
                    }
                    updateRowNumbers(section);
                }

                function addSubRow($row) {
                    const section = $row.closest('table').attr('id').replace('-activities', '');
                    const parentIndex = $row.data('index');
                    const depth = $row.data('depth');

                    if (depth >= 1) {
                        alert("Only one level of sub-rows is allowed.");
                        return;
                    }
                    addRow(section, parentIndex, depth + 1);
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
                    $row.remove();
                    updateRowNumbers(section);
                    updateTotals();
                });

                function updateRowNumbers(section) {
                    const $rows = $(`#${section}-activities tbody tr`);
                    let topLevelCount = 0;

                    $rows.each(function() {
                        const $row = $(this);
                        const depth = $row.data('depth');
                        let number = '';

                        if (depth === 0) {
                            topLevelCount++;
                            number = topLevelCount.toString();
                        } else {
                            const parentRow = $rows.filter(`[data-index="${$row.data('parent')}"]`);
                            const parentNumber = parentRow.find('td:first').text();
                            const subRows = parentRow.nextUntil(':not([data-parent="' + $row.data('parent') +
                                '"])').addBack().filter('[data-depth="' + depth + '"]');
                            const subIndex = subRows.index($row) + 1;
                            number = `${parentNumber}.${subIndex}`;
                        }
                        $row.find('td:first').text(number);
                    });
                }

                function updateTotals() {
                    let capitalTotal = 0;
                    $('#capital-activities .total-cost-input').each(function() {
                        capitalTotal += parseFloat($(this).val()) || 0;
                    });
                    $('#capital-total').text(capitalTotal.toFixed(2));

                    let recurrentTotal = 0;
                    $('#recurrent-activities .total-cost-input').each(function() {
                        recurrentTotal += parseFloat($(this).val()) || 0;
                    });
                    $('#recurrent-total').text(recurrentTotal.toFixed(2));
                }

                $(document).on('input', '.total-cost-input', updateTotals);

                // Form submission handling
                const $form = $('#activity-form');
                const $submitButton = $('#submit-button');

                $form.on('submit', function(e) {
                    e.preventDefault();
                    if ($submitButton.prop('disabled')) return;

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
                            window.location.href = '{{ route('admin.project.show', $project) }}';
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

                $("#close-error").on("click", function() {
                    $("#error-message").addClass("hidden");
                    $("#error-text").text("");
                });

                updateRowNumbers('capital');
                updateRowNumbers('recurrent');
                updateTotals();
            });
        </script>
    @endpush
</x-layouts.app>
