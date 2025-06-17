<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            {{ __('Task Gantt Chart') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Visualize your tasks timeline and progress') }}
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-4 mb-4">
        {{-- View Mode Buttons --}}
        <div class="flex space-x-0 items-center bg-gray-200 dark:bg-gray-600 rounded-lg overflow-hidden">
            <button onclick="setViewMode('Quarter Day')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200"
                style="border-right: 1px solid #d1d5db; border-left: none;" id="quarter-day-btn">
                Quarter Day
            </button>
            <button onclick="setViewMode('Half Day')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200"
                style="border-right: 1px solid #d1d5db;" id="half-day-btn">
                Half Day
            </button>
            <button onclick="setViewMode('Day')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200"
                style="border-right: 1px solid #d1d5db;" id="day-btn">
                Day
            </button>
            <button onclick="setViewMode('Week')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200"
                style="border-right: 1px solid #d1d5db;" id="week-btn">
                Week
            </button>
            <button onclick="setViewMode('Month')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200"
                style="border-right: 1px solid #d1d5db;" id="month-btn">
                Month
            </button>
            <button onclick="setViewMode('Year')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200"
                style="border-left: none;" id="year-btn">
                Year
            </button>
        </div>

        {{-- Filters --}}
        <div class="flex items-center gap-4 ml-auto" style="z-index: 10;">
            <div class="min-w-[150px] flex-shrink-0" data-name="directorate_id">
                <x-forms.select label="Directorate" name="directorate_id" :options="collect($availableDirectorates)
                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                    ->values()
                    ->all()" :selected="request()->input('directorate_id') ? (string) request()->input('directorate_id') : null"
                    placeholder="Select a Directorate..." />
            </div>

            <div class="min-w-[150px] flex-shrink-0" data-name="priority">
                <x-forms.select label="Priority" name="priority" :options="collect($priorities)
                    ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                    ->values()
                    ->all()" :selected="request()->input('priority') ? (string) request()->input('priority') : null"
                    placeholder="Select a Priority..." />
            </div>
        </div>
    </div>

    <livewire:task-gantt-chart />

</x-layouts.app>
