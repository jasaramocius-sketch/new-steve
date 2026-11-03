# Sitemap like Yoast SEO

## Goal
Improve `/sitemap.xml` to include categories, products, blogs, brands in a structured sitemap (Yoast-style index optional).

## Current state
`HomeController::sitemap()` already streams urlset with home, shop, categories, brands, blogs, products.

## Plan
1. Verify categories are included (currently yes via Category loop).
2. Add `lastmod` for categories/products/blogs (already for some).
3. Add image sitemap entries? Optional.
4. Ensure route `/sitemap.xml` exists and cached.

## Implementation
- Review `routes/web.php` for sitemap route.
- Update sitemap() to include category URLs with lastmod.
- Add sitemap index if needed (optional).

## Validation
- `GET /sitemap.xml` returns valid XML.
- Contains category and product URLs.
