<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

class CategoryTree
{
    public const CACHE_KEY = 'front_category_tree_v2';

    /**
     * Build (and cache) the full nested category tree as lightweight objects.
     *
     * The catalogue can contain tens of thousands of categories, so we avoid
     * loading Eloquent models here — the tree is stored as compact JSON and
     * decoded into plain stdClass nodes (each with an array of children).
     *
     * @return array<int, object>
     */
    public static function tree(): array
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        $json = cache()->remember(self::CACHE_KEY, 21600, function () {
            // Aggregate in SQL instead of hydrating every product. The catalogue
            // has ~750k rows; loading Eloquent models here exhausted the 512M
            // PHP memory limit and made every page 500 whenever the cache was cold.
            // This uses the same "usable image" rule as the shop listing, so the
            // category counts now match what shoppers actually see.
            $productCounts = Product::statusCountsByCategoryId()->all();

            // A-Z (case-insensitive) at every level: children are appended to
            // the tree in query order, so this also sorts subcategories.
            $rows = Category::query()
                ->orderByRaw('LOWER(name) ASC')
                ->orderBy('id')
                ->get(['id', 'name', 'slug', 'parent_id']);

            $childrenMap = [];
            $nodes = [];
            foreach ($rows as $row) {
                $childrenMap[$row->parent_id ?? 0][] = $row->id;
                $nodes[$row->id] = [
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'descendant_count' => (int) ($productCounts[$row->id] ?? 0),
                    'children' => [],
                ];
            }

            $build = function ($id) use (&$build, &$childrenMap, &$nodes) {
                $total = $nodes[$id]['descendant_count'];
                $children = [];
                foreach ($childrenMap[$id] ?? [] as $childId) {
                    $child = $build($childId);
                    $total += $child['descendant_count'];
                    $children[] = $child;
                }
                $nodes[$id]['children'] = $children;
                $nodes[$id]['descendant_count'] = $total;

                return $nodes[$id];
            };

            $tree = [];
            foreach ($childrenMap[0] ?? [] as $topId) {
                $tree[] = $build($topId);
            }

            return json_encode($tree, JSON_INVALID_UTF8_SUBSTITUTE);
        });

        return $resolved = (json_decode($json) ?? []);
    }

    public static function flush(): void
    {
        cache()->forget(self::CACHE_KEY);
    }
}
