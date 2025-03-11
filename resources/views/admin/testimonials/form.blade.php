@php
$isEdit = isset($testimonial);
$formTitle = $isEdit ? 'Edit Testimonial' : 'Add New Testimonial';
$submitButtonText = $isEdit ? 'Update Testimonial' : 'Create Testimonial';
$formAction = $isEdit ? route('admin.testimonials.update', $testimonial->id) : route('admin.testimonials.store');
@endphp

@extends('layouts.admin')

@section('title', $formTitle)

@section('header-title', $formTitle)

@section('content')
    <div class="title-bar">
        <div class="title-bar-left">
            <h1>{{ $formTitle }}</h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('admin.testimonials.index') }}">Testimonials</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ $isEdit ? 'Edit' : 'Create' }}</span>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body">
            <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="validate-form">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif
                
                <div class="form-row">
                    <!-- Client Name -->
                    <div class="form-group col-md-6">
                        <label for="client_name">Client Name <span class="required">*</span></label>
                        <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $isEdit ? $testimonial->client_name : '') }}" required>
                        @error('client_name')
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
                    
                    <!-- Company -->
                    <div class="form-group col-md-6">
                        <label for="company">Company</label>
                        <input type="text" name="company" id="company" value="{{ old('company', $isEdit ? $testimonial->company : '') }}">
                        @error('company')
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
                    <label for="testimonial_text">Testimonial <span class="required">*</span></label>
                    <textarea name="testimonial_text" id="testimonial_text" rows="5" required>{{ old('testimonial_text', $isEdit ? $testimonial->testimonial_text : '') }}</textarea>
                    @error('testimonial_text')
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
                
                <div class="form-row">
                    <!-- Client Photo -->
                    <div class="form-group col-md-6">
                        <label for="image">{{ $isEdit ? 'Change Client Photo' : 'Client Photo' }}</label>
                        <div class="file-input {{ $isEdit && $testimonial->image ? 'has-file' : '' }}">
                            <input type="file" name="image" id="image" accept="image/*">
                            <label for="image" class="file-input-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <div class="file-input-text" id="image-name">
                                    {{ $isEdit && $testimonial->image ? 'Current: ' . $testimonial->image : 'Choose a photo' }}
                                </div>
                                <div class="file-input-hint">
                                    Drag & drop or click to browse
                                </div>
                            </label>
                        </div>
                        @error('image')
                            <div class="form-error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">Recommended size: 400x400px. Supported formats: JPEG, PNG, JPG, GIF.</div>
                        
                        @if($isEdit && $testimonial->image)
                            <div class="image-preview" style="max-width: 150px;">
                                <img src="{{ asset('storage/testimonials/' . $testimonial->image) }}" alt="{{ $testimonial->client_name }}" style="border-radius: 50%;">
                            </div>
                        @endif
                    </div>
                    
                    <!-- Active Status -->
                    <div class="form-group col-md-6">
                        <label>Status</label>
                        <div class="toggle-switch-group">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $isEdit ? $testimonial->is_active : '1') ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                            <div class="toggle-switch-labels">
                                <span class="toggle-label">Active</span>
                                <span class="toggle-help">Controls visibility on the website</span>
                            </div>
                        </div>
                        @error('is_active')
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
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        {{ $submitButtonText }}
                    </button>
                    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline">
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
        // File input handling
        const imageInput = document.getElementById('image');
        const imageLabel = document.getElementById('image-name');
        
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    imageLabel.textContent = this.files[0].name;
                    this.closest('.file-input').classList.add('has-file');
                } else {
                    imageLabel.textContent = 'Choose a photo';
                    this.closest('.file-input').classList.remove('has-file');
                }
            });
        }
        
        // Toggle switch styling (add to your CSS if not already there)
        if (!document.getElementById('toggle-switch-styles')) {
            const style = document.createElement('style');
            style.id = 'toggle-switch-styles';
            style.textContent = `
                .toggle-switch-group {
                    display: flex;
                    align-items: center;
                    margin-top: 0.5rem;
                }
                
                .toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 48px;
                    height: 24px;
                    margin-right: 1rem;
                }
                
                .toggle-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                
                .switch-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: var(--color-text-muted);
                    transition: var(--transition-normal);
                    border-radius: var(--border-radius-full);
                }
                
                .switch-slider:before {
                    position: absolute;
                    content: "";
                    height: 18px;
                    width: 18px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: var(--transition-normal);
                    border-radius: 50%;
                }
                
                input:checked + .switch-slider {
                    background-color: var(--color-primary);
                }
                
                input:checked + .switch-slider:before {
                    transform: translateX(24px);
                }
                
                .toggle-switch-labels {
                    display: flex;
                    flex-direction: column;
                }
                
                .toggle-label {
                    font-weight: 500;
                }
                
                .toggle-help {
                    font-size: 0.875rem;
                    color: var(--color-text-muted);
                }
            `;
            document.head.appendChild(style);
        }
    });
</script>
@endsection