<?php

namespace App\Http\Controllers;

use App\Models\PortfolioProject;
use App\Models\Testimonial;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
// Uncomment and create this mail class when ready
// use App\Mail\ContactFormMail;

class PageController extends Controller
{
    /**
     * Display the home page
     */
    public function home()
    {
        // Get featured projects for the homepage
        $featuredProjects = PortfolioProject::where('status', 'published')
            ->orderBy('order')
            ->take(3)
            ->get();
            
        // Get testimonials for the homepage
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('order')
            ->take(4)
            ->get();
            
        return view('index', compact('featuredProjects', 'testimonials'));
    }

    /**
     * Display the about page
     */
    public function about()
    {
            
        return view('about');
    }

    /**
     * Display the services page
     */
    public function services()
    {
        // Get testimonials for the services page
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('order')
            ->take(3)
            ->get();

        return view('services', compact('testimonials'));
    }

    /**
     * Display the portfolio page
     */
    public function portfolio()
    {
        // Get all published projects
        $projects = PortfolioProject::where('status', 'published')
            ->orderBy('order')
            ->get();
            
        // Get unique categories for filtering
        $categories = $projects->pluck('category')->unique();
        
        return view('portfolio', compact('projects', 'categories'));
    }
    
    /**
     * Display a single portfolio project
     */
    public function portfolioShow($id)
    {
        $project = PortfolioProject::findOrFail($id);
        
        // If the project is a draft, only show it to admins
        if ($project->status === 'draft' && (!auth()->check() || !auth()->user()->is_admin)) {
            abort(404);
        }
        
        // Get related projects in the same category
        $relatedProjects = PortfolioProject::where('status', 'published')
            ->where('id', '!=', $project->id)
            ->where('category', $project->category)
            ->orderBy('order')
            ->take(3)
            ->get();
            
        return view('portfolio-single', compact('project', 'relatedProjects'));
    }

    /**
     * Display the contact page
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Process the contact form submission
     */
    public function contactSubmit(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Store message in database
            $message = Message::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'], 
                'message' => $validated['message'],
                'status' => 'new'
            ]);
            
            // Uncomment when ready to send emails
            // Mail::to('contact@siedestudios.com')->send(new ContactFormMail($validated));

            // Redirect back with success message
            return redirect()->route('contact')->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to process contact form: ' . $e->getMessage());
            
            // Redirect with error message
            return redirect()->route('contact')
                ->withInput()
                ->with('error', 'Sorry, there was a problem submitting your message. Please try again later.');
        }
    }

    /**
     * Display the privacy policy page
     */
    public function privacy()
    {
        return view('privacy');
    }

    /**
     * Display the terms of service page
     */
    public function terms()
    {
        return view('terms');
    }
}