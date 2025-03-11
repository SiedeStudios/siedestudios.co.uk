@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="portfolio-hero">
        <div class="container">
            <div class="portfolio-hero-content">
                <h1 class="slide-in-bottom">My Work</h1>
                <p class="lead slide-in-bottom">Showcasing a selection of my best projects and client work.</p>
            </div>
        </div>
    </section>

    <!-- Portfolio Filter -->
    <section class="portfolio-filter">
        <div class="container">
            <div class="filter-wrapper">
                <p class="filter-label">Filter by:</p>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    @foreach($categories as $category)
                        <button class="filter-btn" data-filter="{{ Str::slug($category) }}">{{ $category }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Grid -->
    <section class="portfolio-grid-section">
        <div class="container">
            @if($projects->count() > 0)
                <div class="portfolio-grid">
                    @foreach($projects as $project)
                        <div class="portfolio-item fade-in" data-category="{{ Str::slug($project->category) }}">
                            <div class="portfolio-image">
                                <img src="{{ asset('storage/portfolio/' . $project->image) }}" alt="{{ $project->title }}">
                            </div>
                            <div class="portfolio-overlay">
                                <div class="overlay-content">
                                    <h3>{{ $project->title }}</h3>
                                    <p>{{ Str::limit($project->description, 120) }}</p>
                                    <div class="project-tech">
                                        @if($project->technologies)
                                            @foreach($project->technologies as $tech)
                                                <span>{{ $tech }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                    <a href="#" class="btn btn-outline btn-sm view-project-btn" data-project="{{ $project->id }}">View Details</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-projects">
                    <p>No portfolio projects found. Check back soon for updates!</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Project Modal -->
    <div class="project-modal">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <button class="modal-close-btn" aria-label="Close modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="modal-content">
                <!-- Content will be loaded dynamically via JavaScript -->
                <div class="modal-loading">
                    <div class="spinner"></div>
                    <p>Loading project details...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content fade-in">
                <h2>Like what you see?</h2>
                <p>Let's discuss how I can help bring your project to life.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Start a Project</a>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Portfolio filtering
        const filterButtons = document.querySelectorAll('.filter-btn');
        const portfolioItems = document.querySelectorAll('.portfolio-item');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active state
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter items
                const filterValue = this.getAttribute('data-filter');
                
                portfolioItems.forEach(item => {
                    if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
        
        // Modal functionality
        const modal = document.querySelector('.project-modal');
        const modalContent = modal.querySelector('.modal-content');
        const closeBtn = modal.querySelector('.modal-close-btn');
        const modalBackdrop = modal.querySelector('.modal-backdrop');
        const viewButtons = document.querySelectorAll('.view-project-btn');
        
        function openModal(projectId) {
            // Show loading indicator
            modalContent.innerHTML = `
                <div class="modal-loading">
                    <div class="spinner"></div>
                    <p>Loading project details...</p>
                </div>
            `;
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Fetch project details with AJAX
            fetch(`/api/portfolio/${projectId}`)
                .then(response => response.json())
                .then(data => {
                    // Build technologies HTML
                    let techHtml = '';
                    if (data.technologies && data.technologies.length > 0) {
                        techHtml = data.technologies.map(tech => `<span class="tech-badge">${tech}</span>`).join('');
                    }
                    
                    // Format client and duration information
                    const clientInfo = data.client_name ? `<div class="metadata-item">
                        <h4>Client</h4>
                        <p>${data.client_name}</p>
                    </div>` : '';
                    
                    const durationInfo = data.project_duration ? `<div class="metadata-item">
                        <h4>Timeline</h4>
                        <p>${data.project_duration}</p>
                    </div>` : '';
                    
                    // Project URL button
                    const projectUrlBtn = data.project_url ? 
                        `<a href="${data.project_url}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Visit Project</a>` : 
                        '';
                    
                    modalContent.innerHTML = `
                        <div class="project-details">
                            <div class="project-images">
                                <img src="/storage/portfolio/${data.image}" alt="${data.title}">
                            </div>
                            <div class="project-info">
                                <h2>${data.title}</h2>
                                <div class="project-category">${data.category}</div>
                                
                                <div class="project-technologies">
                                    ${techHtml}
                                </div>
                                
                                <div class="project-metadata">
                                    ${clientInfo}
                                    ${durationInfo}
                                    <div class="metadata-item">
                                        <h4>Services</h4>
                                        <p>${data.category}</p>
                                    </div>
                                </div>
                                
                                <div class="project-description">
                                    <p>${data.description}</p>
                                </div>
                                
                                <div class="project-detailed-description">
                                    ${data.detailed_description || ''}
                                </div>
                                
                                <div class="project-cta">
                                    ${projectUrlBtn}
                                    <a href="{{ route('contact') }}" class="btn btn-outline">Discuss a Similar Project</a>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error loading project details:', error);
                    modalContent.innerHTML = `
                        <div class="error-message">
                            <p>Sorry, there was a problem loading the project details. Please try again.</p>
                            <button class="btn btn-primary retry-btn">Retry</button>
                        </div>
                    `;
                    
                    // Add retry functionality
                    modalContent.querySelector('.retry-btn').addEventListener('click', function() {
                        openModal(projectId);
                    });
                });
        }
        
        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        viewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const projectId = this.getAttribute('data-project');
                openModal(projectId);
            });
        });
        
        closeBtn.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', closeModal);
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    });
</script>
@endsection