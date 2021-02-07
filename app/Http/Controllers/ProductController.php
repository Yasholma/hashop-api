<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Checkout;
use App\Models\CheckoutItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::where([['name', '!=', Null], [function ($query) use ($request) {
            if ($search = $request->search) {
                $query
                    ->orWhere('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%')
                    ->get();
            }
        }]])->orderBy('id', 'DESC')->paginate(10);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:100',
            'description' => 'required|min:5',
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'image' => 'required|image|mimes:jpg,jpeg,svg,png'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // store file
        $path = $request->file('image')->store("public/products");

        // save to database
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'image' => $path
        ]);

        return response(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'min:3|max:100',
            'description' => 'min:5',
            'price' => 'integer',
            'quantity' => 'integer',
            'image' => 'image|mimes:jpg,jpeg,svg,png'
        ]);

        $newPath = "";
        if ($request->hasFile('image')) {
            Storage::delete('products/' . $product->image);
            $newPath = $request->file('image')->store('public/products');
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product->name = $request->get('name', $product->name);
        $product->description = $request->get('description', $product->description);
        $product->price = $request->get('price', $product->price);
        $product->quantity = $request->get('quantity', $product->quantity);
        $product->image = $request->get($newPath, $product->image);

        $product->save();
        return new ProductResource($product);
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cartItems' => 'required|array',
            'total' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // save to checkout and retrieve id
        $checkout = Checkout::create([
            'total' => $request->total,
            'user_id' => auth()->id()
        ]);

        $cartItems = $request->cartItems;
        for ($i = 0; $i < count($cartItems); $i++) {
            $item = $cartItems[$i];
            CheckoutItem::create([
                'checkout_id' => $checkout['id'],
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'sub_total' => $item['sub_total']
            ]);
        }

        return response('Checkout was successful', 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response('', 204);
    }
}
