<div id="taskTableView" class="mb-6 hidden">
    <div id="taskTableContainer">
        @if (isset($tasksFlat) && count($tasksFlat) > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Title</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Project</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Priority</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Start Date</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Due Date</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Completion Date</th>
                            <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300"></th>
                        </tr>
                    </thead>
                    <tbody id="taskTableBody">
                        @foreach ($tasksFlat as $task)
                            <tr class="table-item border-b border-gray-300 dark:border-gray-600"
                                data-search="{{ collect($task)->flatten()->implode(' ') }}">
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $task->title ?? 'N/A' }}
                                    @php
                                        $priorityClass = '';
                                        switch ($task->priority) {
                                            case 'low':
                                                $priorityClass = 'bg-green-500';
                                                break;
                                            case 'medium':
                                                $priorityClass = 'bg-yellow-500';
                                                break;
                                            case 'high':
                                                $priorityClass = 'bg-red-500';
                                                break;
                                        }
                                    @endphp
                                    @if (!empty($priorityClass))
                                        <span
                                            class="badge inline-block px-2 py-1 text-xs font-semibold text-white rounded-full {{ $priorityClass }}">{{ ucfirst($task->priority) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $task->project->title ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $task->status->title ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ ucfirst($task->priority) ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $task->start_date ? (new DateTime($task->start_date))->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $task->due_date ? (new DateTime($task->due_date))->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $task->completion_date ? (new DateTime($task->completion_date))->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('admin.task.show', $task->id) }}"
                                        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center p-3 text-muted">Table will load here after filtering or view switch.</p>
        @endif
    </div>
</div>
