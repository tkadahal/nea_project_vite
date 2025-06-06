<x-layouts.app>
    <!-- Page Title -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Permission') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update Permission') }}</p>
        </div>

        <a href="{{ route('admin.permission.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300
                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2
                  dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-900">
            Back to Permissions
        </a>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <div class="flex-1">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6 p-6">
                {{-- Form container with max-width and auto margins for centering --}}
                <form class="max-w-3xl mx-auto" action="{{ route('admin.permission.update', $permission->id) }}"
                    method="POST">
                    @csrf
                    @method('PUT')

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
                            {{ __('Permission Information') }}
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full">
                                <x-forms.input label="Title" name="title" type="text" :value="old('title', $permission->title)"
                                    :error="$errors->first('title')" />
                            </div>
                        </div>
                    </div>
                    {{-- End Permission Information Section --}}

                    <div class="mt-8">
                        <x-buttons.primary>{{ __('Update') }}</x-buttons.primary>
                        <a href="{{ route('admin.permission.index') }}"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500 ml-2">
                            {{-- Added ml-2 for spacing --}}
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- General app.js and formDependencies.js --}}
    @vite(['resources/js/app.js', 'resources/js/formDependencies.js'])

    @push('scripts')
        <script>
            // Define waitForJQuery to ensure jQuery and DOM are ready
            function waitForJQuery(callback, retries = 100) {
                if (typeof jQuery !== "undefined" && jQuery.fn.jquery && document.readyState === "complete") {
                    callback();
                } else if (retries > 0) {
                    console.warn("Permission Edit Script: jQuery or DOM not ready, retrying... Retries left:", retries);
                    setTimeout(function() {
                        waitForJQuery(callback, retries - 1);
                    }, 200);
                } else {
                    console.error("Permission Edit Script: Failed to load jQuery or DOM after maximum retries. Aborting.");
                }
            }

            // Main script logic for the Permission Edit form
            waitForJQuery(function() {
                const $ = jQuery;
                console.log("Permission Edit Script: jQuery ready.");

                // Close global error message (if it exists on the page)
                $("#close-error").on("click", function() {
                    console.log("Permission Edit Script: Close error button clicked.");
                    $("#error-message").addClass("hidden");
                    $("#error-text").text("");
                });
            });
        </script>
    @endpush
</x-layouts.app>
