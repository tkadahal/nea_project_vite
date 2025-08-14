@props(['tasksFlat', 'priorityColors', 'statusColors'])

<div id="taskListContainer">
    @if (isset($tasksFlat) && count($tasksFlat) > 0)
        @foreach ($tasksFlat as $task)
            <div class="list-item border border-gray-300 dark:border-gray-600 rounded-lg p-4 mb-3"
                data-task-id="{{ $task['id'] }}" data-directorate-id="{{ $task['directorate_id'] ?? '' }}"
                data-department-id="{{ $task['department_id'] ?? '' }}"
                data-priority-id="{{ $task['priority_id'] ?? '' }}" data-project-id="{{ $task['project_id'] ?? '' }}"
                data-start-date="{{ $task['start_date'] ?? '' }}" data-due-date="{{ $task['due_date'] ?? '' }}"
                data-title="{{ $task['title'] }}"
                data-search="{{ strtolower($task['title'] . ' ' . ($task['description'] ?? '') . ' ' . ($task['priority'] ?? '') . ' ' . ($task['status'] ?? '') . ' ' . ($task['projects'][0] ?? '') . ' ' . ($task['directorate_id'] ?? '') . ' ' . ($task['department_id'] ?? '')) }}">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            {{ $task['title'] }}
                            @php
                                $priorityBadgeColor = $priorityColors[$task['priority'] ?? ''] ?? 'gray';
                            @endphp
                            @if ($task['priority'])
                                <span class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                    style="background-color: {{ $priorityBadgeColor }};">
                                    {{ ucfirst($task['priority']) }}
                                </span>
                            @endif
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{!! $task['description'] !!}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ trans('global.task.fields.project_id') }} :
                            <span class="font-medium">
                                {{ $task['projects'][0] ?? 'None' }}
                            </span> |
                            {{ trans('global.task.fields.status_id') }} :
                            @php
                                $taskStatusColor = $statusColors[$task['status_id']] ?? 'gray';
                            @endphp
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full"
                                style="background-color: {{ $taskStatusColor }};">
                                {{ $task['status'] ?? 'N/A' }}
                            </span> |
                            {{ trans('global.task.fields.due_date') }} :
                            <span class="font-medium">
                                {{ $task['due_date'] ? \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') : 'No Due Date' }}
                            </span>
                        </p>
                    </div>
                    <a href="{{ $task['view_url'] }}"
                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                        {{ trans('global.view') }}
                    </a>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-center p-3 text-gray-500 dark:text-gray-400">
            {{ trans('global.noRecords') }}
        </p>
    @endif
</div>
