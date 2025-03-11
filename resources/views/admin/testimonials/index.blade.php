@extends('layouts.admin')

@section('title', 'Testimonials')

@section('header-title', 'Testimonials')

@section('content')
    <div class="title-bar">
        <div class="title-bar-left">
            <h1>Testimonials</h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Testimonials</span>
            </div>
        </div>
        <div class="title-bar-actions">
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Testimonial
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="admin-card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.testimonials.index') }}" method="GET" class="search-filter-container">
                <div class="search-input">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search testimonials..." aria-label="Search">
                </div>
                
                <select name="status" class="filter-select" aria-label="Filter by status">
                    <option value="all">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Hidden</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                
                @if(request('search') || request('status') != 'all')
                    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Testimonials List -->
    <div class="admin-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Company</th>
                            <th>Testimonial</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="sortable-table" data-url="{{ route('admin.testimonials.updateOrder') }}">
                        @forelse($testimonials as $testimonial)
                            <tr data-id="{{ $testimonial->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($testimonial->image)
                                            <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; margin-right: 0.75rem;">
                                                <img src="{{ asset('storage/testimonials/' . $testimonial->image) }}" alt="{{ $testimonial->client_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @else
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-weight: 600;">
                                                {{ substr($testimonial->client_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="font-medium">{{ $testimonial->client_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $testimonial->company ?? '-' }}</td>
                                <td class="testimonial-excerpt">{{ Str::limit($testimonial->testimonial_text, 100) }}</td>
                                <td>
                                    <div class="status-switch">
                                        <form action="{{ route('admin.testimonials.toggleStatus', $testimonial->id) }}" method="POST" class="status-toggle-form">
                                            @csrf
                                            @method('PATCH')
                                            <label class="switch">
                                                <input type="checkbox" class="status-toggle-switch" {{ $testimonial->is_active ? 'checked' : '' }}>
                                                <span class="switch-slider"></span>
                                            </label>
                                            <span class="status-label {{ $testimonial->is_active ? 'text-success' : 'text-muted' }}">
                                                {{ $testimonial->is_active ? 'Active' : 'Hidden' }}
                                            </span>
                                        </form>
                                    </div>
                                </td>
                                <td>{{ $testimonial->created_at->format('M d, Y') }}</td>
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
                                        
                                        <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"></path>
                                            </svg>
                                        </a>
                                        
                                        <form action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this testimonial?')">
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
                                            <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
                                        </svg>
                                        <h3 class="empty-state-title">No testimonials found</h3>
                                        <p class="empty-state-text">Get started by creating your first testimonial.</p>
                                        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            Add Testimonial
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($testimonials->hasPages())
            <div class="card-footer">
                {{ $testimonials->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status toggle switches
        const statusForms = document.querySelectorAll('.status-toggle-form');
        statusForms.forEach(form => {
            const toggle = form.querySelector('.status-toggle-switch');
            const statusLabel = form.querySelector('.status-label');
            
            toggle.addEventListener('change', function() {
                form.submit();
            });
        });
        
    });
</script>
@endsection