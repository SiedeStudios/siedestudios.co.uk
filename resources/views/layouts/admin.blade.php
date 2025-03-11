<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Dashboard') - SiedeStudios</title>
    <meta name="description" content="SiedeStudios Admin Dashboard">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @yield('styles')
</head>
<body>
    <div class="admin-layout" id="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="logo">
                    <svg class="logo-icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 2C8.268 2 2 8.268 2 16C2 23.732 8.268 30 16 30C23.732 30 30 23.732 30 16C30 8.268 23.732 2 16 2Z" fill="currentColor" fill-opacity="0.1"/>
                        <path d="M23 10.5C23 11.8807 21.8807 13 20.5 13C19.1193 13 18 11.8807 18 10.5C18 9.11929 19.1193 8 20.5 8C21.8807 8 23 9.11929 23 10.5Z" fill="currentColor"/>
                        <path d="M14 10.5C14 11.8807 12.8807 13 11.5 13C10.1193 13 9 11.8807 9 10.5C9 9.11929 10.1193 8 11.5 8C12.8807 8 14 9.11929 14 10.5Z" fill="currentColor"/>
                        <path d="M9 21.5V19C9 15.134 12.134 12 16 12C19.866 12 23 15.134 23 19V21.5C23 22.052 22.552 22.5 22 22.5H10C9.448 22.5 9 22.052 9 21.5Z" fill="currentColor"/>
                    </svg>
                    <span class="logo-text">Siede<span class="highlight">Studios</span></span>
                </a>
                <!--<button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                </button>-->
            </div>
            
            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <div class="nav-section-header">
                            <span>Main</span>
                        </div>
                        <ul>
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="9"></rect>
                                        <rect x="14" y="3" width="7" height="5"></rect>
                                        <rect x="14" y="12" width="7" height="9"></rect>
                                        <rect x="3" y="16" width="7" height="5"></rect>
                                    </svg>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-header">
                            <span>Content</span>
                        </div>
                        <ul>
                            <li>
                                <a href="{{ route('admin.portfolio.index') }}" class="{{ request()->routeIs('admin.portfolio*') ? 'active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                                    </svg>
                                    <span>Portfolio</span>
                                    @if(isset($draftProjectCount) && $draftProjectCount > 0)
                                        <span class="badge">{{ $draftProjectCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
                                    </svg>
                                    <span>Testimonials</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-header">
                            <span>Communications</span>
                        </div>
                        <ul>
                            <li>
                                <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                    <span>Messages</span>
                                    @if(isset($newMessagesCount) && $newMessagesCount > 0)
                                        <span class="badge badge-primary">{{ $newMessagesCount }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-header">
                            <span>Configuration</span>
                        </div>
                        <ul>
                            <li>
                                <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>Settings</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <span>{{ auth()->user()->initials }}</span>
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
                <div class="user-actions">
                    <a href="#" class="theme-toggle-btn" aria-label="Toggle theme">
                        <svg class="theme-icon-light" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"></circle>
                            <path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"></path>
                        </svg>
                        <svg class="theme-icon-dark" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                        </svg>
                    </a>
                    <a href="#" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" aria-label="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-container">
                    <div class="header-left">
                        <button class="mobile-sidebar-toggle" id="mobile-sidebar-toggle" aria-label="Toggle mobile sidebar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                        <h1 class="header-title">@yield('header-title', 'Dashboard')</h1>
                    </div>
                    
                    <div class="header-right">
                        @yield('header-actions')
                        
                        <a href="{{ url('/') }}" class="view-site-btn" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" y1="14" x2="21" y2="3"></line>
                            </svg>
                            <span>View Site</span>
                        </a>
                    </div>
                </div>
            </header>
            
            <div class="admin-content">
                @if (session('success'))
                    <div class="alert alert-success">
                        <div class="alert-content">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'" aria-label="Close alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-error">
                        <div class="alert-content">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.style.display='none'" aria-label="Close alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                @endif
                
                @yield('content')
            </div>
            
            <footer class="admin-footer">
                <div class="footer-copyright">
                    &copy; {{ date('Y') }} SiedeStudios. All rights reserved.
                </div>
                <div class="footer-version">
                    Version 1.0.0
                </div>
            </footer>
        </main>
    </div>
    
    @yield('scripts')
</body>
</html>