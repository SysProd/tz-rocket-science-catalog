<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('properties') && is_array($request->input('properties'))) {
            foreach ($request->input('properties') as $property => $values) {
                if (is_array($values) && !empty($values)) {
                    $query->filterByProperties([$property => $values]);
                }
            }
        }

        $products = $query->with(['properties', 'properties.values'])->paginate(40);

        // Форматируем ответ, чтобы включить свойства товара
        $formattedProducts = $products->map(function ($product) {
            $properties = $product->properties->mapWithKeys(function ($property) {
                $value = $property->pivot->property_value_id;
                return [$property->name => $property->values->where('id', $value)->first()->value];
            });

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'properties' => $properties,
            ];
        });

        $response = [
            'data' => $formattedProducts,
            'total' => $products->total(),
            'per_page' => $products->perPage(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ];

        return response()->json($response);

    }
}
