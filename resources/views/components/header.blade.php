<header class="site-header">
    <div class="header-inner">
        <div class="logo">
            <a href="{{ route('home') }}" aria-label="SiedeStudios">
                <span class="logo-text"><span class="siede">Siede</span><span class="highlight">Studios</span></span>
            </a>
        </div>
        
        <nav class="main-nav" aria-label="Main navigation">
            <ul>
                <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
                <li><a href="{{ route('services') }}" class="{{ request()->routeIs('services') ? 'active' : '' }}">Services</a></li>
                <li><a href="{{ route('portfolio') }}" class="{{ request()->routeIs('portfolio') ? 'active' : '' }}">Portfolio</a></li>
                <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
            </ul>
        </nav>
        
        <div class="header-actions">
            @include('components.theme-toggle')
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation -->
<div class="mobile-nav" aria-hidden="true">
    <ul>
        <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
        <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
        <li><a href="{{ route('services') }}" class="{{ request()->routeIs('services') ? 'active' : '' }}">Services</a></li>
        <li><a href="{{ route('portfolio') }}" class="{{ request()->routeIs('portfolio') ? 'active' : '' }}">Portfolio</a></li>
        <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
    </ul>
    
    <div class="mobile-nav-socials">
        <a href="https://twitter.com/siedestudios" aria-label="Twitter" target="_blank" rel="noopener">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
            </svg>
        </a>
        <a href="https://github.com/siedestudios" aria-label="GitHub" target="_blank" rel="noopener">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
            </svg>
        </a>
        <a href="https://discord.gg/siedestudios" aria-label="Discord" target="_blank" rel="noopener">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 18a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12Z"></path>
                <path d="M7 10.5v-1c0-.3.3-.5.6-.5h.8c.3 0 .6.2.6.5v1c0 .3-.3.5-.6.5h-.8c-.3 0-.6-.2-.6-.5Z"></path>
                <path d="M15 10.5v-1c0-.3.3-.5.6-.5h.8c.3 0 .6.2.6.5v1c0 .3-.3.5-.6.5h-.8c-.3 0-.6-.2-.6-.5Z"></path>
                <path d="M8 15h8"></path>
            </svg>
        </a>
    </div>
</div>