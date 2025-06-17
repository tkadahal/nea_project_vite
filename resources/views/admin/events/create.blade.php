<x-layouts.app>
    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Events') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create new event') }}</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6 p-6">
                {{-- Form container with max-width and auto margins for centering --}}
                <form class="max-w-3xl mx-auto" action="{{ route('admin.event.store') }}" method="POST">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Error message display block (hidden by default) --}}
                    <div id="error-message"
                        class="mb-6 hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
                        <span id="error-text"></span>
                        <button type="button" id="close-error" class="absolute top-0 right-0 px-4 py-3">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path
                                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 010 1.698z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Permission Information Section --}}
                    <div
                        class="mb-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h3
                            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                            {{ __('Event Information') }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full">
                                <x-forms.input label="Title" name="title" type="text" :value="old('title')"
                                    :error="$errors->first('title')" />
                            </div>

                            <!-- Description -->
                            <div class="col-span-full">
                                <x-forms.text-area label="Description" name="description" wire:model="description"
                                    :error="$errors->has('description') ? $errors->first('description') : ''" />
                            </div>

                            <!-- Start Time -->
                            <div class="col-span-full">
                                <x-forms.datetime-input label="Start Time" name="start_time" type="datetime-local"
                                    wire:model="start_time" :error="$errors->has('start_time') ? $errors->first('start_time') : ''" />
                            </div>

                            <!-- End Time -->
                            <div class="col-span-full">
                                <x-forms.datetime-input label="End Time" name="end_time" type="datetime-local"
                                    wire:model="end_time" :error="$errors->has('end_time') ? $errors->first('end_time') : ''" />
                            </div>

                            <!-- Reminder Checkbox -->
                            <div class="col-span-full">
                                <x-forms.checkbox label="Set as Reminder" name="is_reminder" wire:model="is_reminder" />
                            </div>
                        </div>
                    </div>
                    {{-- End Permission Information Section --}}

                    <div class="mt-8">
                        <x-buttons.primary>{{ __('Save') }}</x-buttons.primary>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- General app.js and formDependencies.js --}}
    {{-- Assuming these are loaded globally or via a layout blade --}}
    @vite(['resources/js/app.js', 'resources/js/formDependencies.js'])

    @push('scripts')
        <script>
            // Define waitForJQuery to ensure jQuery and DOM are ready
            function waitForJQuery(callback, retries = 100) {
                if (typeof jQuery !== "undefined" && jQuery.fn.jquery && document.readyState === "complete") {
                    callback();
                } else if (retries > 0) {
                    console.warn("Permission Create Script: jQuery or DOM not ready, retrying... Retries left:", retries);
                    setTimeout(function() {
                        waitForJQuery(callback, retries - 1);
                    }, 200);
                } else {
                    console.error("Permission Create Script: Failed to load jQuery or DOM after maximum retries. Aborting.");
                }
            }

            // Main script logic for the Permission Create form
            waitForJQuery(function() {
                const $ = jQuery;
                console.log("Permission Create Script: jQuery ready.");

                // Close global error message (if it exists on the page)
                $("#close-error").on("click", function() {
                    console.log("Permission Create Script: Close error button clicked.");
                    $("#error-message").addClass("hidden");
                    $("#error-text").text("");
                });
            });
        </script>
    @endpush
</x-layouts.app>
