<x-layouts.app>
    {{-- Page Title --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            {{ __('Project Gantt Chart') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ __('Visualize your tasks timeline and progress') }}
        </p>
    </div>

    {{-- View Mode Buttons and Filters --}}
    <div class="flex flex-col md:flex-row items-center gap-4 mb-4">
        {{-- View Mode Buttons --}}
        <div class="w-full md:w-1/3 bg-gray-200 dark:bg-gray-600 rounded-lg overflow-hidden">
            <div class="flex flex-wrap items-center w-full">
                <button onclick="setViewMode('Quarter Day')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="quarter-day-btn">
                    <span class="md:hidden">QD</span><span class="hidden md:inline">Quarter Day</span>
                </button>
                <button onclick="setViewMode('Half Day')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="half-day-btn">
                    <span class="md:hidden">HD</span><span class="hidden md:inline">Half Day</span>
                </button>
                <button onclick="setViewMode('Day')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="day-btn">
                    <span class="md:hidden">D</span><span class="hidden md:inline">Day</span>
                </button>
                <button onclick="setViewMode('Week')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 bg-blue-500 text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="week-btn">
                    <span class="md:hidden">W</span><span class="hidden md:inline">Week</span>
                </button>
                <button onclick="setViewMode('Month')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="month-btn">
                    <span class="md:hidden">M</span><span class="hidden md:inline">Month</span>
                </button>
                <button onclick="setViewMode('Year')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-l-0 border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="year-btn">
                    <span class="md:hidden">Y</span><span class="hidden md:inline">Year</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="w-full md:w-2/3" style="z-index: 1001;">
            <div class="flex flex-col md:flex-row items-center w-full gap-4">
                <div class="min-w-[150px] flex-shrink-0 w-full md:w-1/2">
                    <x-forms.select label="Directorate" name="directorate_id" :options="collect($availableDirectorates)
                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                        ->values()
                        ->all()" :selected="request()->input('directorate_id') ? (string) request()->input('directorate_id') : null"
                        placeholder="Select a Directorate..." />
                </div>
                <div class="min-w-[150px] flex-shrink-0 w-full md:w-1/2">
                    <x-forms.select label="Priority" name="priority" :options="collect($priorities)
                        ->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])
                        ->values()
                        ->all()" :selected="request()->input('priority') ? (string) request()->input('priority') : null"
                        placeholder="Select a Priority..." />
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow"
        style="width: 100%; overflow-x: auto; min-height: 400px;">
        <div id="gantt-container" class="dark:bg-gray-900" style="width: 100%; overflow-x: auto; min-height: 400px;">
            <svg id="gantt-target" style="width: 100%; height: auto; min-width: 1000px;"></svg>
        </div>
    </div>

    <style>
        *,
        ::after,
        ::before,
        ::backdrop,
        ::file-selector-button {
            z-index: 1;
        }

        #gantt-container {
            overflow-x: auto;
            overflow-y: hidden;
            min-height: 400px;
            width: 100%;
            position: relative;
            /* Ensure child elements are contained */
        }

        #gantt-target {
            width: 100%;
            height: 400px;
            /* Match container min-height */
            min-width: 0;
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                console.log('DOMContentLoaded fired');
                console.log('Gantt library loaded:', typeof Gantt !== 'undefined');
                const initialTasksData = @json($tasks);
                console.log('Initial Tasks Data:', initialTasksData);

                const directorateTitlesMap = @json($availableDirectorates);
                const priorityTitlesMap = @json($priorities);

                let ganttInstance;
                let currentViewMode = 'Week';

                function formatTasksForGantt(tasksToFormat) {
                    return tasksToFormat.map(task => ({
                        id: task.id.toString(),
                        name: task.title,
                        start: task.start,
                        end: task.end,
                        progress: task.progress ?? 0,
                        dependencies: '',
                        custom_class: 'gantt-task',
                        directorate_id: task.directorate_id,
                        directorate_title: task.directorate || directorateTitlesMap[task.directorate_id] ||
                            'N/A',
                        priority_id: task.priority,
                        priority_title: priorityTitlesMap[task.priority] || 'N/A'
                    }));
                }

                function initializeGantt(tasksToRender, viewMode = 'Week') {
                    console.log('Reinitializing Gantt with viewMode:', viewMode, 'tasks:', tasksToRender);
                    const formattedTasks = formatTasksForGantt(tasksToRender);
                    console.log('Formatted tasks:', formattedTasks);

                    const svgContainer = document.querySelector('#gantt-target');
                    if (svgContainer) {
                        svgContainer.innerHTML = '';
                        console.log('Cleared SVG container');
                    }

                    if (ganttInstance) {
                        console.log('Clearing existing Gantt instance');
                        try {
                            ganttInstance.clear();
                        } catch (e) {
                            console.warn('Error clearing Gantt instance:', e);
                        }
                        ganttInstance = null;
                    }

                    let column_width;
                    switch (viewMode) {
                        case 'Quarter Day':
                            column_width = 45 / 4;
                            break;
                        case 'Half Day':
                            column_width = 45 / 2;
                            break;
                        case 'Day':
                            column_width = 45;
                            break;
                        case 'Week':
                            column_width = 45 * 7;
                            break;
                        case 'Month':
                            column_width = 45 * 30;
                            break;
                        case 'Year':
                            column_width = 45 * 365;
                            break;
                        default:
                            column_width = 45;
                    }

                    try {
                        ganttInstance = new Gantt("#gantt-target", formattedTasks, {
                            view_mode: viewMode,
                            upper_header_height: 45,
                            lower_header_height: 30,
                            date_format: 'YYYY-MM-DD',
                            snap_at: '1d',
                            bar_height: 30,
                            bar_corner_radius: 3,
                            padding: 18,
                            infinite_padding: true,
                            container_height: 400, // Match container min-height in pixels
                            popup_on: 'click',
                            column_width: column_width,
                            today_button: true,
                            scroll_to: 'today',
                            view_mode_select: true,
                            custom_popup_html: function(task) {
                                return `
                                <div class="p-3 rounded-lg bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-200 text-sm shadow">
                                    <div class="font-semibold mb-1">${task.name}</div>
                                    <div><strong>Start:</strong> ${task.start}</div>
                                    <div><strong>End:</strong> ${task.end}</div>
                                    <div><strong>Progress:</strong> ${task.progress}%</div>
                                    <div><strong>Directorate:</strong> ${task.directorate_title || 'N/A'}</div>
                                    <div><strong>Priority:</strong> ${task.priority_title || 'N/A'}</div>
                                </div>
                            `;
                            }
                        });
                        console.log('Gantt instance initialized');
                        currentViewMode = viewMode;
                    } catch (e) {
                        console.error('Error initializing Gantt:', e);
                    }

                    document.querySelectorAll('.flex.space-x-0.items-center button').forEach(button => {
                        const isActive = button.textContent.trim() === viewMode;
                        button.classList.toggle('bg-blue-500', isActive);
                        button.classList.toggle('text-white', isActive);
                        button.classList.toggle('hover:bg-blue-600', isActive);
                        button.classList.toggle('dark:bg-blue-600', isActive);
                        button.classList.toggle('dark:hover:bg-blue-700', isActive);
                        button.classList.toggle('bg-transparent', !isActive);
                        button.classList.toggle('text-gray-700', !isActive);
                        button.classList.toggle('dark:text-gray-300', !isActive);
                        button.classList.toggle('hover:bg-gray-300', !isActive);
                        button.classList.toggle('dark:hover:bg-gray-500', !isActive);
                    });
                }

                initializeGantt(initialTasksData);

                // Native change event listener
                document.addEventListener('change', function(event) {
                    const target = event.target;
                    console.log('Native change event detected on:', target, 'name:', target.name, 'value:',
                        target.value);
                    if (target.matches('input[name="directorate_id"], input[name="priority"]')) {
                        const directorateId = document.querySelector('input[name="directorate_id"]')?.value ||
                            '';
                        const priorityId = document.querySelector('input[name="priority"]')?.value || '';
                        console.log('Applying filters (native):', {
                            directorateId,
                            priorityId
                        });
                        applyFilters(directorateId, priorityId);
                    }
                }, true);

                // jQuery fallback change event listener
                if (window.jQuery) {
                    jQuery(document).on('change', 'input[name="directorate_id"], input[name="priority"]', function() {
                        console.log('jQuery change event detected on:', this, 'name:', this.name, 'value:', this
                            .value);
                        const directorateId = jQuery('input[name="directorate_id"]').val() || '';
                        const priorityId = jQuery('input[name="priority"]').val() || '';
                        console.log('Applying filters (jQuery):', {
                            directorateId,
                            priorityId
                        });
                        applyFilters(directorateId, priorityId);
                    });
                }

                function applyFilters(directorateId, priorityId) {
                    console.log('Fetching tasks with filters:', {
                        directorateId,
                        priorityId
                    });

                    const url = new URL('/admin/tasks/gantt-chart', window.location.origin);
                    if (directorateId) url.searchParams.set('directorate_id', directorateId);
                    if (priorityId) url.searchParams.set('priority', priorityId);
                    console.log('AJAX URL:', url.toString());

                    const browserUrl = new URL(window.location.href);
                    if (directorateId) {
                        browserUrl.searchParams.set('directorate_id', directorateId);
                    } else {
                        browserUrl.searchParams.delete('directorate_id');
                    }
                    if (priorityId) {
                        browserUrl.searchParams.set('priority', priorityId);
                    } else {
                        browserUrl.searchParams.delete('priority');
                    }
                    window.history.pushState({}, '', browserUrl.toString());
                    console.log('Browser URL updated:', browserUrl.toString());

                    fetch(url.toString(), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            console.log('AJAX response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json().catch(error => {
                                console.error('JSON parse error:', error);
                                throw new Error('Invalid JSON response');
                            });
                        })
                        .then(data => {
                            console.log('AJAX response data:', data);
                            if (data.tasks && Array.isArray(data.tasks)) {
                                initializeGantt(data.tasks, currentViewMode);
                            } else {
                                console.warn('No tasks in response or invalid format:', data);
                                initializeGantt([], currentViewMode);
                                alert('No tasks match the selected filters.');
                            }
                        })
                        .catch(error => {
                            console.error('AJAX request failed:', error);
                            alert('Failed to load tasks: ' + error.message);
                        });
                }

                const urlParams = new URLSearchParams(window.location.search);
                const initialDirectorateId = urlParams.get('directorate_id') || '';
                const initialPriorityId = urlParams.get('priority') || '';
                console.log('Initial URL parameters:', {
                    initialDirectorateId,
                    initialPriorityId
                });
                if (initialDirectorateId || initialPriorityId) {
                    const directorateInput = document.querySelector('input[name="directorate_id"]');
                    const priorityInput = document.querySelector('input[name="priority"]');
                    if (directorateInput) directorateInput.value = initialDirectorateId;
                    if (priorityInput) priorityInput.value = initialPriorityId;

                    const $ = window.jQuery;
                    if ($) {
                        $('.js-single-select[data-name="directorate_id"]').trigger('options-updated', {
                            selected: initialDirectorateId
                        });
                        $('.js-single-select[data-name="priority"]').trigger('options-updated', {
                            selected: initialPriorityId
                        });
                    }

                    applyFilters(initialDirectorateId, initialPriorityId);
                }

                window.setViewMode = function(mode) {
                    console.log('Changing view mode to:', mode);
                    if (ganttInstance) {
                        try {
                            ganttInstance.change_view_mode(mode);
                            currentViewMode = mode;
                        } catch (e) {
                            console.warn('Error changing view mode:', e);
                        }
                        document.querySelectorAll('.flex.space-x-0.items-center button').forEach(button => {
                            const isActive = button.textContent.trim() === mode;
                            button.classList.toggle('bg-blue-500', isActive);
                            button.classList.toggle('text-white', isActive);
                            button.classList.toggle('hover:bg-blue-600', isActive);
                            button.classList.toggle('dark:bg-blue-600', isActive);
                            button.classList.toggle('dark:hover:bg-blue-700', isActive);
                            button.classList.toggle('bg-transparent', !isActive);
                            button.classList.toggle('text-gray-700', !isActive);
                            button.classList.toggle('dark:text-gray-300', !isActive);
                            button.classList.toggle('hover:bg-gray-300', !isActive);
                            button.classList.toggle('dark:hover:bg-gray-500', !isActive);
                        });
                    }
                };

                window.testFilter = function(directorateId) {
                    console.log('Manually testing filter with directorateId:', directorateId);
                    applyFilters(directorateId, '');
                };
            });
        </script>
    @endpush
</x-layouts.app>
