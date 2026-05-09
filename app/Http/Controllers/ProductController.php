<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response {
        return Inertia::render('Products/Index', [
            'products' => Product::paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['required', 'decimal:10,2', 'min:0']
        ]);

        Product::create($validated);

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product): RedirectResponse {
        $validate = $request->validate([
            'name' => ['nullable', 'string'],
            'price' => ['nullable', 'decimal:10,2', 'min:0']
        ]);

        $product->update(array_filter($validate));

        return redirect()->route('products.index');
    }

    public function destroy(Product $product): RedirectResponse {
        $product->delete();
        
        return redirect()->route('products.index');
    }
}
