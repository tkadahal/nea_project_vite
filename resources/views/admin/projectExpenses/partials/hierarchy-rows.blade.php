@foreach ($group->get($parentId, collect()) as $childIndex => $activity)
    @php
        $childNumber = $childIndex + 1;
        $currentNumber = $numberPrefix . '.' . $childNumber;
        $childGroup = $group->get($activity->id, collect());
        $bgClass = $childGroup->isNotEmpty() ? 'bg-gray-50 dark:bg-gray-700/50' : '';
        $fontClass = $depth === 1 ? 'font-medium' : '';
        $amounts = $activityAmounts[$activity->id] ?? ['q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0];
    @endphp

    <tr class="projectExpense-row {{ $bgClass }}" data-depth="{{ $depth }}">
        <td
            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
            {{ $currentNumber }}
        </td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1" style="padding-left: {{ $depth * 20 }}px;">
            <span
                class="{{ $fontClass }} text-gray-900 dark:text-gray-100">{{ $activity->program ?? 'Untitled' }}</span>
        </td>
        @foreach ([1, 2, 3, 4] as $q)
            <td
                class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-sm text-gray-700 dark:text-gray-200">
                {{ number_format($amounts['q' . $q], 2) }}
            </td>
        @endforeach
    </tr>

    {{-- Recursive children --}}
    @if ($childGroup->isNotEmpty())
        @include('admin.projectExpenses.partials.hierarchy-rows', [
            'group' => $group,
            'parentId' => $activity->id,
            'depth' => $depth + 1,
            'numberPrefix' => $currentNumber,
            'activityAmounts' => $activityAmounts,
            'subtreeQuarterTotals' => $subtreeQuarterTotals,
        ])
        @include('admin.projectExpenses.partials.totals-row', [
            'depth' => $depth,
            'number' => $currentNumber,
            'activityId' => $activity->id,
            'activityAmounts' => $activityAmounts,
            'subtreeQuarterTotals' => $subtreeQuarterTotals,
        ])
    @endif
@endforeach
