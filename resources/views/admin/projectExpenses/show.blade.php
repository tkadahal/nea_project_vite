<x-layouts.app>
    <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                Expenses for {{ $project->title }} - {{ $fiscalYear->title }}
            </h1>
            <a href="{{ route('admin.projectExpense.index') }}"
                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                Back to Overview
            </a>
        </div>
        <p class="text-gray-600 dark:text-gray-400">
            Detailed breakdown of Expeneses for Fiscal Year {{ $fiscalYear->title }}.
        </p>
        <div class="mt-2 flex flex-wrap gap-4 text-sm">
            <div
                class="bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200 px-4 py-2 rounded-lg border border-green-200 dark:border-green-700">
                Total Expense: {{ number_format($totalExpense, 2) }}
            </div>
            <div
                class="bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 px-4 py-2 rounded-lg border border-blue-200 dark:border-blue-700">
                Capital Expense: {{ number_format($capitalTotal, 2) }}
            </div>
            <div
                class="bg-indigo-100 dark:bg-indigo-900/20 text-indigo-800 dark:text-indigo-200 px-4 py-2 rounded-lg border border-indigo-200 dark:border-indigo-700">
                Recurrent Expense: {{ number_format($recurrentTotal, 2) }}
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Capital Expenses Section -->
        @if ($capitalActivities->isNotEmpty())
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-t-lg border-b border-gray-200 dark:border-gray-600">
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                        Capital Expenses ({{ number_format($capitalTotal, 2) }})
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-600 sticky top-0 z-10">
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                    #
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                    Activity/Program
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q1
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q2
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q3
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q4
                                </th>
                            </tr>
                        </thead>
                        <tbody id="capital-tbody">
                            @foreach ($capitalActivities as $rootIndex => $activity)
                                @php
                                    $rootNumber = $rootIndex + 1;
                                    $amounts = $activityAmounts[$activity->id] ?? [
                                        'q1' => 0,
                                        'q2' => 0,
                                        'q3' => 0,
                                        'q4' => 0,
                                    ];
                                    $bgClass = $groupedActivities->has($activity->id)
                                        ? 'bg-gray-50 dark:bg-gray-700/50'
                                        : '';
                                    $hasChildren = $groupedActivities->has($activity->id);
                                @endphp
                                <tr class="projectExpense-row {{ $bgClass }}" data-depth="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        {{ $rootNumber }}
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1"
                                        style="padding-left: {{ 0 * 20 }}px;">
                                        <span
                                            class="font-bold text-gray-900 dark:text-gray-100">{{ $activity->program ?? 'Untitled' }}</span>
                                    </td>
                                    @foreach ([1, 2, 3, 4] as $q)
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-sm text-gray-700 dark:text-gray-200">
                                            {{ number_format($amounts['q' . $q], 2) }}
                                        </td>
                                    @endforeach
                                </tr>
                                @if ($hasChildren)
                                    @include('admin.projectExpenses.partials.hierarchy-rows', [
                                        'group' => $groupedActivities,
                                        'parentId' => $activity->id,
                                        'depth' => 1,
                                        'numberPrefix' => $rootNumber,
                                        'activityAmounts' => $activityAmounts,
                                        'subtreeQuarterTotals' => $subtreeQuarterTotals,
                                    ])
                                    @include('admin.projectExpenses.partials.totals-row', [
                                        'depth' => 0,
                                        'number' => $rootNumber,
                                        'activityId' => $activity->id,
                                        'activityAmounts' => $activityAmounts,
                                        'subtreeQuarterTotals' => $subtreeQuarterTotals,
                                    ])
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center text-gray-500 dark:text-gray-400">
                No capital expenses found.
            </div>
        @endif

        <!-- Recurrent Expenses Section -->
        @if ($recurrentActivities->isNotEmpty())
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 bg-gray-50 dark:bg-gray-700 rounded-t-lg border-b border-gray-200 dark:border-gray-600">
                    <h3
                        class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                        Recurrent Expenses ({{ number_format($recurrentTotal, 2) }})
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-600 sticky top-0 z-10">
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-12">
                                    #
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-gray-700 dark:text-gray-200 w-64">
                                    Activity/Program
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q1
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q2
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q3
                                </th>
                                <th
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-sm font-semibold text-center">
                                    Q4
                                </th>
                            </tr>
                        </thead>
                        <tbody id="recurrent-tbody">
                            @foreach ($recurrentActivities as $rootIndex => $activity)
                                @php
                                    $rootNumber = $rootIndex + 1;
                                    $amounts = $activityAmounts[$activity->id] ?? [
                                        'q1' => 0,
                                        'q2' => 0,
                                        'q3' => 0,
                                        'q4' => 0,
                                    ];
                                    $bgClass = $groupedActivities->has($activity->id)
                                        ? 'bg-gray-50 dark:bg-gray-700/50'
                                        : '';
                                    $hasChildren = $groupedActivities->has($activity->id);
                                @endphp
                                <tr class="projectExpense-row {{ $bgClass }}" data-depth="0">
                                    <td
                                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
                                        {{ $rootNumber }}
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1"
                                        style="padding-left: {{ 0 * 20 }}px;">
                                        <span
                                            class="font-bold text-gray-900 dark:text-gray-100">{{ $activity->program ?? 'Untitled' }}</span>
                                    </td>
                                    @foreach ([1, 2, 3, 4] as $q)
                                        <td
                                            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-sm text-gray-700 dark:text-gray-200">
                                            {{ number_format($amounts['q' . $q], 2) }}
                                        </td>
                                    @endforeach
                                </tr>
                                @if ($hasChildren)
                                    @include('admin.projectExpenses.partials.hierarchy-rows', [
                                        'group' => $groupedActivities,
                                        'parentId' => $activity->id,
                                        'depth' => 1,
                                        'numberPrefix' => $rootNumber,
                                        'activityAmounts' => $activityAmounts,
                                        'subtreeQuarterTotals' => $subtreeQuarterTotals,
                                    ])
                                    @include('admin.projectExpenses.partials.totals-row', [
                                        'depth' => 0,
                                        'number' => $rootNumber,
                                        'activityId' => $activity->id,
                                        'activityAmounts' => $activityAmounts,
                                        'subtreeQuarterTotals' => $subtreeQuarterTotals,
                                    ])
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center text-gray-500 dark:text-gray-400">
                No recurrent expenses found.
            </div>
        @endif
    </div>

    <div class="mt-8 flex gap-4">
        @can('projectExpense_edit')
            <a href="{{ route('admin.projectExpense.edit', $project->id) }}"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Edit Expense
            </a>
        @endcan

        @can('projectExpense_delete')
            <form action="{{ route('admin.projectExpense.destroy', $project->id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this expense?')" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 border-0 cursor-pointer">
                    Delete Expense
                </button>
            </form>
        @endcan
    </div>
</x-layouts.app>
