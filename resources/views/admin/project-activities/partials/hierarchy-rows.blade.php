{{-- resources/views/admin/project-activities/partials/hierarchy-rows.blade.php --}}
@foreach ($parentActivity->children as $childIndex => $activity)
    @php
        $childNumber = $childIndex + 1;
        $currentNumber = $numberPrefix . '.' . $childNumber;
        $hasChildren = $activity->children->isNotEmpty();
        $bgClass = $hasChildren ? 'bg-gray-50 dark:bg-gray-700/50' : '';
        $fontClass = $depth === 1 ? 'font-medium' : '';
        $totalBudget = $hasChildren ? 0 : $activity->total_budget ?? 0;
        $totalExpense = $hasChildren ? 0 : $activity->total_expense ?? 0;
        $planned = $hasChildren ? 0 : $activity->planned_budget ?? 0;
        $q1 = $hasChildren ? 0 : $activity->q1 ?? 0;
        $q2 = $hasChildren ? 0 : $activity->q2 ?? 0;
        $q3 = $hasChildren ? 0 : $activity->q3 ?? 0;
        $q4 = $hasChildren ? 0 : $activity->q4 ?? 0;
        $totalQuantity = $hasChildren ? 0 : $activity->total_quantity ?? 0;
        $completedQuantity = $hasChildren ? 0 : $activity->completed_quantity ?? 0;
        $plannedQuantity = $hasChildren ? 0 : $activity->planned_quantity ?? 0;
        $q1Quantity = $hasChildren ? 0 : $activity->q1_quantity ?? 0;
        $q2Quantity = $hasChildren ? 0 : $activity->q2_quantity ?? 0;
        $q3Quantity = $hasChildren ? 0 : $activity->q3_quantity ?? 0;
        $q4Quantity = $hasChildren ? 0 : $activity->q4_quantity ?? 0;
    @endphp
    <tr class="projectActivity-row {{ $bgClass }}" data-depth="{{ $depth }}">
        <td
            class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-sm text-gray-700 dark:text-gray-200">
            {{ $currentNumber }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1" style="padding-left: {{ $depth * 20 }}px;">
            <span class="{{ $fontClass }} text-gray-900 dark:text-gray-100">{{ $activity->program }}</span>
        </td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($totalQuantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($totalBudget, 2) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($completedQuantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($totalExpense, 2) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($plannedQuantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($planned, 2) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q1Quantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q1, 2) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q2Quantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q2, 2) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q3Quantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q3, 2) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q4Quantity, 0) }}</td>
        <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-700 dark:text-gray-200">
            {{ number_format($q4, 2) }}</td>
    </tr>
    @if ($hasChildren)
        @include('admin.project-activities.partials.hierarchy-rows', [
            'parentActivity' => $activity,
            'depth' => $depth + 1,
            'numberPrefix' => $currentNumber,
        ])
        @include('admin.project-activities.partials.totals-row', [
            'depth' => $depth,
            'number' => $currentNumber,
            'parentActivity' => $activity,
        ])
    @endif
@endforeach
