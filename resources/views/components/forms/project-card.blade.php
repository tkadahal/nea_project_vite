@props([
    'title',
    'description',
    'directorate' => null,
    'fields' => [],
    'actions' => [],
    'routePrefix' => '',
    'deleteConfirmationMessage' => 'Are you sure you want to delete this item?',
    'arrayColumnColor' => [],
    'uniqueId' => null,
    'id' => null,
    'comment_count' => 0,
])

@php
    $dropdownId = 'dropdown-' . Str::slug($title) . ($uniqueId ? '-' . $uniqueId : '');
    $accordionId = 'accordion-' . Str::slug($title) . ($uniqueId ? '-' . $uniqueId : '');
@endphp

<div
    {{ $attributes->merge(['class' => 'bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md p-6 mb-4 border border-gray-300 dark:border-gray-600']) }}>
    <div class="flex justify-between items-start">
        <div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                {{ $title }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $description }}
            </p>
        </div>
        <div class="relative">
            <button type="button"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 focus:outline-none dropdown-toggle"
                data-dropdown="{{ $dropdownId }}" aria-label="Open actions menu" aria-haspopup="true"
                aria-controls="{{ $dropdownId }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v.01M12 12v.01M12 18v.01"></path>
                </svg>
            </button>
            <div id="{{ $dropdownId }}"
                class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-300 dark:border-gray-600 z-10">
                @if (in_array('view', $actions))
                    <a href="{{ route($routePrefix . '.show', $id) }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ trans('global.view') }}
                    </a>
                @endif
                @if (in_array('edit', $actions))
                    <a href="{{ route($routePrefix . '.edit', $id) }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ trans('global.edit') }}
                    </a>
                @endif
                @if (in_array('delete', $actions))
                    <form action="{{ route($routePrefix . '.destroy', $id) }}" method="POST"
                        onsubmit="return confirm('{{ $deleteConfirmationMessage }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            {{ trans('global.delete') }}
                        </button>
                    </form>
                @endif
                @can('budget_create')
                    <a href="{{ route('admin.budget.create') }}?project_id={{ $id }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        Add Budget
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Display Directorate directly -->
    @if ($directorate)
        <div class="mt-2">
            <span
                class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ trans('global.project.fields.directorate_id') }}:</span>
            <span class="text-gray-600 dark:text-gray-400">
                @if (isset($directorate['id']) && isset($arrayColumnColor['directorate'][$directorate['id']]))
                    <x-forms.badge :title="$directorate['title']" :color="$arrayColumnColor['directorate'][$directorate['id']] ?? 'gray'" />
                @else
                    {{ $directorate['title'] }}
                @endif
            </span>
        </div>
    @endif

    <!-- Buttons and Accordion -->
    <div class="mt-4">
        <div class="flex justify-end items-center gap-2">
            <button type="button"
                class="border border-blue-500 text-blue-500 px-2 py-1 rounded text-xs hover:bg-blue-500 hover:text-white dark:hover:bg-blue-500 dark:hover:text-white accordion-toggle"
                data-accordion="{{ $accordionId }}" aria-expanded="false" aria-controls="{{ $accordionId }}">
                {{ trans('global.view_details') }}
            </button>
            <a href="{{ route('admin.task.create') }}?project_id={{ $id }}"
                class="border border-blue-500 text-blue-500 px-2 py-1 rounded text-xs hover:bg-blue-500 hover:text-white dark:hover:bg-blue-500 dark:hover:text-white">
                Add Task
            </a>
            <a href="{{ route('admin.contract.create') }}?project_id={{ $id }}"
                class="border border-blue-500 text-blue-500 px-2 py-1 rounded text-xs hover:bg-blue-500 hover:text-white dark:hover:bg-blue-500 dark:hover:text-white">
                Add Contract
            </a>
            <a href="{{ route($routePrefix . '.show', $id) }}"
                class="relative text-blue-500 hover:text-blue-700 dark:hover:text-blue-300" title="Messages">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                    </path>
                </svg>
                <span
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center {{ $comment_count == 0 ? 'bg-gray-400' : 'bg-red-500' }}">
                    {{ $comment_count }}
                </span>
            </a>
        </div>
        <div id="{{ $accordionId }}" class="hidden mt-2 grid grid-cols-1 gap-2">
            @foreach ($fields as $field)
                @if ($field['label'] !== trans('global.project.fields.title'))
                    <div>
                        <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $field['label'] }}:</span>
                        <span class="text-gray-600 dark:text-gray-400">
                            @if (isset($field['color']) && $field['color'])
                                <x-forms.badge :title="$field['value']" :color="str_replace('#', '', $field['color'])" />
                            @elseif(isset($arrayColumnColor[$field['key']]) && !is_array($arrayColumnColor[$field['key']]))
                                <x-forms.badge :title="$field['value']" :color="$arrayColumnColor[$field['key']]" />
                            @else
                                {{ $field['value'] }}
                            @endif
                        </span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
