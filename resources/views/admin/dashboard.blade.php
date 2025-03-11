@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header-title', 'Dashboard')

@section('content')
    <!-- Overview Section -->
    <div class="title-bar">
        <div class="title-bar-left">
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-muted">Here's what's happening with your portfolio today.</p>
        </div>
        <div class="title-bar-actions">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                    <path d="m2 12 5 5a8 8 0 0 0 6 2.5c5 .3 9-4 9-9a9 9 0 0 0-9-9 9 9 0 0 0-9 9"/>
                    <path d="m12 16 4-4"/>
                    <path d="m8 8 4 4"/>
                </svg>
                <span>View Website</span>
            </a>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $portfolioCount ?? 0 }}</div>
                <div class="stat-label">Portfolio Projects</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon icon-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $testimonialCount ?? 0 }}</div>
                <div class="stat-label">Testimonials</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon icon-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $newMessagesCount ?? 0 }}</div>
                <div class="stat-label">New Messages</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon icon-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $draftProjectCount ?? 0 }}</div>
                <div class="stat-label">Draft Projects</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="admin-card">
        <div class="card-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <a href="{{ route('admin.portfolio.create') }}" class="quick-action-item">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path>
                            <line x1="12" y1="10" x2="12" y2="16"></line>
                            <line x1="9" y1="13" x2="15" y2="13"></line>
                        </svg>
                    </div>
                    <div class="action-text">Add Project</div>
                </a>
                
                <a href="{{ route('admin.testimonials.create') }}" class="quick-action-item">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </div>
                    <div class="action-text">Add Testimonial</div>
                </a>
                
                <a href="{{ route('admin.messages.index') }}" class="quick-action-item">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </div>
                    <div class="action-text">Check Messages</div>
                </a>
                
                <a href="{{ route('admin.settings') }}" class="quick-action-item">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div class="action-text">Settings</div>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Projects -->
    <div class="admin-card">
        <div class="card-header">
            <h2>Recent Projects</h2>
            <div class="card-actions">
                <a href="{{ route('admin.portfolio.index') }}" class="btn btn-sm btn-outline">
                    <span>View All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProjects ?? [] as $project)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($project->image)
                                            <div style="width: 36px; height: 36px; border-radius: var(--border-radius-md); overflow: hidden; margin-right: 0.75rem;">
                                                <img src="{{ asset('storage/portfolio/' . $project->image) }}" alt="{{ $project->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @endif
                                        <span class="font-medium">{{ $project->title }}</span>
                                    </div>
                                </td>
                                <td>{{ $project->category }}</td>
                                <td>
                                    @if($project->status == 'published')
                                        <span class="status-badge status-success">Published</span>
                                    @else
                                        <span class="status-badge status-warning">Draft</span>
                                    @endif
                                </td>
                                <td>{{ $project->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.portfolio.edit', $project->id) }}" class="action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.portfolio.destroy', $project->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-delete" onclick="return confirm('Are you sure you want to delete this project?')">
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
                                <td colspan="5" class="text-center">
                                    <div class="py-4 text-muted">No recent projects found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Testimonials -->
    <div class="admin-card">
        <div class="card-header">
            <h2>Recent Testimonials</h2>
            <div class="card-actions">
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-sm btn-outline">
                    <span>View All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTestimonials ?? [] as $testimonial)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($testimonial->image)
                                            <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden; margin-right: 0.75rem;">
                                                <img src="{{ asset('storage/testimonials/' . $testimonial->image) }}" alt="{{ $testimonial->client_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        @else
                                            <div style="width: 36px; height: 36px; border-radius: 50%; background-color: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; font-weight: 600;">
                                                {{ substr($testimonial->client_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="font-medium">{{ $testimonial->client_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $testimonial->company }}</td>
                                <td>
                                    @if($testimonial->is_active)
                                        <span class="status-badge status-success">Active</span>
                                    @else
                                        <span class="status-badge status-muted">Hidden</span>
                                    @endif
                                </td>
                                <td>{{ $testimonial->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="action-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-delete" onclick="return confirm('Are you sure you want to delete this testimonial?')">
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
                                <td colspan="5" class="text-center">
                                    <div class="py-4 text-muted">No recent testimonials found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Messages Preview -->
    @if(isset($recentMessages) && count($recentMessages) > 0)
    <div class="admin-card">
        <div class="card-header">
            <h2>Recent Messages</h2>
            <div class="card-actions">
                <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-outline">
                    <span>View All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="message-list">
                @foreach($recentMessages as $message)
                <div class="message-preview">
                    <div class="message-header">
                        <div class="message-sender">
                            <div class="sender-avatar">{{ substr($message->name, 0, 1) }}</div>
                            <div class="sender-info">
                                <div class="sender-name">{{ $message->name }}</div>
                                <div class="sender-email">{{ $message->email }}</div>
                            </div>
                        </div>
                        <div class="message-meta">
                            <div class="message-date">{{ $message->created_at->format('M d, Y') }}</div>
                            <div class="message-status">
                                @if($message->status == 'new')
                                    <span class="status-badge status-primary">New</span>
                                @elseif($message->status == 'read')
                                    <span class="status-badge status-muted">Read</span>
                                @elseif($message->status == 'replied')
                                    <span class="status-badge status-success">Replied</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="message-subject">{{ $message->subject }}</div>
                    <div class="message-excerpt">{{ Str::limit($message->message, 120) }}</div>
                    <div class="message-actions">
                        <a href="{{ route('admin.messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary">View Message</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@endsection