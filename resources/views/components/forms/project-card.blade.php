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
                        {{ __('View') }}
                    </a>
                @endif
                @if (in_array('edit', $actions))
                    <a href="{{ route($routePrefix . '.edit', $id) }}"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                        {{ __('Edit') }}
                    </a>
                @endif
                @if (in_array('delete', $actions))
                    <form action="{{ route($routePrefix . '.destroy', $id) }}" method="POST"
                        onsubmit="return confirm('{{ $deleteConfirmationMessage }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            {{ __('Delete') }}
                        </button>
                    </form>
                @endif
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

    <!-- Accordion for remaining fields -->
    <div class="mt-4">
        <button type="button"
            class="accordion-toggle w-full text-left px-4 py-2 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-500 focus:outline-none"
            data-accordion="{{ $accordionId }}" aria-expanded="false" aria-controls="{{ $accordionId }}">
            <div class="flex justify-between items-center">
                <span>{{ __('More Details') }}</span>
                <svg class="w-5 h-5 transform transition-transform accordion-icon" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>
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
