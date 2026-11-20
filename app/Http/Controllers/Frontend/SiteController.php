<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Middleware\SetFrontendLocale;
use App\Services\Frontend\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SiteController extends Controller
{
    public function __construct(private CatalogService $catalog)
    {
    }

    public function home()
    {
        return view('frontend.pages.home', [
            'stats'      => $this->catalog->stats(),
            'companies'  => $this->catalog->companies(),
            'featured'   => $this->catalog->featured(10),
            'heroImages' => $this->catalog->heroImages(),
        ]);
    }

    public function contact(Request $request)
    {
        $inquiryProduct = $request->integer('product')
            ? $this->catalog->find($request->integer('product'))
            : null;

        return view('frontend.pages.contact', compact('inquiryProduct'));
    }

    public function switchLocale(Request $request, string $locale)
    {
        if (in_array($locale, SetFrontendLocale::LOCALES, true)) {
            $request->session()->put('frontend_locale', $locale);
        }

        $back = url()->previous();
        // Only bounce back to our own public pages.
        if (! str_starts_with($back, url('/')) || str_contains($back, '/admin') || str_contains($back, '/lang/')) {
            $back = route('home');
        }

        return redirect()->to($back);
    }

    public function sitemap()
    {
        $products = $this->catalog->products();

        return response()
            ->view('frontend.sitemap', compact('products'))
            ->header('Content-Type', 'application/xml');
    }
}
