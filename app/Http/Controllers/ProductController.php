<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Products/Index', [
            'products' => Product::paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'show_in_prep_summary' => ['boolean'],
        ]);

        Product::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Produto criado com sucesso!',
        ]);

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validate = $request->validate([
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'show_in_prep_summary' => ['boolean'],
        ]);

        $product->update($validate);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Produto atualizado com sucesso!',
        ]);

        return redirect()->route('products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Produto excluído com sucesso!',
        ]);

        return redirect()->route('products.index');
    }
}
