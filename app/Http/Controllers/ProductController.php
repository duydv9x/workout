<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use JWTAuth;

class ProductController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index()
    {
        $product = $this->user
            ->products()
            ->get(['id', 'name', 'price', 'quantity'])
            ->toArray();
        return response()->json([
            'code' => 200,
            'data' => $product,
            'message' => '',
        ]);
    }

    public  function show($id)
    {
        $product = $this->user->product()->find($id);
        if (!$product) {
            return response()->json([
                'code' => 400,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found',
            ], 400);
        }

        return $product;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'price' => 'required|integer',
            'quantity' => 'required|integer'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->quantity = $request->quantity;

        if ($this->user->products()->save($product))
            return response()->json([
                'code' => 200,
                'data' => ['product' => $product],
                'message' => 'Product create success',
            ]);
        else
            return response()->json([
                'code' => 500,
                'message' => 'Sorry, product could not be added'
            ], 500);
    }

    public function update(Request $request, $id)
    {
        $product = $this->user->products()->find($id);

        if (!$product) {
            return response()->json([
                'code' => 400,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found',
                'data' => [],
            ], 400);
        }

        $updated = $product->fill($request->all())
            ->save();

        if ($updated) {
            return response()->json([
                'code' => 200,
                'data' => [],
                'message' => 'Product update success',
            ]);
        } else {
            return response()->json([
                'code' => 500,
                'message' => 'Sorry, product could not be updated',
                'data' => [],
            ], 500);
        }
    }

    public function destroy($id)
    {
        $product = $this->user->products()->find($id);

        if (!$product) {
            return response()->json([
                'code' => 400,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found'
            ], 400);
        }

        if ($product->delete()) {
            return response()->json([
                'code' => 200
            ]);
        } else {
            return response()->json([
                'code' => 500,
                'message' => 'Product could not be deleted'
            ], 500);
        }
    }
}
