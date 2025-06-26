<aside
    class="js-sidebar bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden md:w-64 shadow-sm shadow-gray-300"
    data-open="false">
    <!-- Sidebar Content -->
    <div class="h-full flex flex-col">
        <!-- Sidebar Menu -->
        <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
            <ul class="space-y-1 px-2">
                <!-- Dashboard -->
                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon="fas-house" :active="request()->routeIs('dashboard*')">
                    Dashboard
                </x-layouts.sidebar-link>

                <li class="js-collapsible-menu">
                    <button
                        class="js-toggle-submenu flex items-center justify-between w-full px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                        <div class="flex items-center">
                            <!-- Replaced with ChartBarIcon for Analytics -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="js-submenu-label transition-opacity duration-300 opacity-100">
                                Analytics
                            </span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="js-chevron h-4 w-4 transition-transform opacity-100" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div class="js-submenu mt-1 ml-4 space-y-1 hidden">
                        <a href="{{ route('admin.tasks.analytics') }}"
                            class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <div class="flex items-center">
                                <!-- Replaced with ClipboardListIcon for Tasks -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                <span>Tasks</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.projects.analytics') }}"
                            class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <div class="flex items-center">
                                <!-- Replaced with FolderIcon for Projects -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span>Projects</span>
                            </div>
                        </a>
                    </div>
                </li>

                @can('admin_menu_access')
                    <!-- Components - Level 1 -->
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-2 font-semibold text-lg transition-opacity duration-300"
                                :class="{ 'opacity-0': !sidebarOpen }">Admin Menu</span>
                        </div>
                    </div>
                    @can('user_management_access')
                        <li class="js-collapsible-menu">
                            <button
                                class="js-toggle-submenu flex items-center justify-between w-full px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <span class="js-submenu-label transition-opacity duration-300 opacity-100">
                                        User Management
                                    </span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="js-chevron h-4 w-4 transition-transform opacity-100" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <div class="js-submenu mt-1 ml-4 space-y-1 hidden">
                                @can('permission_access')
                                    <a href="{{ route('admin.permission.index') }}"
                                        class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Permissions</span>
                                        </div>
                                    </a>
                                @endcan

                                @can('role_access')
                                    <a href="{{ route('admin.role.index') }}"
                                        class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                            </svg>
                                            <span>Roles</span>
                                        </div>
                                    </a>
                                @endcan

                                @can('user_access')
                                    <a href="{{ route('admin.user.index') }}"
                                        class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                            <span>Users</span>
                                        </div>
                                    </a>
                                @endcan
                            </div>
                        </li>
                    @endcan
                @endcan

                <li class="flex px-3 py-2 text-base font-bold text-blue-700">
                    {{ trans('global.menu.title') }}
                </li>
                <!-- Pages - Level 1 -->
                @can('directorate_access')
                    <x-layouts.sidebar-link href="{{ route('admin.directorate.index') }}" icon="fas-building"
                        :active="request()->routeIs('admin.directorate*')">
                        Directorate
                    </x-layouts.sidebar-link>
                @endcan

                @can('department_access')
                    <x-layouts.sidebar-link href="{{ route('admin.department.index') }}" icon="fas-bars"
                        :active="request()->routeIs('admin.department*')">
                        Department
                    </x-layouts.sidebar-link>
                @endcan

                @can('project_access')
                    <x-layouts.sidebar-link href="{{ route('admin.project.index') }}" icon="fas-clipboard"
                        :active="request()->routeIs('admin.project*')">
                        Projects
                    </x-layouts.sidebar-link>
                @endcan

                @can('contract_access')
                    <x-layouts.sidebar-link href="{{ route('admin.contract.index') }}" icon="fas-list" :active="request()->routeIs('admin.contract*')">
                        Contracts
                    </x-layouts.sidebar-link>
                @endcan

                @can('task_access')
                    <x-layouts.sidebar-link href="{{ route('admin.task.index') }}" icon="fas-pen-to-square"
                        :active="request()->routeIs('admin.task*')">
                        Tasks
                    </x-layouts.sidebar-link>
                @endcan


                {{-- @can('task_access') --}}
                <x-layouts.sidebar-link href="{{ route('admin.file.index') }}" icon="fas-folder" :active="request()->routeIs('admin.file*')">
                    Files
                </x-layouts.sidebar-link>
                {{-- @endcan --}}

                @can('event_access')
                    <x-layouts.sidebar-link href="{{ route('admin.event.index') }}" icon="fas-pen-to-square"
                        :active="request()->routeIs('admin.event*')">
                        Events
                    </x-layouts.sidebar-link>
                @endcan

                @can('status_access')
                    <x-layouts.sidebar-link href="{{ route('admin.status.index') }}" icon="fas-cog" :active="request()->routeIs('admin.status*')">
                        Status
                    </x-layouts.sidebar-link>
                @endcan

                @can('priority_access')
                    <x-layouts.sidebar-link href="{{ route('admin.priority.index') }}" icon="fas-fire" :active="request()->routeIs('admin.priority*')">
                        Priority
                    </x-layouts.sidebar-link>
                @endcan

                <li class="js-collapsible-menu">
                    <button
                        class="js-toggle-submenu flex items-center justify-between w-full px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="js-submenu-label transition-opacity duration-300 opacity-100">Reports</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="js-chevron h-4 w-4 transition-transform opacity-100" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div class="js-submenu mt-1 ml-4 space-y-1 hidden">
                        <a href="#"
                            class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Profile</span>
                            </div>
                        </a>
                        <a href="login.html"
                            class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                <span>Login</span>
                            </div>
                        </a>
                        <a href="register.html"
                            class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span>Register</span>
                            </div>
                        </a>
                        <a href="#"
                            class="block px-3 py-2 text-sm rounded-md hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Error</span>
                            </div>
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</aside>
