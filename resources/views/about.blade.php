@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="about-hero-content">
                <h1 class="slide-in-bottom">About SiedeStudios</h1>
                <p class="lead slide-in-bottom">I'm a passionate developer focused on creating exceptional digital experiences.</p>
            </div>
        </div>
    </section>

    <!-- About Content Section -->
    <section class="about-content-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-image slide-in-right">
                    <img src="{{ asset('images/431474257_7098146786971570_4160786033065290043_n.jpg') }}" alt="SiedeStudios Founder">
                </div>
                <div class="about-text slide-in-left">
                    <h2>My Story</h2>
                    <p>SiedeStudios was founded in 2020 with a simple mission: to create digital experiences that make an impact. What started as a personal freelance operation has grown into a specialized service focused on delivering exceptional digital solutions.</p>
                    <p>I specialize in both web development using modern frameworks like Laravel and custom game content creation, particularly for platforms like Garry's Mod using Lua.</p>
                    <p>My commitment to quality, attention to detail, and passion for pushing boundaries has allowed me to build lasting relationships with clients across various industries.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- My Values Section -->
    <section class="values-section">
        <div class="container">
            <h2 class="section-title text-center slide-in-bottom">My Values</h2>
            <div class="values-grid">
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h3>Quality First</h3>
                    <p>I believe in doing things right the first time. Quality is never compromised, and I take pride in my attention to detail.</p>
                </div>
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                    <h3>Innovation</h3>
                    <p>I constantly push boundaries and explore new technologies to create the most effective solutions for my clients.</p>
                </div>
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Collaboration</h3>
                    <p>I believe the best results come from open communication and collaborative efforts with my clients throughout the entire development process.</p>
                </div>
                <div class="value-card fade-in">
                    <div class="value-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="4 17 10 11 4 5"></polyline>
                            <line x1="12" y1="19" x2="20" y2="19"></line>
                        </svg>
                    </div>
                    <h3>Results-Driven</h3>
                    <p>I focus on creating solutions that deliver tangible results and measurable success for my clients.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section (replacing Team Section) -->
    <section class="skills-section">
        <div class="container">
            <h2 class="section-title text-center slide-in-bottom">My Expertise</h2>
            <div class="skills-grid">
                <div class="skill-area fade-in">
                    <div class="skill-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <h3>Web Development</h3>
                    <p>Specializing in Laravel, creating responsive, performant web applications with clean, maintainable code.</p>
                    <div class="skill-tags">
                        <span>PHP</span>
                        <span>Laravel</span>
                        <span>MySQL</span>
                        <span>JavaScript</span>
                        <span>HTML/CSS</span>
                    </div>
                </div>
                
                <div class="skill-area fade-in">
                    <div class="skill-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3H6a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3V6a3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 3 3 0 0 0-3-3z"></path>
                        </svg>
                    </div>
                    <h3>UI/UX Design</h3>
                    <p>Creating intuitive, user-centered designs that are both functional and visually appealing.</p>
                    <div class="skill-tags">
                        <span>User Interface</span>
                        <span>User Experience</span>
                        <span>Responsive Design</span>
                        <span>SCSS</span>
                    </div>
                </div>
                
                <div class="skill-area fade-in">
                    <div class="skill-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <h3>Lua Development</h3>
                    <p>Expert in Lua programming for game modifications with a focus on performance optimization.</p>
                    <div class="skill-tags">
                        <span>Lua</span>
                        <span>Game Development</span>
                        <span>Scripting</span>
                        <span>Optimization</span>
                    </div>
                </div>
                
                <div class="skill-area fade-in">
                    <div class="skill-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>GMod Content Development</h3>
                    <p>Creating custom server systems, gameplay mechanics, and tools for Garry's Mod.</p>
                    <div class="skill-tags">
                        <span>GMod</span>
                        <span>Server Systems</span>
                        <span>Game Mechanics</span>
                        <span>Custom Entities</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content fade-in">
                <h2>Want to work with me?</h2>
                <p>I'm always looking for exciting new projects and challenges.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Get in Touch</a>
            </div>
        </div>
    </section>
@endsection