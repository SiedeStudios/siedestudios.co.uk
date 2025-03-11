@extends('layouts.admin')

@section('title', 'Settings')

@section('header-title', 'Site Settings')

@section('content')
    <div class="title-bar">
        <div class="title-bar-left">
            <h1>Settings</h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Settings</span>
            </div>
        </div>
    </div>

    <!-- Admin Tabs -->
    <div class="admin-tabs">
        <a href="#general" class="admin-tab active" data-tab="general">General</a>
        <a href="#contact" class="admin-tab" data-tab="contact">Contact</a>
        <a href="#social" class="admin-tab" data-tab="social">Social Media</a>
        <a href="#branding" class="admin-tab" data-tab="branding">Branding</a>
    </div>

    <!-- Settings Form -->
    <div class="admin-card">
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- General Settings -->
                <div class="tab-content active" id="general-content" data-tab-content="general">
                    <div class="settings-section">
                        <h3>General Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="site_title">Site Title <span class="required">*</span></label>
                                <input type="text" name="site_title" id="site_title" value="{{ old('site_title', $settings['site_title']) }}" required>
                                @error('site_title')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="site_description">Site Description</label>
                                <input type="text" name="site_description" id="site_description" value="{{ old('site_description', $settings['site_description']) }}">
                                @error('site_description')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords']) }}">
                            @error('meta_keywords')
                                <div class="form-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">Comma-separated keywords for SEO</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="google_analytics">Google Analytics ID</label>
                            <input type="text" name="google_analytics" id="google_analytics" value="{{ old('google_analytics', $settings['google_analytics']) }}" placeholder="G-XXXXXXXXXX">
                            @error('google_analytics')
                                <div class="form-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="tab-content" id="contact-content" data-tab-content="contact">
                    <div class="settings-section">
                        <h3>Contact Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="contact_email">Email Address</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                        </svg>
                                    </div>
                                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}">
                                </div>
                                @error('contact_email')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="contact_phone">Phone Number</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}">
                                </div>
                                @error('contact_phone')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="tab-content" id="social-content" data-tab-content="social">
                    <div class="settings-section">
                        <h3>Social Media Links</h3>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="social_twitter">Twitter URL</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                                        </svg>
                                    </div>
                                    <input type="url" name="social_twitter" id="social_twitter" value="{{ old('social_twitter', $settings['social_twitter']) }}" placeholder="https://twitter.com/username">
                                </div>
                                @error('social_twitter')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="social_facebook">Facebook URL</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                        </svg>
                                    </div>
                                    <input type="url" name="social_facebook" id="social_facebook" value="{{ old('social_facebook', $settings['social_facebook']) }}" placeholder="https://facebook.com/username">
                                </div>
                                @error('social_facebook')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="social_instagram">Instagram URL</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                        </svg>
                                    </div>
                                    <input type="url" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="https://instagram.com/username">
                                </div>
                                @error('social_instagram')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="social_github">GitHub URL</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"></path>
                                            <path d="M9 18c-4.51 2-5-2-7-2"></path>
                                        </svg>
                                    </div>
                                    <input type="url" name="social_github" id="social_github" value="{{ old('social_github', $settings['social_github']) }}" placeholder="https://github.com/username">
                                </div>
                                @error('social_github')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="social_linkedin">LinkedIn URL</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                            <rect width="4" height="12" x="2" y="9"></rect>
                                            <circle cx="4" cy="4" r="2"></circle>
                                        </svg>
                                    </div>
                                    <input type="url" name="social_linkedin" id="social_linkedin" value="{{ old('social_linkedin', $settings['social_linkedin']) }}" placeholder="https://linkedin.com/in/username">
                                </div>
                                @error('social_linkedin')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Branding -->
                <div class="tab-content" id="branding-content" data-tab-content="branding">
                    <div class="settings-section">
                        <h3>Logo & Favicon</h3>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Logo</label>
                                <div class="file-input {{ $settings['logo'] ? 'has-file' : '' }}">
                                    <input type="file" name="logo" id="logo" accept="image/*">
                                    <label for="logo" class="file-input-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <div class="file-input-text" id="logo-name">
                                            {{ $settings['logo'] ? 'Current: ' . $settings['logo'] : 'Choose a logo file' }}
                                        </div>
                                        <div class="file-input-hint">
                                            Drag & drop or click to browse
                                        </div>
                                    </label>
                                </div>
                                @error('logo')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">Recommended size: 200x60px. Supported formats: PNG, SVG</div>
                                
                                @if($settings['logo'])
                                    <div class="image-preview">
                                        <img src="{{ asset('storage/settings/' . $settings['logo']) }}" alt="Logo">
                                    </div>
                                @endif
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Favicon</label>
                                <div class="file-input {{ $settings['favicon'] ? 'has-file' : '' }}">
                                    <input type="file" name="favicon" id="favicon" accept=".ico,.png">
                                    <label for="favicon" class="file-input-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <div class="file-input-text" id="favicon-name">
                                            {{ $settings['favicon'] ? 'Current: ' . $settings['favicon'] : 'Choose a favicon file' }}
                                        </div>
                                        <div class="file-input-hint">
                                            Drag & drop or click to browse
                                        </div>
                                    </label>
                                </div>
                                @error('favicon')
                                    <div class="form-error">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">Recommended size: 32x32px. Supported formats: ICO, PNG</div>
                                
                                @if($settings['favicon'])
                                    <div class="image-preview">
                                        <img src="{{ asset('storage/settings/' . $settings['favicon']) }}" alt="Favicon">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save Settings
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 14 4 9l5-5"></path>
                            <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"></path>
                        </svg>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabs = document.querySelectorAll('.admin-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetTab = this.getAttribute('data-tab');
                
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding content
                tabContents.forEach(content => {
                    if (content.getAttribute('data-tab-content') === targetTab) {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });
                
                // Update URL hash
                window.location.hash = targetTab;
            });
        });
        
        // Check URL hash on page load
        const hash = window.location.hash.substring(1);
        if (hash) {
            const activeTab = document.querySelector(`.admin-tab[data-tab="${hash}"]`);
            if (activeTab) {
                activeTab.click();
            }
        }
        
        // File input handling
        const logoInput = document.getElementById('logo');
        const logoLabel = document.getElementById('logo-name');
        
        if (logoInput) {
            logoInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    logoLabel.textContent = this.files[0].name;
                    this.closest('.file-input').classList.add('has-file');
                } else {
                    logoLabel.textContent = 'Choose a logo file';
                    this.closest('.file-input').classList.remove('has-file');
                }
            });
        }
        
        // Favicon file input handling
        const faviconInput = document.getElementById('favicon');
        const faviconLabel = document.getElementById('favicon-name');
        
        if (faviconInput) {
            faviconInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    faviconLabel.textContent = this.files[0].name;
                    this.closest('.file-input').classList.add('has-file');
                } else {
                    faviconLabel.textContent = 'Choose a favicon file';
                    this.closest('.file-input').classList.remove('has-file');
                }
            });
        }
    });
</script>
@endsection