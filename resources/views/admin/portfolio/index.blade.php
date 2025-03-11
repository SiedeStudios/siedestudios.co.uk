@extends('layouts.admin')

@section('title', 'Portfolio Projects')

@section('header-title', 'Portfolio Projects')

@section('content')
    <div class="title-bar">
        <div class="title-bar-left">
            <h1>Portfolio Projects</h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Portfolio</span>
            </div>
        </div>
        <div class="title-bar-actions">
            <a href="{{ route('admin.portfolio.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Project
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.portfolio.index') }}" method="GET" class="search-filter-container">
                <div class="search-input">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search projects..." aria-label="Search">
                </div>
                
                <select name="category" class="filter-select" aria-label="Filter by category">
                    <option value="all">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
                
                <select name="status" class="filter-select" aria-label="Filter by status">
                    <option value="all">All Statuses</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                
                @if(request('search') || request('category') != 'all' || request('status') != 'all')
                    <a href="{{ route('admin.portfolio.index') }}" class="btn btn-outline">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Projects List -->
    <div class="admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="sortable-table" data-url="{{ route('admin.portfolio.updateOrder') }}">
                        @forelse($projects as $project)
                            <tr data-id="{{ $project->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($project->image)
                                            <div style="width: 48px; height: 48px; border-radius: var(--border-radius-md); overflow: hidden; margin-right: 0.75rem;">
                                                <img src="{{ asset('storage/portfolio/' . $project->image) }}" alt="{{ $project->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-medium">{{ $project->title }}</span>
                                            <div class="text-muted" style="font-size: 0.875rem;">
                                                {{ Str::limit($project->description, 60) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $project->category }}</td>
                                <td>{{ $project->client_name ?? '-' }}</td>
                                <td>
                                    <div class="status-dropdown">
                                        <button type="button" class="dropdown-toggle status-{{ $project->status }}">
                                            <span>{{ ucfirst($project->status) }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </button>
                                        <div class="status-dropdown-menu">
                                            <a href="{{ route('admin.portfolio.changeStatus', [$project->id, 'published']) }}" class="dropdown-item {{ $project->status == 'published' ? 'active' : '' }}">
                                                Published
                                            </a>
                                            <a href="{{ route('admin.portfolio.changeStatus', [$project->id, 'draft']) }}" class="dropdown-item {{ $project->status == 'draft' ? 'active' : '' }}">
                                                Draft
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $project->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <span class="sort-handle action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="21" y1="10" x2="3" y2="10"></line>
                                                <line x1="21" y1="6" x2="3" y2="6"></line>
                                                <line x1="21" y1="14" x2="3" y2="14"></line>
                                                <line x1="21" y1="18" x2="3" y2="18"></line>
                                            </svg>
                                        </span>
                                        
                                        <a href="{{ route('admin.portfolio.edit', $project->id) }}" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"></path>
                                            </svg>
                                        </a>
                                        
                                        <a href="{{ $project->project_url }}" class="action-btn action-view" target="_blank" title="View Live" rel="noopener noreferrer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                        </a>
                                        
                                        <form action="{{ route('admin.portfolio.destroy', $project->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this project?')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 6h18"></path>
                                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="empty-state">
                                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                                        </svg>
                                        <h3 class="empty-state-title">No projects found</h3>
                                        <p class="empty-state-text">Get started by creating your first portfolio project.</p>
                                        <a href="{{ route('admin.portfolio.create') }}" class="btn btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            Add Project
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($projects->hasPages())
            <div class="card-footer">
                {{ $projects->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status dropdown toggle
        const statusDropdowns = document.querySelectorAll('.status-dropdown');
        statusDropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.status-dropdown')) {
                statusDropdowns.forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    });
</script>
@endsection