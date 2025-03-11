<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Testimonial::query();

        // Handle search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('testimonial_text', 'like', "%{$search}%");
            });
        }

        // Handle status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Get testimonials with pagination
        $testimonials = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.testimonials.form');
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
            'client_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial_text' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->storeAs('public/testimonials', $imageName);
                $validated['image'] = $imageName;
            }

            // Set is_active to false if not in request
            $validated['is_active'] = $request->has('is_active');

            // Get the next order value
            $maxOrder = Testimonial::max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;

            Testimonial::create($validated);

            return redirect()->route('admin.testimonials.index')
                ->with('success', 'Testimonial created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create testimonial: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create testimonial. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Testimonial $testimonial
     * @return \Illuminate\View\View
     */
    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Testimonial $testimonial
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial_text' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($testimonial->image) {
                    Storage::delete('public/testimonials/' . $testimonial->image);
                }

                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->storeAs('public/testimonials', $imageName);
                $validated['image'] = $imageName;
            }

            // Set is_active to false if not in request
            $validated['is_active'] = $request->has('is_active');

            $testimonial->update($validated);

            return redirect()->route('admin.testimonials.index')
                ->with('success', 'Testimonial updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update testimonial: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update testimonial. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Testimonial $testimonial
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Testimonial $testimonial)
    {
        try {
            // Delete image if it exists
            if ($testimonial->image) {
                Storage::delete('public/testimonials/' . $testimonial->image);
            }

            $testimonial->delete();

            return redirect()->route('admin.testimonials.index')
                ->with('success', 'Testimonial deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete testimonial: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete testimonial. Please try again.');
        }
    }

    /**
     * Update the order of testimonials.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|exists:testimonials,id',
        ]);

        try {
            foreach ($request->items as $index => $id) {
                Testimonial::where('id', $id)->update(['order' => $index + 1]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to update order: ' . $e->getMessage());
            
            return response()->json(['success' => false, 'message' => 'Failed to update order'], 500);
        }
    }

    /**
     * Toggle the active status of a testimonial.
     *
     * @param \App\Models\Testimonial $testimonial
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Testimonial $testimonial)
    {
        try {
            $testimonial->update([
                'is_active' => !$testimonial->is_active,
            ]);

            return redirect()->back()->with('success', 'Testimonial status updated.');
        } catch (\Exception $e) {
            Log::error('Failed to toggle testimonial status: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update testimonial status. Please try again.');
        }
    }
}