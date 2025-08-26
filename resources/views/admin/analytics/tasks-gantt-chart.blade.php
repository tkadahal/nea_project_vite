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
                <button onclick="setViewMode('day')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="day-btn">
                    <span class="md:hidden">D</span><span class="hidden md:inline">Day</span>
                </button>
                <button onclick="setViewMode('week')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 bg-blue-500 text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="week-btn">
                    <span class="md:hidden">W</span><span class="hidden md:inline">Week</span>
                </button>
                <button onclick="setViewMode('month')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-800 transition-colors duration-200 border-r border-gray-300 dark:border-gray-700 first:rounded-l-lg last:rounded-r-lg md:first:rounded-l-none md:last:rounded-r-none"
                    id="month-btn">
                    <span class="md:hidden">M</span><span class="hidden md:inline">Month</span>
                </button>
                <button onclick="setViewMode('year')"
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

    <div class="bg-gray-800 p-4 rounded-lg shadow" style="width: 100%; overflow-x: auto; min-height: 400px;">
        <div id="gantt-container" style="width: 100%; height: 400px;"></div>
    </div>

    <style>
        #gantt-container .gantt_task_line {
            background-color: #6b46c1;
            border-radius: 4px;
            cursor: pointer;
        }

        #gantt-container .gantt_task_progress {
            background-color: #8a4af3;
            border-radius: 4px;
        }

        #gantt-container .gantt_link_line {
            stroke: #a0aec0;
            stroke-width: 2;
        }

        #gantt-container .gantt_grid_data .gantt_row {
            background-color: #2d3748;
        }

        #gantt-container .gantt_grid_head_cell {
            background-color: #4a5568;
            color: #e2e8f0;
        }

        #gantt-container .gantt_scale_cell {
            background-color: #4a5568;
            color: #e2e8f0;
        }

        #gantt-container .gantt_task_content {
            color: #e2e8f0;
            font-size: 12px;
            text-align: center;
        }

        .custom-tooltip {
            position: absolute;
            z-index: 1001;
            background: #2d3748;
            color: #e2e8f0;
            padding: 8px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            pointer-events: none;
        }
    </style>

    @push('scripts')
        <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
        <link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                console.log('DOMContentLoaded fired');
                const initialTasksData = @json($tasks);
                const initialLinksData = @json($links);
                console.log('Initial Tasks Data:', initialTasksData);
                console.log('Initial Links Data:', initialLinksData);

                let currentViewMode = 'week';
                let tooltipElement = null;

                function formatTasksForGantt(tasksToFormat) {
                    return tasksToFormat.map(task => ({
                        id: task.id.toString(),
                        text: task.title,
                        start_date: task.start,
                        end_date: task.end,
                        progress: 0,
                        parent: task.parent_id ? task.parent_id.toString() : 0,
                        open: true,
                        project_name: task.project_name || 'N/A',
                        directorate: task.directorate || 'N/A',
                        assigned_user: task.assigned_user || 'N/A'
                    }));
                }

                function initializeGantt(tasksToRender, linksToRender, viewMode = 'week') {
                    console.log('Reinitializing Gantt with viewMode:', viewMode, 'tasks:', tasksToRender, 'links:',
                        linksToRender);
                    const formattedTasks = formatTasksForGantt(tasksToRender);

                    gantt.clearAll();

                    gantt.config.date_format = "%Y-%m-%d";
                    gantt.config.scale_height = 50;
                    gantt.config.row_height = 40;
                    gantt.config.task_height = 20;
                    gantt.config.link_line_width = 2;
                    gantt.config.links = {
                        finish_to_start: 0
                    };
                    gantt.config.columns = [];

                    // Migrate to new scales configuration
                    switch (viewMode) {
                        case 'day':
                            gantt.config.scales = [{
                                unit: "day",
                                step: 1,
                                format: "%d"
                            }];
                            break;
                        case 'week':
                            gantt.config.scales = [{
                                unit: "week",
                                step: 1,
                                format: "Week #%W"
                            }];
                            break;
                        case 'month':
                            gantt.config.scales = [{
                                unit: "month",
                                step: 1,
                                format: "%F"
                            }];
                            break;
                        case 'year':
                            gantt.config.scales = [{
                                unit: "year",
                                step: 1,
                                format: "%Y"
                            }];
                            break;
                    }

                    // Custom scale for week number of month (approximate)
                    if (viewMode === 'week') {
                        gantt.templates.scale_cell = function(date) {
                            var weekNum = Math.ceil((date.getDate() + new Date(date.getFullYear(), date.getMonth(),
                                1).getDay()) / 7);
                            return "Week " + weekNum;
                        };
                    }

                    gantt.init("gantt-container");
                    gantt.parse({
                        data: formattedTasks,
                        links: linksToRender
                    });

                    // Attach click listener immediately after parse
                    const container = document.getElementById('gantt-container');
                    if (container) {
                        container.removeEventListener('click', handleTaskBarClick);
                        container.addEventListener('click', handleTaskBarClick);
                        console.log('Click event listener attached to gantt-container');
                    } else {
                        console.error('Gantt container not found');
                    }
                }

                function handleTaskBarClick(e) {
                    console.log('Click event triggered', e);
                    const bar = e.target.closest('.gantt_task_line');
                    if (bar) {
                        const id = bar.getAttribute('task_id');
                        console.log('Clicked bar ID:', id);
                        if (id) {
                            var task = gantt.getTask(id);
                            if (task) {
                                var pos = gantt.getTaskPosition(task);
                                if (pos) {
                                    // Remove existing tooltip if it exists
                                    if (tooltipElement && tooltipElement.parentNode) {
                                        tooltipElement.parentNode.removeChild(tooltipElement);
                                    }
                                    // Create and display tooltip instantly
                                    tooltipElement = document.createElement('div');
                                    tooltipElement.className = 'custom-tooltip';
                                    tooltipElement.innerHTML = `
                                        <p><strong>Project:</strong> ${task.project_name}</p>
                                        <p><strong>Directorate:</strong> ${task.directorate}</p>
                                        <p><strong>Assigned User:</strong> ${task.assigned_user}</p>
                                    `;
                                    tooltipElement.style.left = `${pos.left + 10}px`;
                                    tooltipElement.style.top = `${pos.top - 40}px`;
                                    document.body.appendChild(tooltipElement);
                                    console.log('Tooltip displayed for task:', task.text);
                                } else {
                                    console.error('Failed to get task position for ID:', id);
                                }
                            } else {
                                console.error('Task not found for ID:', id);
                            }
                        } else {
                            console.error('No task_id attribute on clicked bar');
                        }
                    }
                }

                initializeGantt(initialTasksData, initialLinksData);

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
                            if (data.tasks && Array.isArray(data.tasks) && data.links && Array.isArray(data
                                .links)) {
                                initializeGantt(data.tasks, data.links, currentViewMode);
                            } else {
                                console.warn('No tasks or links in response or invalid format:', data);
                                initializeGantt([], [], currentViewMode);
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
                    currentViewMode = mode;
                    initializeGantt(initialTasksData, initialLinksData, mode);
                };
            });
        </script>
    @endpush
</x-layouts.app>
