<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

class CategoryTree
{
    public const CACHE_KEY = 'front_category_tree_v1';

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
            $productCounts = Product::where('status', true)
                ->selectRaw('category_id, COUNT(*) as count')
                ->groupBy('category_id')
                ->pluck('count', 'category_id');

            $rows = Category::query()->get(['id', 'name', 'slug', 'parent_id']);

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
                foreach ($childrenMap[$id] ?? [] as $childId) {
                    $child = $build($childId);
                    $total += $child['descendant_count'];
                    $nodes[$id]['children'][] = $child;
                }
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
