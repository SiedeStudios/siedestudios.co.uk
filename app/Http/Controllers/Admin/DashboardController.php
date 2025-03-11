<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\PortfolioProject;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get stats for the dashboard
        $portfolioCount = PortfolioProject::count();
        $testimonialCount = Testimonial::count();
        $newMessagesCount = Message::new()->count();
        $draftProjectCount = PortfolioProject::draft()->count();

        // Get recent projects
        $recentProjects = PortfolioProject::latest()->take(5)->get();

        // Get recent testimonials
        $recentTestimonials = Testimonial::latest()->take(5)->get();
        
        // Get recent messages
        $recentMessages = Message::latest()->take(3)->get();

        return view('admin.dashboard', compact(
            'portfolioCount',
            'testimonialCount',
            'newMessagesCount',
            'draftProjectCount',
            'recentProjects',
            'recentTestimonials',
            'recentMessages'
        ));
    }
}