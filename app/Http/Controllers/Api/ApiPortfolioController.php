<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;

class ApiPortfolioController extends Controller
{
    /**
     * Get a single portfolio project's details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $project = PortfolioProject::findOrFail($id);
            
            // If the project is a draft, only allow access for admins
            if ($project->status === 'draft' && (!auth()->check() || !auth()->user()->is_admin)) {
                return response()->json(['error' => 'Project not found'], 404);
            }
            
            return response()->json($project);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Project not found'], 404);
        }
    }
}