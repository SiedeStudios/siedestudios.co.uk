@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="contact-hero-content">
                <h1 class="slide-in-bottom">Get in Touch</h1>
                <p class="lead slide-in-bottom">We'd love to hear from you. Let's start a conversation.</p>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info slide-in-left">
                    <h2>Contact Information</h2>
                    <p>Feel free to reach out to us with any questions, project inquiries, or just to say hello. We're always happy to chat about potential collaborations.</p>
                    
                    <div class="contact-details">
                        <div class="contact-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div class="detail-text">
                                <h3>Phone</h3>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="detail-text">
                                <h3>Email</h3>
                                <p>info@siedestudios.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="detail-text">
                                <h3>Office</h3>
                                <p>123 Web Development St.<br>Digital City, CA 90210</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h3>Connect With Us</h3>
                        <div class="social-icons">
                            <a href="https://twitter.com/siedestudios" aria-label="Twitter" target="_blank" rel="noopener">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                                </svg>
                            </a>
                            <a href="https://github.com/siedestudios" aria-label="GitHub" target="_blank" rel="noopener">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                                </svg>
                            </a>
                            <a href="https://discord.gg/siedestudios" aria-label="Discord" target="_blank" rel="noopener">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 18a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12Z"></path>
                                    <path d="M7 10.5v-1c0-.3.3-.5.6-.5h.8c.3 0 .6.2.6.5v1c0 .3-.3.5-.6.5h-.8c-.3 0-.6-.2-.6-.5Z"></path>
                                    <path d="M15 10.5v-1c0-.3.3-.5.6-.5h.8c.3 0 .6.2.6.5v1c0 .3-.3.5-.6.5h-.8c-.3 0-.6-.2-.6-.5Z"></path>
                                    <path d="M8 15h8"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form-container slide-in-right">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <form class="contact-form" method="POST" action="{{ route('contact.submit') }}">
                        @csrf
                        <h2>Send Us a Message</h2>
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" required>{{ old('message') }}</textarea>
                            @error('message')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection