<div>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Your Calendar</h2>
    </div>

    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center">
            <button wire:click="prevMonth"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button wire:click="goToToday"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mr-2 px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                Today
            </button>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-2" id="calendarMonthYear">
                {{ \Carbon\Carbon::parse($currentMonth)->format('F Y') }}</h2>
            <button wire:click="nextMonth"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
        <!-- Three-dot button with dropdown -->
        <div class="relative">
            <button id="dropdownButton"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </button>
            <div id="dropdownMenu"
                class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-10">
                <a href="{{ route('admin.calendar.store') }}"
                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Add Event
                </a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-6">
        <!-- Calendar on the left -->
        <div>
            <div id="calendar" wire:ignore></div>
        </div>
        <!-- Events list on the right -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Events for
                {{ \Carbon\Carbon::parse($activeDate)->format('F j, Y') }}</h2>
            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                @forelse ($filteredEvents as $event)
                    <div>
                        <span>⏰ {{ \Carbon\Carbon::parse($event['start'])->format('g:i A') }} -
                            {{ $event['end'] ? \Carbon\Carbon::parse($event['end'])->format('g:i A') : 'No end time' }}</span>
                        <div class="ml-2"><strong>{{ $event['title'] }}</strong></div>
                        <div class="ml-2">{{ $event['description'] ?? 'No description' }}</div>
                    </div>
                @empty
                    <p>No events for this date.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
        <style>
            #calendar .fc-daygrid-day[data-date="{{ now()->toDateString() }}"] {
                background-color: #ef4444 !important;
                color: white !important;
                border: 2px solid #b91c1c !important;
                border-radius: 4px !important;
            }

            #calendar .fc-header-toolbar {
                display: none;
            }

            #calendar .fc-button {
                background-color: transparent;
                border: none;
                color: #6b7280;
            }

            #calendar .fc-button:hover {
                background-color: #f3f4f6;
            }

            #calendar .fc-daygrid-day.fc-daygrid-day-selected {
                background-color: #e0e7ff !important;
                color: #1e40af !important;
            }

            /* Force hand pointer on all clickable calendar elements */
            #calendar .fc-daygrid-day,
            #calendar .fc-daygrid-day-frame,
            #calendar .fc-daygrid-day-top,
            #calendar .fc-daygrid-day-number,
            #calendar .fc-daygrid-day-number a {
                cursor: pointer !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                if (!calendarEl) {
                    console.error('Calendar element not found!');
                    return;
                }

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: false,
                    events: [], // No events on calendar grid
                    dateClick: function(info) {
                        // Remove previous selection
                        var previousSelected = calendarEl.querySelector('.fc-daygrid-day-selected');
                        if (previousSelected) {
                            previousSelected.classList.remove('fc-daygrid-day-selected');
                        }
                        // Add selection to clicked cell
                        info.dayEl.classList.add('fc-daygrid-day-selected');
                        window.Livewire.dispatch('setActiveDate', {
                            date: info.dateStr
                        });
                    },
                    datesSet: function(info) {
                        document.getElementById('calendarMonthYear').textContent = info.view.title;
                        // Re-apply today highlight
                        highlightToday();
                    }
                });
                calendar.render();

                // Highlight today's date
                function highlightToday() {
                    var today = new Date();
                    var todayStr = today.toISOString().split('T')[0]; // e.g., 2025-06-11
                    console.log('Today date:', todayStr); // Debug
                    var todayCell = calendarEl.querySelector(`[data-date="${todayStr}"]`);
                    if (todayCell) {
                        todayCell.classList.add('bg-red-500', 'text-white');
                        console.log('Today cell highlighted:', todayCell); // Debug
                    } else {
                        console.warn('Today cell not found for date:', todayStr); // Debug
                    }
                }
                highlightToday();

                // Highlight active date if set
                var activeDate = @json($activeDate);
                var activeCell = calendarEl.querySelector(`[data-date="${activeDate}"]`);
                if (activeCell) {
                    activeCell.classList.add('fc-daygrid-day-selected');
                }

                // Listen for changeMonth event from Livewire
                Livewire.on('changeMonth', (event) => {
                    calendar.gotoDate(event.date);
                    document.getElementById('calendarMonthYear').textContent = calendar.view.title;
                    highlightToday(); // Re-apply today highlight
                });

                // Toggle dropdown menu
                const dropdownButton = document.getElementById('dropdownButton');
                const dropdownMenu = document.getElementById('dropdownMenu');
                dropdownButton.addEventListener('click', () => {
                    dropdownMenu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (event) => {
                    if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });

                // Debug cursor on hover
                calendarEl.addEventListener('mouseover', (event) => {
                    if (event.target.closest('.fc-daygrid-day')) {
                        console.log('Hovering over day cell:', event.target);
                    }
                });
            });
        </script>
    @endpush
</div>
