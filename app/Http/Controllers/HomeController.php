<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\HomePageSection;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tag;

class HomeController extends Controller
{
    private function formatCategoryName(string $slug): string
    {
        $names = [
            'Engine-Parts' => 'Engine Parts',
            'Body-Exterior' => 'Body & Exterior',
            'Interior-Parts' => 'Interior Parts',
            'Electrical-Lighting' => 'Electrical & Lighting',
            'Brakes-Brake-Parts' => 'Brakes & Brake Parts',
            'Transmission-Drivetrain' => 'Transmission & Drivetrain',
            'Suspension-Steering' => 'Suspension & Steering',
        ];

        return $names[$slug] ?? ucwords(str_replace('-', ' ', $slug));
    }

    private function getCategories($categorySection = null)
    {
        $selectedIds = $categorySection?->extra_data['category_ids'] ?? [];
        $selectedIds = is_array($selectedIds) ? array_values(array_filter(array_map('intval', $selectedIds))) : [];

        $counts = Product::statusCountsByCategoryId();

        $query = Category::whereNull('parent_id');

        if (! empty($selectedIds)) {
            $query->whereIn('id', $selectedIds)->orderByRaw('FIELD(id, '.implode(',', $selectedIds).')');
        } else {
            $query->orderBy('name');
        }

        return $query->get()
            ->map(function ($category) use ($counts) {
                $categoryIds = $category->getAllDescendantIds();
                $count = 0;
                foreach ($categoryIds as $id) {
                    $count += (int) ($counts[$id] ?? 0);
                }

                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->getDisplayImagePath(),
                    'count' => $count,
                ];
            });
    }

    private function getCategoryImage(string $slug): string
    {
        $images = [
            'engine-parts' => '1730804224silver-device-with-white-cover-that-says-word-it-minpng.png',
            'body-exterior' => '173080421331792401shockabsorber2-minpng.png',
            'interior-parts' => '1730804204oval-shaped-car-mirror-minpng.png',
            'electrical-lighting' => '1730804142c1b1489eb0545231cf0bfa44a827e2ae-minpng.png',
            'brakes-brake-parts' => '173080413017369231014177a5f-cedb-407a-8a1d-a6aa0f40249d-minpng.png',
            'transmission-drivetrain' => '1730804110engineering-concept-project-heating-house-thermostatic-valve-copper-fitting-project-minpng.png',
            'suspension-steering' => '1730804093huelpful-steering-wheel-isolated-white-background-minpng.png',
        ];

        return $images[strtolower($slug)] ?? 'test.webp';
    }

    private function getDefaultCategories()
    {
        return [];
    }

    private function getFeaturedProducts()
    {
        return $this->filterProductsWithImages(
            Product::where('status', true)->latest()->take(8)->get()
        );
    }

    private function filterProductsWithImages($products)
    {
        return $products
            ->filter(fn (Product $product) => filled($product->image))
            ->values();
    }

    private function randomProducts(int $take = 8)
    {
        $maxId = (int) Product::where('status', true)->max('id');

        if ($maxId < 1) {
            return collect();
        }

        $products = Product::where('status', true)
            ->where('id', '>=', random_int(1, $maxId))
            ->take($take)
            ->get();
        $products = $this->filterProductsWithImages($products);

        if ($products->isEmpty()) {
            $products = $this->filterProductsWithImages(
                Product::where('status', true)->latest()->take($take)->get()
            );
        }

        return $products;
    }

    public function index()
    {
        $sections = HomePageSection::where('status', true)
            ->orderBy('order')
            ->get()
            ->keyBy('section_name');

        $categories = $this->getCategories($sections->get('categories_heading'));

        if (! HomePageSection::where('section_name', 'top_brands_heading')->exists()) {
            HomePageSection::create([
                'section_name' => 'top_brands_heading',
                'title' => 'Top Brands',
                'description' => 'Explore our curated selection of premium auto part brands known for quality and reliability.',
                'order' => 999,
                'status' => true,
                'extra_data' => ['brands_limit' => '6'],
            ]);
            $sections = HomePageSection::where('status', true)
                ->orderBy('order')
                ->get()
                ->keyBy('section_name');
        }

        $exploreSection = $sections->get('explore_products');
        $exploreTabs = $this->buildExploreTabs($exploreSection);

        $featuredProducts = $this->getFeaturedProducts();
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $this->randomProducts(8);
        }

        $heroSection = $sections->get('hero');

        $offersSection = $sections->get('offers');
        $bannerData = $offersSection?->extra_data['banners'] ?? [];
        if (! empty($bannerData)) {
            $banners = collect($bannerData)->map(fn ($b) => (object) $b);
        } else {
            $banners = $sections->filter(fn ($s) => str_starts_with($s->section_name, 'banner_'))->values();
        }

        $bestSelling = $this->randomProducts(8);

        $servicesSection = HomePageSection::where('section_name', 'services')->first();
        if ($servicesSection) {
            $services = $servicesSection->status
                ? ($servicesSection->extra_data['services'] ?? config('homepage.services', []))
                : [];
        } else {
            $services = Setting::get('homepage_services');
            if (is_string($services)) {
                $services = json_decode($services, true);
            }
            if (! is_array($services) || count($services) !== count(config('homepage.services', []))) {
                $services = config('homepage.services', []);
            }
        }

        $latestPostSection = $sections->get('latest_post');
        $latestPostLimit = (int) ($latestPostSection?->extra_data['posts_count'] ?? 2);
        if ($latestPostLimit < 1 || $latestPostLimit > 12) {
            $latestPostLimit = 2;
        }

        $selectedPostIds = $latestPostSection?->extra_data['post_ids'] ?? [];
        $selectedPostIds = is_array($selectedPostIds) ? array_filter(array_map('intval', $selectedPostIds)) : [];

        if (! empty($selectedPostIds)) {
            $latestPosts = Blog::accessible()
                ->whereIn('id', $selectedPostIds)
                ->orderByRaw('FIELD(id, '.implode(',', $selectedPostIds).')')
                ->take($latestPostLimit)
                ->get();
        } else {
            $latestPosts = Blog::accessible()->latest()->take($latestPostLimit)->get();
        }

        $brandsSection = $sections->get('top_brands_heading');
        $brandsExtra = $brandsSection?->extra_data ?? [];
        $selectedBrandIds = $brandsExtra['brand_ids'] ?? [];
        $brandsLimit = $brandsExtra['brands_limit'] ?? '6';
        $brandsLimit = $brandsLimit === 'all' || $brandsLimit === '' ? '6' : $brandsLimit;

        $brandsQuery = Brand::orderBy('name');

        if (! empty($selectedBrandIds) && is_array($selectedBrandIds)) {
            $selectedBrandIds = array_filter(array_map('intval', $selectedBrandIds));
            if (! empty($selectedBrandIds)) {
                $brandsQuery = Brand::whereIn('id', $selectedBrandIds)
                    ->orderByRaw('FIELD(id, '.implode(',', $selectedBrandIds).')');
            }
        } elseif ((int) $brandsLimit > 0) {
            $brandsQuery->take((int) $brandsLimit);
        }

        $brands = $brandsQuery->get();

        return view('home.index', compact('categories', 'featuredProducts', 'exploreTabs', 'heroSection', 'banners', 'bestSelling', 'sections', 'latestPosts', 'brands', 'services') + ['page' => Page::where('slug', 'home')->where('status', true)->first()]);
    }

    private function buildExploreTabs($section): array
    {
        $extra = $section?->extra_data ?? [];
        $tabs = $extra['tabs'] ?? [];

        // Migrate old format (keyed by product_type) to new format
        if (empty($tabs)) {
            $oldKeys = ['new_arrival' => 'New Arrivals', 'trending' => 'Trending', 'best_selling' => 'Best Selling', 'popular' => 'Popular'];
            foreach ($oldKeys as $key => $label) {
                $ids = $extra[$key] ?? [];
                if (! empty($ids)) {
                    $tabs[] = ['label' => $label, 'product_ids' => $ids];
                }
            }
        }

        $result = [];
        foreach ($tabs as $i => $tab) {
            $label = $tab['label'] ?? ('Tab '.($i + 1));
            $sectionKeys = [
                'New Arrivals' => 'new_arrival',
                'Trending' => 'trending',
                'Best Selling' => 'best_selling',
                'Popular' => 'popular',
            ];
            $sectionKey = $sectionKeys[$label] ?? null;
            $ids = $tab['product_ids'] ?? [];

            if (! empty($ids)) {
                $products = Product::where('status', true)
                    ->whereIn('id', $ids)
                    ->latest()
                    ->take(8)
                    ->get();
            } elseif ($sectionKey) {
                $products = Product::where('status', true)
                    ->latest()
                    ->take(8)
                    ->get();
            } else {
                continue;
            }

            $products = $this->filterProductsWithImages($products);

            if ($products->isEmpty()) {
                $products = $this->randomProducts(8);
            }

            $result[] = [
                'label' => $label,
                'products' => $products,
            ];
        }

        if (empty($result)) {
            $result[] = [
                'label' => 'New Arrivals',
                'products' => $this->getFeaturedProducts(),
            ];
        }

        return $result;
    }

    private function pageOrAbort($slug)
    {
        $page = Page::where('slug', $slug)->first();

        if ($page && ! $page->status) {
            abort(404);
        }

        return $page;
    }

    public function about()
    {
        $page = $this->pageOrAbort('about');
        if ($page) {
            return view('pages.show', compact('page'));
        }

        return view('pages.about');
    }

    public function contact()
    {
        $page = $this->pageOrAbort('contact');

        return view('pages.contact', compact('page'));
    }

    public function page($slug)
    {
        $canonicalRoute = config("page-builder.live_url_map.page.$slug");
        if ($canonicalRoute) {
            return redirect()->route($canonicalRoute, [], 301);
        }

        $page = Page::where('slug', $slug)->where('status', true)->firstOrFail();

        return view('pages.show', compact('page'));
    }

    public function faq()
    {
        $faqs = Faq::where('status', true)->orderBy('order')->get();
        $page = $this->pageOrAbort('faq');

        return view('pages.faq', compact('faqs') + ['page' => $page]);
    }

    public function privacy()
    {
        $page = $this->pageOrAbort('privacy-policy');
        if ($page) {
            return view('pages.show', compact('page'));
        }

        return view('pages.privacy');
    }

    public function terms()
    {
        $page = $this->pageOrAbort('terms-conditions');
        if ($page) {
            return view('pages.show', compact('page'));
        }

        return view('pages.terms');
    }

    public function returnPolicy()
    {
        $page = $this->pageOrAbort('return-policy');
        if ($page) {
            return view('pages.show', compact('page'));
        }

        return view('pages.return-policy');
    }

    public function supportPolicy()
    {
        $page = $this->pageOrAbort('support-policy');
        if ($page) {
            return view('pages.show', compact('page'));
        }

        return view('pages.support-policy');
    }

    public function sitemap()
    {
        $base = url('/');

        return response()->stream(function () use ($base) {
            $out = fopen('php://output', 'w');

            $write = function (string $loc, $lastmod = null, string $changefreq = 'weekly', float $priority = 0.5) use ($out) {
                fwrite($out, "  <url>\n");
                fwrite($out, '    <loc>'.e($loc)."</loc>\n");
                if ($lastmod) {
                    fwrite($out, '    <lastmod>'.$lastmod."</lastmod>\n");
                }
                fwrite($out, '    <changefreq>'.$changefreq."</changefreq>\n");
                fwrite($out, '    <priority>'.number_format($priority, 1)."</priority>\n");
                fwrite($out, "  </url>\n");
            };

            fwrite($out, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
            fwrite($out, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");

            $write($base, null, 'daily', 1.0);
            $write($base.'/shop', null, 'daily', 0.9);
            $write($base.'/categories', null, 'weekly', 0.7);
            $write($base.'/brands', null, 'weekly', 0.6);
            $write($base.'/blog', null, 'daily', 0.8);
            $write($base.'/about-us', null, 'monthly', 0.4);
            $write($base.'/contact-us', null, 'monthly', 0.4);
            $write($base.'/faq', null, 'monthly', 0.3);
            $write($base.'/privacy', null, 'monthly', 0.2);
            $write($base.'/terms', null, 'monthly', 0.2);
            $write($base.'/return-policy', null, 'monthly', 0.2);
            $write($base.'/support-policy', null, 'monthly', 0.2);

            $categoryMap = Category::select('id', 'slug', 'parent_id')->get()->keyBy('id');

            Category::select('id', 'slug', 'parent_id', 'updated_at')->orderBy('id')->cursor()
                ->each(function ($category) use ($write, $categoryMap, $base) {
                    $segments = [];
                    $current = $category;
                    $guard = 0;
                    while ($current && $guard++ < 5) {
                        array_unshift($segments, $current->slug);
                        $current = $current->parent_id ? ($categoryMap[$current->parent_id] ?? null) : null;
                    }
                    $write($base.'/category/'.implode('/', $segments), optional($category->updated_at)->toAtomString(), 'weekly', 0.6);
                });

            Product::where('status', true)->select('slug', 'updated_at')->orderBy('id')->cursor()
                ->each(function ($product) use ($write, $base) {
                    $write($base.'/product/'.$product->slug, optional($product->updated_at)->toAtomString(), 'daily', 0.9);
                });

            Blog::accessible()->select('slug', 'updated_at')->orderBy('id')->cursor()
                ->each(function ($blog) use ($write, $base) {
                    $write($base.'/blog/'.$blog->slug, optional($blog->updated_at)->toAtomString(), 'weekly', 0.8);
                });

            BlogCategory::withCount(['blogs' => fn ($q) => $q->accessible()])->get()
                ->filter(fn ($c) => $c->blogs_count > 0)
                ->each(function ($category) use ($write, $base) {
                    $write($base.'/blog/category/'.$category->slug, optional($category->updated_at)->toAtomString(), 'weekly', 0.6);
                });

            Tag::whereHas('blogs', fn ($q) => $q->accessible())->get()->each(function ($tag) use ($write, $base) {
                $write($base.'/blog/tag/'.$tag->slug, null, 'weekly', 0.4);
            });

            fwrite($out, "</urlset>\n");
            fclose($out);
        }, 200, ['Content-Type' => 'application/xml']);
    }
}
