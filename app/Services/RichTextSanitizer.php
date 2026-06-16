<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_AttrDef_Text;
use HTMLPurifier_Config;

class RichTextSanitizer
{
    /**
     * Sanitize rich HTML from WYSIWYG editors (Quill-compatible tags plus common table markup).
     */
    public static function purify(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();

        // Needed so we can declare non-standard attrs (e.g. Quill task lists use li[data-list]).
        $config->set('HTML.DefinitionID', 'antivo-rich-text-html');
        $config->set('HTML.DefinitionRev', 10);

        $serializerPath = storage_path('app/htmlpurifier');
        if (! is_dir($serializerPath)) {
            @mkdir($serializerPath, 0775, true);
        }
        $config->set('Cache.SerializerPath', $serializerPath);

        $config->set('HTML.Allowed',
            // Note: omit <mark> — HTMLPurifier's default HTML def has no ElementDef for it and throws.
            // p/h* need style|class so Quill alignment (ql-align-*) and text-align survive sanitization.
            // strong/b/em/etc. need style|class — Quill applies font-size on inline format tags, not only span.
            'p[style|class],br,hr,strong[style|class],b[style|class],em[style|class],i[style|class],u[style|class],s[style|class],sub[style|class],sup[style|class],strike[style|class],del[style|class],abbr[title],'.
            'h1[style|class],h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class],'.
            'ul,ol,li[style|class|data-list],blockquote[style|class],pre[style|class],code,span[style|class],div[style|class],'.
            'a[href|title|target|rel],img[src|alt|width|height|class|style],'.
            'table[class|style],thead,tbody,tfoot,tr[style|class],th[class|style|colspan|rowspan],'.
            'td[class|style|colspan|rowspan],caption,colgroup,col'
        );
        $config->set('CSS.AllowedProperties',
            'text-align,color,background-color,font-size,font-weight,font-style,font-family,width,max-width,min-width,'.
            'height,white-space,line-height,vertical-align,text-decoration,text-indent,'.
            'border,border-top,border-right,border-bottom,border-left,border-width,border-color,border-style,border-collapse,'.
            'margin,margin-top,margin-left,margin-right,margin-bottom,padding,padding-top,padding-left,padding-right,padding-bottom'
        );
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);

        // Use addAttribute: $def->info['li'] is not populated yet during customization
        // (see HTMLPurifier_HTMLDefinition::addAttribute / anonymous module).
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('li', 'data-list', new HTMLPurifier_AttrDef_Text());
        }

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Sanitize then normalize Quill formatting for DomPDF (alignment, etc.).
     */
    public static function prepareForPdf(?string $html): string
    {
        $input = $html ?? '';
        // #region agent log
        AppointmentFormDomPdfService::agentDebugLog('RichTextSanitizer.php:prepareForPdf', 'before_purify', AppointmentFormDomPdfService::fontSizeDebugMeta($input), 'B');
        // #endregion
        $clean = self::purify($input);
        // #region agent log
        AppointmentFormDomPdfService::agentDebugLog('RichTextSanitizer.php:prepareForPdf', 'after_purify', AppointmentFormDomPdfService::fontSizeDebugMeta($clean), 'C');
        // #endregion
        $normalized = self::normalizeFontSizesForPdf(self::normalizeQuillForPdf($clean));
        // #region agent log
        AppointmentFormDomPdfService::agentDebugLog('RichTextSanitizer.php:prepareForPdf', 'after_normalizeFontSizes', AppointmentFormDomPdfService::fontSizeDebugMeta($normalized), 'D');
        // #endregion

        return $normalized;
    }

    /**
     * Convert Quill alignment classes to inline text-align (DomPDF does not load Quill CSS).
     */
    public static function normalizeQuillForPdf(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $alignClassMap = [
            'ql-align-center' => 'center',
            'ql-align-right' => 'right',
            'ql-align-justify' => 'justify',
            'ql-align-left' => 'left',
        ];

        $blockTags = 'p|h[1-6]|div|blockquote|li|td|th';

        foreach ($alignClassMap as $className => $alignValue) {
            $pattern = '/<('.$blockTags.')(\s[^>]*)>/iu';
            $html = preg_replace_callback($pattern, function (array $m) use ($className, $alignValue) {
                $tag = $m[1];
                $attrs = $m[2] ?? '';

                if (! preg_match('/\b'.preg_quote($className, '/').'\b/i', $attrs)) {
                    return $m[0];
                }

                if (preg_match('/\sclass="([^"]*)"/i', $attrs, $cm)) {
                    $classes = preg_split('/\s+/', trim($cm[1]));
                    $classes = array_values(array_filter($classes, function ($c) use ($className) {
                        return strcasecmp($c, $className) !== 0;
                    }));
                    if (count($classes) === 0) {
                        $attrs = preg_replace('/\sclass="[^"]*"/i', '', $attrs, 1) ?? $attrs;
                    } else {
                        $attrs = preg_replace(
                            '/\sclass="[^"]*"/i',
                            ' class="'.implode(' ', $classes).'"',
                            $attrs,
                            1
                        ) ?? $attrs;
                    }
                }

                $styleRule = 'text-align: '.$alignValue.';';
                if (preg_match('/\sstyle="([^"]*)"/i', $attrs, $sm)) {
                    $existing = $sm[1];
                    if (! preg_match('/text-align\s*:/i', $existing)) {
                        $merged = rtrim($existing, '; ').'; '.$styleRule;
                        $attrs = preg_replace('/\sstyle="[^"]*"/i', ' style="'.trim($merged).'"', $attrs, 1);
                    }
                } else {
                    $attrs .= ' style="'.$styleRule.'"';
                }

                return '<'.$tag.$attrs.'>';
            }, $html) ?? $html;
        }

        // Legacy align="" attribute
        $html = preg_replace('/<(p|h[1-6]|div|blockquote)\s+([^>]*?)align="(center|right|justify|left)"([^>]*)>/i', '<$1 $2style="text-align: $3;" $4>', $html) ?? $html;

        // DomPDF will not wrap lines that use nowrap from the editor
        $html = preg_replace('/white-space\s*:\s*nowrap\b/i', 'white-space: normal', $html) ?? $html;

        return $html;
    }

    /**
     * Convert Quill inline font-size (px) to pt for DomPDF, preserving relative sizes.
     */
    public static function normalizeFontSizesForPdf(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $minPt = 6.0;
        $maxPt = 18.0;

        return preg_replace_callback(
            '/font-size\s*:\s*([\d.]+)\s*(px|pt)\b/i',
            function (array $m) use ($minPt, $maxPt): string {
                $value = (float) $m[1];
                $unit = strtolower($m[2]);

                if ($unit === 'pt') {
                    $pt = $value;
                } else {
                    $pt = $value * 0.75;
                }

                $pt = max($minPt, min($maxPt, round($pt, 1)));

                return 'font-size: '.$pt.'pt';
            },
            $html
        ) ?? $html;
    }

    /**
     * Strip HTML tags and decode entities for plain-text previews.
     */
    public static function toPlainText(?string $html, int $maxLength = 0): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text);
        $text = trim($text);

        if ($maxLength > 0 && mb_strlen($text) > $maxLength) {
            return mb_substr($text, 0, $maxLength) . '…';
        }

        return $text;
    }
}
