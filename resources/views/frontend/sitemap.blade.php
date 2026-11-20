{!! '<' . '?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
    <url><loc>{{ route('products.index') }}</loc><changefreq>daily</changefreq><priority>0.9</priority></url>
    <url><loc>{{ route('contact') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
@foreach($products as $p)
    <url><loc>{{ route('products.show', [$p['id'], $p['slug']]) }}</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
@endforeach
</urlset>
