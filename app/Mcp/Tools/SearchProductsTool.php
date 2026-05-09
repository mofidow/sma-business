<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use App\Models\Sma\Product\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly(true)]
class SearchProductsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Search and browse products with flexible filtering options.
        Returns a list of products matching your criteria. Use GetProductTool to get detailed information about a specific product.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'search'    => 'nullable|string',
            'type'      => 'nullable|string|in:Standard,Combo,Service,Digital,Recipe',
            'category'  => 'nullable|string',
            'brand'     => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'in_stock'  => 'nullable|boolean',
            'featured'  => 'nullable|boolean',
            'on_sale'   => 'nullable|boolean',
            'limit'     => 'nullable|integer|min:1|max:100',
        ]);

        $query = Product::query()
            ->with(['brand:id,name', 'category:id,name', 'unit:id,name,code', 'stocks', 'validPromotions'])
            ->withCount('validPromotions as promotions_count')
            ->where('active', true);

        // Search by name, code, or description
        if ($search = $validated['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $validated['type'] ?? null) {
            $query->where('type', $type);
        }

        // Filter by category name
        if ($category = $validated['category'] ?? null) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', 'like', "%{$category}%");
            });
        }

        // Filter by brand name
        if ($brand = $validated['brand'] ?? null) {
            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('name', 'like', "%{$brand}%");
            });
        }

        // Filter by price range
        if (isset($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }
        if (isset($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        // Filter by stock availability
        if (isset($validated['in_stock']) && $validated['in_stock']) {
            $query->whereHas('stocks');
        }

        // Filter by featured
        if (isset($validated['featured'])) {
            $query->where('featured', $validated['featured']);
        }

        // Filter by on sale
        if (isset($validated['on_sale'])) {
            $query->has('validPromotions');
        }

        $limit = $validated['limit'] ?? 20;
        $products = $query->take($limit)->get();

        if ($products->isEmpty()) {
            return Response::text('No products found matching your criteria.');
        }

        $output = "**Found {$products->count()} product(s)";
        if ($products->count() >= $limit) {
            $output .= " (showing first {$limit})";
        }
        $output .= ":**\n\n";

        foreach ($products as $product) {
            $output .= "---\n\n";
            $output .= "**{$product->name}**\n";
            $output .= "- ID: {$product->id}\n";
            $output .= "- Code: {$product->code}\n";
            $output .= "- Type: {$product->type}\n";
            $output .= '- Price: ' . number_format($product->price, 2) . "\n";
            $output .= '- Stock: ' . ($product->stocks->sum(fn ($stock) => $stock->balance) ?: 0) . "\n";

            if ($product->brand) {
                $output .= "- Brand: {$product->brand->name}\n";
            }
            if ($product->category) {
                $output .= "- Category: {$product->category->name}\n";
            }

            if ($product->description) {
                $output .= "- Description: {$product->description}\n";
            }

            if ($product->featured) {
                $output .= "- ⭐ Featured Product\n";
            }
            if ($product->promotions_count > 0) {
                $output .= "- 🏷️ On Sale\n";
            }

            $output .= "\n";
        }

        $output .= "\n*Use GetProductTool with product ID or code to see full details.*";

        return Response::text($output);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('Search term to match against product name, code, or description'),
            'type' => $schema->string()->enum(['Standard', 'Combo', 'Service', 'Digital', 'Recipe'])
                ->description('Filter by product type'),
            'category' => $schema->string()
                ->description('Filter by category name (partial match supported)'),
            'brand' => $schema->string()
                ->description('Filter by brand name (partial match supported)'),
            'min_price' => $schema->number()
                ->description('Minimum price filter'),
            'max_price' => $schema->number()
                ->description('Maximum price filter'),
            'in_stock' => $schema->boolean()
                ->description('Filter by stock availability (true for in-stock products only)'),
            'featured' => $schema->boolean()
                ->description('Filter by featured status'),
            'on_sale' => $schema->boolean()
                ->description('Filter by sale status'),
            'limit' => $schema->integer()
                ->description('Maximum number of results to return (1-100, default: 20)'),
        ];
    }
}
