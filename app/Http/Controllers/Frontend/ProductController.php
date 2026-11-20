<?php

namespace App\Http\Controllers\Frontend;

use App\Services\Frontend\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    private const PER_PAGE = 24;

    public function __construct(private CatalogService $catalog)
    {
    }

    public function index(Request $request)
    {
        $q         = trim((string) $request->query('q', ''));
        $companyId = $request->integer('company') ?: null;
        $sort      = in_array($request->query('sort'), ['name', 'price_asc', 'price_desc', 'newest'], true)
            ? $request->query('sort') : 'name';

        $results = $this->catalog->search($q, $companyId, $sort);
        $page    = max(1, $request->integer('page', 1));

        $products = new LengthAwarePaginator(
            $results->forPage($page, self::PER_PAGE)->values(),
            $results->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $companies = $this->catalog->companies();
        $activeCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;

        return view('frontend.pages.products', compact('products', 'companies', 'activeCompany', 'q', 'sort'));
    }

    public function show(int $product, ?string $slug = null)
    {
        $item = $this->catalog->find($product);

        abort_if(! $item, 404);

        if ($slug !== $item['slug']) {
            return redirect()->route('products.show', [$item['id'], $item['slug']], 301);
        }

        return view('frontend.pages.product', [
            'product' => $item,
            'related' => $this->catalog->related($item, 4),
        ]);
    }
}
