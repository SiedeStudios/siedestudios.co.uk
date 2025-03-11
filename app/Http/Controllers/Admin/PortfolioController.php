<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = PortfolioProject::query();

        // Handle search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Handle category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Handle status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Get projects with pagination
        $projects = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(10);

        // Get distinct categories for filter dropdown
        $categories = PortfolioProject::select('category')->distinct()->pluck('category');

        return view('admin.portfolio.index', compact('projects', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.portfolio.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'detailed_description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'client_name' => 'nullable|string|max:255',
            'project_duration' => 'nullable|string|max:100',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
            'project_url' => 'nullable|url|max:255',
            'status' => 'required|in:draft,published',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->storeAs('public/portfolio', $imageName);
                $validated['image'] = $imageName;
            }

            // Get the next order value
            $maxOrder = PortfolioProject::max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;

            PortfolioProject::create($validated);

            return redirect()->route('admin.portfolio.index')
                ->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create project: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create project. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PortfolioProject $portfolio
     * @return \Illuminate\View\View
     */
    public function edit(PortfolioProject $portfolio)
    {
        return view('admin.portfolio.form', compact('portfolio'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\PortfolioProject $portfolio
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PortfolioProject $portfolio)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'detailed_description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'client_name' => 'nullable|string|max:255',
            'project_duration' => 'nullable|string|max:100',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
            'project_url' => 'nullable|url|max:255',
            'status' => 'required|in:draft,published',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($portfolio->image) {
                    Storage::delete('public/portfolio/' . $portfolio->image);
                }

                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->storeAs('public/portfolio', $imageName);
                $validated['image'] = $imageName;
            }

            $portfolio->update($validated);

            return redirect()->route('admin.portfolio.index')
                ->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update project: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update project. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PortfolioProject $portfolio
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PortfolioProject $portfolio)
    {
        try {
            // Delete image if it exists
            if ($portfolio->image) {
                Storage::delete('public/portfolio/' . $portfolio->image);
            }

            $portfolio->delete();

            return redirect()->route('admin.portfolio.index')
                ->with('success', 'Project deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete project. Please try again.');
        }
    }

    /**
     * Update the order of portfolio projects.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|exists:portfolio_projects,id',
        ]);

        try {
            foreach ($request->items as $index => $id) {
                PortfolioProject::where('id', $id)->update(['order' => $index + 1]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to update order: ' . $e->getMessage());
            
            return response()->json(['success' => false, 'message' => 'Failed to update order'], 500);
        }
    }

    /**
     * Change the status of a portfolio project.
     *
     * @param \App\Models\PortfolioProject $portfolio
     * @param string $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus(PortfolioProject $portfolio, $status)
    {
        if (!in_array($status, ['draft', 'published'])) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        try {
            $portfolio->update(['status' => $status]);

            return redirect()->back()->with('success', 'Project status updated.');
        } catch (\Exception $e) {
            Log::error('Failed to update project status: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update project status. Please try again.');
        }
    }
}