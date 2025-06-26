<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Contract') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create New Contract') }}</p>
    </div>

    {{-- The outer flex-col md:flex-row gap-6 and flex-1 are no longer needed here as the form itself will manage columns --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden p-6">
        <form class="w-full" action="{{ route('admin.contract.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div
                    class="mb-6 p-4 bg-red-400 text-white border border-red-500 rounded-lg dark:bg-red-900 dark:border-red-700">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="error-message"
                class="mb-6 hidden bg-gray-100 border border-gray-400 text-gray-800 px-4 py-3 rounded-lg relative dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                <span id="error-text"></span>
                <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-gray-500 dark:text-gray-400" role="button"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path
                            d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                    </svg>
                </button>
            </div>

            {{-- New: Main Grid for Two Columns --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Left Column: Contract Details --}}
                <div>
                    <div
                        class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 h-full">
                        {{-- Added h-full to make it fill the height --}}
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Contract Details') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-6"> {{-- Changed to grid-cols-1 to make inner items stack --}}
                            <div class="col-span-full">
                                <x-forms.select label="Directorate" name="directorate_id" id="directorate_id"
                                    :options="collect($directorates)
                                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                        ->values()
                                        ->all()" :selected="old('directorate_id', '')" placeholder="Select directorate"
                                    :error="$errors->first('directorate_id')" class="js-single-select" /> {{-- Added js-single-select class --}}
                            </div>

                            <div class="col-span-full">
                                <x-forms.select label="Project" name="project_id" id="project_id" :options="[]"
                                    :selected="old('project_id', '')" placeholder="Select project" :error="$errors->first('project_id')"
                                    class="js-single-select" /> {{-- Added js-single-select class --}}
                            </div>

                            <div class="col-span-full">
                                <x-forms.input label="Name" name="title" type="text" :value="old('title')"
                                    placeholder="Enter contract name" :error="$errors->first('title')" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.text-area label="Description" name="description" :value="old('description')"
                                    placeholder="Enter contract description" :error="$errors->first('description')" rows="4" />
                            </div>

                            <div>
                                <x-forms.input label="Contractor" name="contractor" type="text" :value="old('contractor')"
                                    placeholder="Enter contractor name" :error="$errors->first('contractor')" />
                            </div>

                            <div>
                                <x-forms.input label="Amount" name="contract_amount" type="number" step="0.01"
                                    :value="old('contract_amount')" placeholder="0.00" :error="$errors->first('contract_amount')" />
                            </div>

                            <div class="col-span-full">
                                <x-forms.input label="Variation Amount" name="contract_variation_amount" type="number"
                                    step="0.01" :value="old('contract_variation_amount')" placeholder="0.00" :error="$errors->first('contract_variation_amount')" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Other Sections --}}
                <div class="space-y-6"> {{-- Use space-y to add vertical gap between stacked sections --}}
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Status & Priority') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-forms.select label="Status" name="status_id" id="status_id" :options="collect($statuses)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()"
                                    :selected="old('status_id', '')" placeholder="Select status" :error="$errors->first('status_id')"
                                    class="js-single-select" /> {{-- Added js-single-select class --}}
                            </div>

                            <div>
                                <x-forms.select label="Priority" name="priority_id" id="priority_id" :options="collect($priorities)
                                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                                    ->values()
                                    ->all()"
                                    :selected="old('priority_id', '')" placeholder="Select priority" :error="$errors->first('priority_id')"
                                    class="js-single-select" /> {{-- Added js-single-select class --}}
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Dates & Progress') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-forms.date-input label="Agreement Date" name="contract_agreement_date"
                                    :value="old('contract_agreement_date')" :error="$errors->first('contract_agreement_date')" />
                            </div>

                            <div>
                                <x-forms.date-input label="Effective Date" name="agreement_effective_date"
                                    :value="old('agreement_effective_date')" :error="$errors->first('agreement_effective_date')" />
                            </div>

                            <div>
                                <x-forms.date-input label="Completion Date" name="agreement_completion_date"
                                    :value="old('agreement_completion_date')" :error="$errors->first('agreement_completion_date')" />
                            </div>

                            <div class="col-span-full md:col-span-1">
                                <x-forms.input label="Contract Period (days)" name="initial_contract_period"
                                    type="number" :value="old('initial_contract_period')" placeholder="0" :error="$errors->first('initial_contract_period')" />
                            </div>

                            <div class="col-span-full md:col-span-1">
                                <x-forms.input label="Progress (%)" name="progress" type="number" step="0.01"
                                    min="0" max="100" :value="old('progress')" placeholder="0.00"
                                    :error="$errors->first('progress')" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-buttons.primary>{{ __('Save') }}</x-buttons.primary>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function waitForJQuery() {
                if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.jquery !== 'undefined') {
                    console.log('jQuery version in contract create:', window.jQuery.fn.jquery);
                    initializeScript(window.jQuery);
                } else {
                    console.log('Waiting for jQuery...');
                    setTimeout(waitForJQuery, 50);
                }
            })();

            function initializeScript(jQuery) {
                jQuery(document).ready(function() {
                    const projectSelect = jQuery('.js-single-select[data-name="project_id"]');
                    const errorMessage = jQuery('#error-message');
                    const errorText = jQuery('#error-text');
                    const directorateInput = jQuery('input[name="directorate_id"].js-hidden-input');

                    console.log('Directorate input found:', directorateInput.length);
                    console.log('Project select found:', projectSelect.length);

                    // Handle directorate change to fetch projects
                    directorateInput.on('change', function() {
                        const directorateId = jQuery(this).val();
                        console.log('Directorate changed:', directorateId);

                        if (directorateId) {
                            projectSelect.find('.js-options-container').html(
                                '<div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">Loading projects...</div>'
                            );
                            projectSelect.find('input.js-hidden-input').val('').trigger('change');

                            jQuery.ajax({
                                url: '/admin/contracts/projects/' + encodeURIComponent(directorateId),
                                type: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    const options = Array.isArray(data) ? data.map(project => ({
                                        value: String(project.value),
                                        label: String(project.label ||
                                            'Untitled Project')
                                    })).filter(opt => opt.value && opt.label && opt.label !==
                                        'undefined') : [];
                                    const selected = @json(old('project_id', '')) && options.some(
                                            opt => String(opt.value) === String(
                                                @json(old('project_id', '')))) ?
                                        String(@json(old('project_id', ''))) : '';
                                    console.log('Triggering options-updated for project select:',
                                        projectSelect.attr('id'));
                                    projectSelect
                                        .attr('data-options', JSON.stringify(options))
                                        .attr('data-selected', JSON.stringify(selected))
                                        .trigger('options-updated', {
                                            options,
                                            selected
                                        });
                                    console.log('Projects fetched:', options.slice(0, 3),
                                        'Selected:', selected);
                                },
                                error: function(xhr) {
                                    console.error('AJAX error:', xhr.status, xhr.statusText, xhr
                                        .responseJSON);
                                    projectSelect
                                        .attr('data-options', JSON.stringify([]))
                                        .attr('data-selected', JSON.stringify(''))
                                        .trigger('options-updated', {
                                            options: [],
                                            selected: ''
                                        });
                                    errorMessage.removeClass('hidden');
                                    errorText.text('Failed to load projects: ' + (xhr.responseJSON
                                        ?.message || 'AJAX error'));
                                }
                            });
                        } else {
                            console.log('Triggering options-updated for project select:', projectSelect.attr(
                                'id'));
                            projectSelect
                                .attr('data-options', JSON.stringify([]))
                                .attr('data-selected', JSON.stringify(''))
                                .trigger('options-updated', {
                                    options: [],
                                    selected: ''
                                });
                            console.log('Directorate cleared, projects reset');
                        }
                    });

                    // Handle error message close
                    jQuery('#close-error').on('click', function() {
                        console.log('Close error clicked');
                        errorMessage.addClass('hidden');
                        errorText.text('');
                    });

                    // Trigger initial load
                    console.log('Initial directorate value:', directorateInput.val());
                    directorateInput.trigger('change');
                });
            }
        </script>
    @endpush
</x-layouts.app>
