<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

class PolicyContentSanitizer
{
    private const DISALLOWED_ELEMENTS = [
        'script',
        'iframe',
        'object',
        'embed',
        'link',
        'meta',
        'base',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'option',
        'foreignobject',
    ];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="policy-content-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        foreach (self::DISALLOWED_ELEMENTS as $tagName) {
            $nodes = $xpath->query('//'.$tagName);

            if ($nodes === false) {
                continue;
            }

            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $comments = $xpath->query('//comment()');
        if ($comments !== false) {
            foreach (iterator_to_array($comments) as $comment) {
                $comment->parentNode?->removeChild($comment);
            }
        }

        $elements = $xpath->query('//*');
        if ($elements !== false) {
            foreach (iterator_to_array($elements) as $element) {
                if (! $element instanceof DOMElement) {
                    continue;
                }

                $attributeNames = [];
                foreach ($element->attributes as $attribute) {
                    $attributeNames[] = $attribute->name;
                }

                foreach ($attributeNames as $attributeName) {
                    $normalized = strtolower($attributeName);

                    if (str_starts_with($normalized, 'on') || in_array($normalized, ['srcdoc', 'style', 'formaction'], true)) {
                        $element->removeAttribute($attributeName);
                        continue;
                    }

                    if (in_array($normalized, ['href', 'src', 'xlink:href'], true)) {
                        $value = html_entity_decode($element->getAttribute($attributeName), ENT_QUOTES | ENT_HTML5, 'UTF-8');

                        if (! $this->isSafeUrl($value, $normalized === 'href')) {
                            $element->removeAttribute($attributeName);
                        }
                    }
                }
            }
        }

        $root = $document->getElementById('policy-content-root');

        if (! $root instanceof DOMElement) {
            return '';
        }

        $sanitized = '';
        foreach ($root->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return trim($sanitized);
    }

    private function isSafeUrl(string $url, bool $allowContactSchemes): bool
    {
        $url = trim(preg_replace('/[\x00-\x20\x7F]+/u', '', $url) ?? '');

        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $allowedSchemes = $allowContactSchemes
            ? ['http', 'https', 'mailto', 'tel']
            : ['http', 'https'];

        return in_array($scheme, $allowedSchemes, true);
    }
}
