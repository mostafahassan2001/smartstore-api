<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Get all banners with pagination
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $pageNumber = $request->input('pageNumber', 1);

        $banners = Banner::paginate($pageSize, ['*'], 'page', $pageNumber);

        return response()->json([
            'pageNumber' => $banners->currentPage(),
            'pageSize'   => $banners->perPage(),
            'total'      => $banners->total(),
            'data'       => $banners->items(),
        ]);
    }

    /**
     * Get a single banner
     */
    public function show($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        return response()->json($banner);
    }

    /**
     * Store a new banner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string',
            'title_ar'      => 'required|string',
            'description'   => 'required|string',
            'description_ar'=> 'required|string',
            'image'         => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
            'link_url'      => 'nullable|url',
            'order'         => 'nullable|integer',
            'is_active'     => 'boolean',
        ]);

        $validated['image'] = $request->file('image')->store('banners', 'public');

        $banner = Banner::create($validated);
        return response()->json($banner, 201);
    }

    /**
     * Update a banner
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        $validated = $request->validate([
            'title'         => 'required|string',
            'title_ar'      => 'required|string',
            'description'   => 'required|string',
            'description_ar'=> 'required|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'link_url'      => 'nullable|url',
            'order'         => 'nullable|integer',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($validated);
        return response()->json($banner);
    }

    /**
     * Delete a banner
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
        return response()->json(['message' => 'Banner deleted']);
    }
}
