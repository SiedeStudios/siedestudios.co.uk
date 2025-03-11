@php
$isEdit = isset($portfolio);
$formTitle = $isEdit ? 'Edit Project: ' . $portfolio->title : 'Create New Project';
$submitButtonText = $isEdit ? 'Update Project' : 'Create Project';
$formAction = $isEdit ? route('admin.portfolio.update', $portfolio->id) : route('admin.portfolio.store');
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
                <a href="{{ route('admin.portfolio.index') }}">Portfolio</a>
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
                    <!-- Project Title -->
                    <div class="form-group col-md-8">
                        <label for="title">Project Title <span class="required">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $isEdit ? $portfolio->title : '') }}" required>
                        @error('title')
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
                    
                    <!-- Category -->
                    <div class="form-group col-md-4">
                        <label for="category">Category <span class="required">*</span></label>
                        <input type="text" name="category" id="category" value="{{ old('category', $isEdit ? $portfolio->category : '') }}" required>
                        @error('category')
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
                    <!-- Short Description -->
                    <div class="form-group col-full">
                        <label for="description">Short Description <span class="required">*</span></label>
                        <textarea name="description" id="description" rows="3" required>{{ old('description', $isEdit ? $portfolio->description : '') }}</textarea>
                        @error('description')
                            <div class="form-error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">A brief summary of the project (shown in lists)</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="detailed_description">Detailed Description</label>
                    <textarea name="detailed_description" id="detailed_description" rows="6">{{ old('detailed_description', $isEdit ? $portfolio->detailed_description : '') }}</textarea>
                    @error('detailed_description')
                        <div class="form-error">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                    <div class="form-text">Full description of the project (shown on project detail page)</div>
                </div>
                
                <div class="form-row">
                    <!-- Client Name -->
                    <div class="form-group col-md-6">
                        <label for="client_name">Client Name</label>
                        <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $isEdit ? $portfolio->client_name : '') }}">
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
                    
                    <!-- Project Duration -->
                    <div class="form-group col-md-6">
                        <label for="project_duration">Project Duration</label>
                        <input type="text" name="project_duration" id="project_duration" value="{{ old('project_duration', $isEdit ? $portfolio->project_duration : '') }}" placeholder="e.g. 3 months">
                        @error('project_duration')
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
                    <!-- Technologies -->
                    <div class="form-group col-md-6">
                        <label for="technologies">Technologies</label>
                        <div class="tags-input" id="technologies-container">
                            @if($isEdit && $portfolio->technologies)
                                @foreach($portfolio->technologies as $tech)
                                    <div class="tag">
                                        <span>{{ $tech }}</span>
                                        <span class="tag-remove">&times;</span>
                                        <input type="hidden" name="technologies[]" value="{{ $tech }}">
                                    </div>
                                @endforeach
                            @endif
                            <input type="text" id="technology-input" placeholder="Add technology and press Enter...">
                        </div>
                        @error('technologies')
                            <div class="form-error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">E.g., HTML, CSS, JavaScript, Laravel, etc.</div>
                    </div>
                    
                    <!-- Project URL -->
                    <div class="form-group col-md-6">
                        <label for="project_url">Project URL</label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" y1="12" x2="22" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                            </div>
                            <input type="url" name="project_url" id="project_url" value="{{ old('project_url', $isEdit ? $portfolio->project_url : '') }}" placeholder="https://">
                        </div>
                        @error('project_url')
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
                    <!-- Project Image -->
                    <div class="form-group col-md-6">
                        <label for="image">{{ $isEdit ? 'Change Project Image' : 'Project Image' }} {{ $isEdit ? '' : '<span class="required">*</span>' }}</label>
                        <div class="file-input {{ $isEdit && $portfolio->image ? 'has-file' : '' }}">
                            <input type="file" name="image" id="image" accept="image/*" {{ $isEdit ? '' : 'required' }}>
                            <label for="image" class="file-input-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <div class="file-input-text" id="image-name">
                                    {{ $isEdit && $portfolio->image ? 'Current: ' . $portfolio->image : 'Choose an image file' }}
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
                        <div class="form-text">Recommended size: 1200x800px. Supported formats: JPEG, PNG, JPG, GIF.</div>
                        
                        @if($isEdit && $portfolio->image)
                            <div class="image-preview">
                                <img src="{{ asset('storage/portfolio/' . $portfolio->image) }}" alt="{{ $portfolio->title }}">
                            </div>
                        @endif
                    </div>
                    
                    <!-- Project Status -->
                    <div class="form-group col-md-6">
                        <label>Project Status <span class="required">*</span></label>
                        <div class="status-radio-group">
                            <label class="status-radio">
                                <input type="radio" name="status" value="published" {{ old('status', $isEdit ? $portfolio->status : '') == 'published' ? 'checked' : '' }} required>
                                <span class="radio-label">Published</span>
                                <span class="radio-help">Project will be visible on the website</span>
                            </label>
                            
                            <label class="status-radio">
                                <input type="radio" name="status" value="draft" {{ old('status', $isEdit ? $portfolio->status : '') == 'draft' ? 'checked' : '' }} required>
                                <span class="radio-label">Draft</span>
                                <span class="radio-help">Project will not be visible on the website</span>
                            </label>
                        </div>
                        @error('status')
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
                    <a href="{{ route('admin.portfolio.index') }}" class="btn btn-outline">
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
                    imageLabel.textContent = 'Choose an image file';
                    this.closest('.file-input').classList.remove('has-file');
                }
            });
        }
        
        // Technologies tags input
        const techContainer = document.getElementById('technologies-container');
        const techInput = document.getElementById('technology-input');
        
        if (techContainer && techInput) {
            // Add tag when pressing Enter
            techInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    
                    const value = this.value.trim();
                    if (value) {
                        addTag(value);
                        this.value = '';
                    }
                }
            });
            
            // Remove tag when clicking X
            techContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('tag-remove')) {
                    e.target.parentElement.remove();
                }
            });
            
            // Function to add a new tag
            function addTag(text) {
                const tag = document.createElement('div');
                tag.className = 'tag';
                tag.innerHTML = `
                    <span>${text}</span>
                    <span class="tag-remove">&times;</span>
                    <input type="hidden" name="technologies[]" value="${text}">
                `;
                techContainer.insertBefore(tag, techInput);
            }
        }
    });
</script>
@endsection