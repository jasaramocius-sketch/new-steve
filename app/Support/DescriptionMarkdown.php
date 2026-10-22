<?php

namespace App\Support;

class DescriptionMarkdown
{
    public static function toHtml(string $text): string
    {
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (preg_match('/<[a-zA-Z][^>]*>/', $text)) {
            return $text;
        }

        $text = preg_replace('/^[ \t]*\xE2\x80\xA2[ \t]+/m', '- ', $text);

        $blocks = self::splitBlocks($text);

        $html = '';
        foreach ($blocks as $block) {
            $html .= self::renderBlock($block);
        }

        return $html;
    }

    /**
     * Pull a trailing "Key Features:" + <ul> section out of a rendered
     * description into a standalone features list (kept as inner HTML).
     *
     * @return array{description: string, features: array<int, string>}
     */
    public static function splitKeyFeatures(string $descriptionHtml): array
    {
        if (preg_match('/(?:<p>[ \t]*<strong>[ \t]*Key Features:\s*<\/strong>[ \t]*<\/p>|<p>[ \t]*Key Features:\s*<\/p>)\s*<ul>.*?<\/ul>\s*$/is', $descriptionHtml, $m)) {
            $sectionEnd = strpos($descriptionHtml, $m[0]);
            if ($sectionEnd !== false) {
                $head = trim(substr($descriptionHtml, 0, $sectionEnd));
                $features = [];
                if (preg_match_all('/<li>(.*?)<\/li>/is', $m[0], $items)) {
                    foreach ($items[1] as $item) {
                        $features[] = trim($item);
                    }
                }

                return [
                    'description' => $head === '' ? '' : $head,
                    'features' => $features,
                ];
            }
        }

        return ['description' => $descriptionHtml, 'features' => []];
    }

    private static function splitBlocks(string $text): array
    {
        $lines = explode("\n", $text);
        $count = count($lines);
        $blocks = [];
        $para = [];
        $i = 0;

        while ($i < $count) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($para !== []) {
                    $blocks[] = ['type' => 'para', 'lines' => $para];
                    $para = [];
                }
                $i++;

                continue;
            }

            if (preg_match('/^[ \t]*([-*+])[ \t]+(.*)$/', $line, $m)) {
                $listType = 'ul';
                $marker = $m[1];
                $firstItem = $m[2];
            } elseif (preg_match('/^[ \t]*\d+[.][ \t]+(.*)$/', $line, $m)) {
                $listType = 'ol';
                $marker = 'ordered';
                $firstItem = $m[1];
            } else {
                $listType = null;
            }

            if ($listType !== null) {
                if ($para !== []) {
                    $blocks[] = ['type' => 'para', 'lines' => $para];
                    $para = [];
                }

                $linesInList = [];
                while ($i < $count) {
                    $li = $lines[$i];
                    if (preg_match('/^[ \t]*([-*+])[ \t]+(.*)$/', $li, $lm)) {
                        if ($listType === 'ul') {
                            $linesInList[] = $lm[2];
                            $i++;

                            continue;
                        }
                        break;
                    }
                    if (preg_match('/^[ \t]*\d+[.][ \t]+(.*)$/', $li, $lm)) {
                        if ($listType === 'ol') {
                            $linesInList[] = $lm[1];
                            $i++;

                            continue;
                        }
                        break;
                    }
                    break;
                }

                if ($linesInList === []) {
                    $linesInList[] = $firstItem;
                }

                $blocks[] = ['type' => $listType, 'lines' => $linesInList];

                continue;
            }

            $para[] = $line;
            $i++;
        }

        if ($para !== []) {
            $blocks[] = ['type' => 'para', 'lines' => $para];
        }

        return $blocks;
    }

    private static function renderBlock(array $block): string
    {
        $type = $block['type'];
        $lines = $block['lines'];

        if ($type === 'ul' || $type === 'ol') {
            $items = '';
            foreach ($lines as $line) {
                $items .= '<li>'.self::inline($line).'</li>'."\n";
            }

            return $type === 'ul' ? "<ul>\n{$items}</ul>\n" : "<ol>\n{$items}</ol>\n";
        }

        if (count($lines) === 1 && preg_match('/^(#{1,6})[ \t]+(.+)$/', $lines[0], $m)) {
            $level = strlen($m[1]);

            return "<h{$level}>".self::inline($m[2])."</h{$level}>\n";
        }

        if (count($lines) === 1 && preg_match('/^\s*(?:-{3,}|\*{3,}|_{3,})\s*$/', $lines[0])) {
            return "<hr>\n";
        }

        $out = array_map([self::class, 'inline'], $lines);

        return '<p>'.implode("<br>\n", $out)."</p>\n";
    }

    private static function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = str_replace('&#039;', '&#39;', $text);

        $text = preg_replace_callback('/`([^`]+)`/', function ($m) {
            return '<code>'.$m[1].'</code>';
        }, $text);

        $text = preg_replace_callback('/\*\*(?=\S)(.+?)(?<=\S)\*\*/', function ($m) {
            return '<strong>'.$m[1].'</strong>';
        }, $text);
        $text = preg_replace_callback('/__(?=\S)(.+?)(?<=\S)__/', function ($m) {
            return '<strong>'.$m[1].'</strong>';
        }, $text);

        $text = preg_replace_callback('/(^|[\s([])\*(?=\S)([^*\n]+?)(?<=\S)\*(?=$|[\s,.;:!?)])/u', function ($m) {
            return $m[1].'<em>'.$m[2].'</em>';
        }, $text);
        $text = preg_replace_callback('/(^|[\s([])_(?=\S)([^_\n]+?)(?<=\S)_(?=$|[\s,.;:!?)])/u', function ($m) {
            return $m[1].'<em>'.$m[2].'</em>';
        }, $text);

        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
            return '<a href="'.$m[2].'">'.$m[1].'</a>';
        }, $text);

        return $text;
    }
}
