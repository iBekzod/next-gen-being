<?php

namespace App\Http\Controllers;

use App\Models\DigitalProduct;
use App\Models\ProductPurchase;
use App\Services\DigitalProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalProductController extends Controller
{
    public function __construct(
        protected DigitalProductService $productService
    ) {}

    /**
     * Display a listing of digital products
     */
    public function index(Request $request)
    {
        $query = DigitalProduct::published();

        // Filter by type
        if ($request->filled('type')) {
            $query->type($request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sort
        if ($request->filled('sort')) {
            match($request->sort) {
                'popular' => $query->popular(),
                'price-low' => $query->orderBy('price'),
                'price-high' => $query->orderByDesc('price'),
                'newest' => $query->orderByDesc('published_at'),
                default => $query->orderByDesc('published_at'),
            };
        } else {
            $query->orderByDesc('published_at');
        }

        $products = $query->paginate(12);

        // Get available categories
        $categories = DigitalProduct::published()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return view('digital-products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified digital product
     */
    public function show(DigitalProduct $product)
    {
        if ($product->status !== 'published') {
            abort(404);
        }

        $isPurchased = auth()->check() && $product->isPurchasedBy(auth()->user());

        $relatedProducts = DigitalProduct::published()
            ->where('type', $product->type)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('digital-products.show', compact('product', 'isPurchased', 'relatedProducts'));
    }

    /**
     * Initiate purchase of a digital product
     */
    public function purchase(DigitalProduct $product)
    {
        if ($product->status !== 'published') {
            return back()->with('error', 'Product is no longer available');
        }

        if (!auth()->check()) {
            return redirect()->route('login')->with('redirect', route('digital-products.show', $product));
        }

        // Check if already purchased
        if ($product->isPurchasedBy(auth()->user())) {
            return redirect()->route('digital-products.download-index')
                ->with('info', 'You already own this product');
        }

        if ($product->is_free) {
            // Free product - instant access
            $purchase = ProductPurchase::create([
                'user_id' => auth()->id(),
                'digital_product_id' => $product->id,
                'amount' => 0,
                'currency' => 'USD',
                'status' => 'completed',
                'license_key' => ProductPurchase::generateLicenseKey(),
                'creator_revenue' => 0,
                'platform_revenue' => 0,
            ]);

            $product->incrementPurchases();

            return redirect()->route('digital-products.download-index')
                ->with('success', 'Product downloaded successfully!');
        }

        // Paid product - would redirect to LemonSqueezy
        // For now, return placeholder
        return back()->with('error', 'Payment processing not yet configured');
    }

    /**
     * Download a purchased product
     */
    public function download(ProductPurchase $purchase)
    {
        // Check authorization
        if ($purchase->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if download is allowed
        if (!$purchase->canDownload()) {
            if ($purchase->download_count >= $purchase->download_limit) {
                return back()->with('error', 'Download limit exceeded (max 10 downloads)');
            }

            if ($purchase->expires_at && $purchase->expires_at->isPast()) {
                return back()->with('error', 'Download access has expired');
            }

            return back()->with('error', 'Cannot download this product');
        }

        // Increment download count
        $purchase->incrementDownload();
        $purchase->product->incrementDownloads();

        // Return file download
        $filePath = $purchase->product->file_path;

        return Storage::disk('private')->download($filePath, $purchase->product->slug . '.txt');
    }

    /**
     * Display user's purchased products
     */
    public function myPurchases()
    {
        $this->middleware('auth');

        $purchases = auth()->user()
            ->purchases()
            ->with('product')
            ->latest()
            ->paginate(10);

        return view('digital-products.my-purchases', compact('purchases'));
    }

    /**
     * List all purchases for download index
     */
    public function downloadIndex()
    {
        $this->middleware('auth');

        $purchases = auth()->user()
            ->purchases()
            ->where('status', 'completed')
            ->with('product')
            ->latest()
            ->paginate(12);

        return view('digital-products.download-index', compact('purchases'));
    }
}
