@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="services-hero">
        <div class="hero-pattern"></div>
        <div class="container">
            <div class="services-hero-content">
                <span class="hero-label fade-in">What We Do</span>
                <h1 class="slide-in-bottom">Services & Expertise</h1>
                <p class="hero-description slide-in-bottom">Building exceptional web applications and custom game content with attention to detail and performance</p>
                <div class="hero-cta fade-in">
                    <a href="#services-overview" class="btn btn-primary">Explore Our Services</a>
                    <a href="{{ route('contact') }}" class="btn btn-outline">Get in Touch</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Overview -->
    <section id="services-overview" class="services-overview">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="slide-in-bottom">Our Core Services</h2>
                <p class="text-muted slide-in-bottom">Comprehensive solutions for all your digital needs</p>
            </div>
            
            <div class="services-cards">
                <!-- Web Development Card -->
                <div class="service-card fade-in">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <h3>Web Development</h3>
                    <p>Custom websites and web applications built with modern technologies.</p>
                    <a href="#web-development" class="card-link">Learn More <span class="arrow">→</span></a>
                </div>
                
                <!-- UI/UX Design Card -->
                <div class="service-card fade-in">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3H6a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3V6a3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 3 3 0 0 0-3-3z"></path>
                        </svg>
                    </div>
                    <h3>UI/UX Design</h3>
                    <p>Intuitive interfaces and engaging user experiences that delight users.</p>
                    <a href="#ui-ux-design" class="card-link">Learn More <span class="arrow">→</span></a>
                </div>
                
                <!-- Lua Development Card -->
                <div class="service-card fade-in">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <h3>Lua Development</h3>
                    <p>Custom Lua scripting for game modifications and specialized functionality.</p>
                    <a href="#lua-development" class="card-link">Learn More <span class="arrow">→</span></a>
                </div>
                
                <!-- GMod Addons Card -->
                <div class="service-card fade-in">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>GMod Addons</h3>
                    <p>Custom content for Garry's Mod to enhance gameplay and server functionality.</p>
                    <a href="#gmod-addons" class="card-link">Learn More <span class="arrow">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Services Sections -->
    <section class="services-detailed">
        <div class="container">
            <!-- Web Development -->
            <div id="web-development" class="service-detailed">
                <div class="service-image fade-in">
                    <img src="{{ asset('images/laravel_development.png') }}" alt="Web Development" class="rounded-image">
                    <div class="image-pattern"></div>
                </div>
                <div class="service-info slide-in-right">
                    <div class="service-tag">Web Services</div>
                    <h2>Web Development</h2>
                    <p class="service-intro">We build custom web applications and websites using modern frameworks and best practices.</p>
                    
                    <div class="service-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Laravel Development</h4>
                                <p>Scalable applications with Laravel's robust framework.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Front-end Development</h4>
                                <p>Responsive interfaces with modern JavaScript and custom SCSS.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>E-commerce Solutions</h4>
                                <p>Custom online stores with payment integration and inventory management.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('contact') }}?service=web" class="btn btn-primary">Request Web Development</a>
                </div>
            </div>
            
            <!-- UI/UX Design -->
            <div id="ui-ux-design" class="service-detailed reverse">
                <div class="service-image fade-in">
                    <img src="{{ asset('images/uiux.png') }}" alt="UI/UX Design" class="rounded-image">
                    <div class="image-pattern"></div>
                </div>
                <div class="service-info slide-in-left">
                    <div class="service-tag">Design Services</div>
                    <h2>UI/UX Design</h2>
                    <p class="service-intro">We create intuitive, engaging user experiences that align with your brand.</p>
                    
                    <div class="service-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>User Interface Design</h4>
                                <p>Visually stunning interfaces that reflect your brand identity.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>User Experience Strategy</h4>
                                <p>Research-driven approaches to ensure intuitive, functional designs.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Wireframing & Prototyping</h4>
                                <p>Interactive prototypes that visualize the final product before development.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('contact') }}?service=design" class="btn btn-primary">Request UI/UX Services</a>
                </div>
            </div>
            
            <!-- Lua Development -->
            <div id="lua-development" class="service-detailed">
                <div class="service-image fade-in">
                    <img src="{{ asset('images/lua.jpg') }}" alt="Lua Development" class="rounded-image">
                    <div class="image-pattern"></div>
                </div>
                <div class="service-info slide-in-right">
                    <div class="service-tag">Game Development</div>
                    <h2>Lua Development</h2>
                    <p class="service-intro">Custom Lua programming for game modifications with optimized performance.</p>
                    
                    <div class="service-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Game Scripting</h4>
                                <p>Specialized Lua scripting for various game engines and platforms.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Performance Optimization</h4>
                                <p>Code optimization for better resource management and smooth gameplay.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Custom Libraries</h4>
                                <p>Reusable Lua libraries and frameworks for consistent, maintainable code.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('contact') }}?service=lua" class="btn btn-primary">Request Lua Development</a>
                </div>
            </div>
            
            <!-- GMod Addons -->
            <div id="gmod-addons" class="service-detailed reverse">
                <div class="service-image fade-in">
                    <img src="{{ asset('images/addons.png') }}" alt="GMod Addons" class="rounded-image">
                    <div class="image-pattern"></div>
                </div>
                <div class="service-info slide-in-left">
                    <div class="service-tag">GMod Services</div>
                    <h2>GMod Addons</h2>
                    <p class="service-intro">Custom content creation for Garry's Mod that enhances server functionality.</p>
                    
                    <div class="service-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Gamemode Development</h4>
                                <p>Custom GMod gamemodes from roleplay scenarios to competitive challenges.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Server Systems</h4>
                                <p>Comprehensive server systems including economy, inventory, and admin tools.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h4>Custom Entities & Weapons</h4>
                                <p>Unique entities, weapons, and tools for your Garry's Mod server.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('contact') }}?service=gmod" class="btn btn-primary">Request GMod Services</a>
                </div>
            </div>
        </div>
    </section>

        <!-- Testimonials -->
        <section class="testimonials">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="slide-in-bottom">What Our Clients Say</h2>
                    <p class="text-muted slide-in-bottom">Feedback from those who've worked with us</p>
                </div>
                
                <div class="testimonial-slider">
                    @foreach($testimonials as $testimonial)
                        <div class="testimonial-item fade-in">
                            <div class="testimonial-content">
                                <p>"{{ $testimonial->testimonial_text }}"</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="{{ asset('storage/testimonials/' . $testimonial->image) }}" alt="{{ $testimonial->client_name }}" class="author-image">
                                <div class="author-info">
                                    <h4>{{ $testimonial->client_name }}</h4>
                                    <p>{{ $testimonial->company }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="testimonial-controls">
                    <button class="testimonial-prev" aria-label="Previous testimonial">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <div class="testimonial-dots">
                        @foreach($testimonials as $index => $testimonial)
                            <button class="dot {{ $loop->first ? 'active' : '' }}" aria-label="Testimonial {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    <button class="testimonial-next" aria-label="Next testimonial">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </section>


    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content fade-in">
                <h2>Ready to start your project?</h2>
                <p>Contact us today to discuss how we can help bring your ideas to life.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Get in Touch</a>
            </div>
        </div>
    </section>
@endsection