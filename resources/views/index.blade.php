@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <span class="hero-title-top">Crafting Digital</span>
                    <span class="hero-title-main">Experiences & Gaming Content</span>
                </h1>
                <p class="hero-subtitle">Professional web development and game server content.</p>
                <div class="hero-cta">
                    <a href="{{ route('services') }}" class="btn btn-primary">Our Services</a>
                    <a href="{{ route('contact') }}" class="btn btn-outline">Get in Touch</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="code-window">
                    <div class="code-header">
                        <span class="code-dot"></span>
                        <span class="code-dot"></span>
                        <span class="code-dot"></span>
                        <span class="code-title">siede_studios.lua</span>
                    </div>
                    <div class="code-content">
                        <pre><code>
<span class="code-keyword">local</span> <span class="code-variable">SiedeStudios</span> = {}

<span class="code-variable">SiedeStudios.Services</span> = {
    <span class="code-string">"Web Development"</span>,
    <span class="code-string">"UI/UX Design"</span>,
    <span class="code-string">"Custom Lua Content"</span>,
    <span class="code-string">"GMod Addons"</span>,
}

<span class="code-keyword">function</span> <span class="code-variable">SiedeStudios:CreateAwesome</span>()
    <span class="code-keyword">return</span> <span class="code-string">"Amazing Results"</span>
<span class="code-keyword">end</span>

<span class="code-variable">SiedeStudios:CreateAwesome</span>()
                        </code></pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-backdrop">
            <div class="hero-shapes">
                <div class="hero-shape hero-shape-1"></div>
                <div class="hero-shape hero-shape-2"></div>
                <div class="hero-shape hero-shape-3"></div>
            </div>
        </div>
    </section>

    <!-- Services Preview Section -->
    <section class="services-preview">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <p>Comprehensive solutions for web and game development needs</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <h3>Web Development</h3>
                    <p>Custom websites and web applications using modern technologies like Laravel</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3H6a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3V6a3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 3 3 0 0 0-3-3z"></path>
                        </svg>
                    </div>
                    <h3>UI/UX Design</h3>
                    <p>Intuitive user interfaces and engaging user experiences for your digital products</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <h3>Custom Lua Development</h3>
                    <p>Specialized Lua scripting for game modifications and custom functionality</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>GMod Addons</h3>
                    <p>Custom content and addons for Garry's Mod to enhance gameplay and server functionality</p>
                </div>
            </div>
            <div class="section-cta">
                <a href="{{ route('services') }}" class="btn btn-secondary">View All Services</a>
            </div>
        </div>
    </section>

    <!-- About Preview Section -->
    <section class="about-preview">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <h2>Who I Am</h2>
                    <p class="lead">SiedeStudios is my personal brand where I focus on creating exceptional digital experiences.</p>
                    <p>I combine technical expertise with creative thinking to deliver web development projects and custom game content that exceeds expectations.</p>
                    <a href="{{ route('about') }}" class="btn btn-text">Learn more about me <span class="arrow">→</span></a>
                </div>
                <div class="about-image">
                    <img src="{{ asset('images/431474257_7098146786971570_4160786033065290043_n.jpg') }}" alt="SiedeStudios team at work">
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Preview Section -->
    <section class="portfolio-preview">
        <div class="container">
            <div class="section-header">
                <h2>Our Work</h2>
                <p>Check out some of our recent projects</p>
            </div>
            <div class="portfolio-grid">
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="{{ asset('images/portfolio/dashboard.png') }}" alt="Web Project 1">
                    </div>
                    <div class="portfolio-overlay">
                        <h3>SiedeGaming Web Suite</h3>
                        <p>Laravel, Custom SCSS, jQuery</p>
                    </div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="{{ asset('images/portfolio/sandervk-com.png') }}" alt="GMod Project 1">
                    </div>
                    <div class="portfolio-overlay">
                        <h3>SanderVK.com</h3>
                        <p>Custom SCSS, jQuery, UI/UX</p>
                    </div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <img src="{{ asset('images/portfolio/sandervktours-com.png') }}" alt="Web Project 2">
                    </div>
                    <div class="portfolio-overlay">
                        <h3>SanderVKTours.com</h3>
                        <p>Laravel, Payment Gateway, Blog, Custom CSS, jQuery</p>
                    </div>
                </div>
            </div>
            <div class="section-cta">
                <a href="{{ route('portfolio') }}" class="btn btn-secondary">View Full Portfolio</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to start your project?</h2>
                <p>Let's create something amazing together. Contact us today to discuss your ideas.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Get in Touch</a>
            </div>
        </div>
    </section>
@endsection