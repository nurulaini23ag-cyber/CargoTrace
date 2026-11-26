<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the Commodity Catalog (Cocoa & Coffee).
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'originCountry']);

        // Filter by category slug or name
        if ($request->filled('category')) {
            $catSlug = strtolower($request->category);
            $query->whereHas('category', function ($q) use ($catSlug) {
                $q->where('slug', $catSlug)->orWhere('name', 'like', "%{$catSlug}%");
            });
        }

        // Filter by origin country
        if ($request->filled('country_id')) {
            $query->where('origin_country_id', $request->country_id);
        }

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        // Keyword Search
        if ($request->filled('q')) {
            $kw = '%' . trim($request->q) . '%';
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', $kw)
                  ->orWhere('description', 'like', $kw)
                  ->orWhere('grade', 'like', $kw);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'featured');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            $query->orderByDesc('is_featured')->orderBy('name', 'asc');
        }

        $products = $query->paginate(9)->withQueryString();
        $categories = Category::all();
        $countries = Country::has('products')->get();

        return view('catalog.index', compact('products', 'categories', 'countries'));
    }

    /**
     * Show detailed product view with laboratory quality specs & ordering CTA.
     */
    public function show(string $slug): View
    {
        $product = Product::with(['category', 'originCountry'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(3)
            ->get();

        return view('catalog.show', compact('product', 'relatedProducts'));
    }
}
