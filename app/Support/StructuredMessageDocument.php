<?php

namespace App\Support;

use App\Models\Attendee;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class StructuredMessageDocument
{
    public const SCHEMA_VERSION = 1;

    public const MAX_BLOCKS_TOTAL = 28;

    public const MAX_ROOT_BLOCKS = 20;

    public const MAX_SECTION_CHILDREN = 12;

    public const MAX_BUTTONS = 6;

    /** Supported font presets (maps to generic email-safe stacks). */
    public const FONT_KEYS = ['default', 'sans', 'serif', 'modern', 'mono'];

    public const FONT_SIZE_KEYS = ['default', '14', '15', '16', '18', '20', '22', '24', '28', '32', '36'];

    public const TEXT_ALIGN_KEYS = ['left', 'center', 'right', 'justify'];

    /**
     * UI + server-valid text / button accents (preset keys plus optional hex only).
     *
     * @var array<string, string|null>
     */
    private const COLOR_PRESETS = [
        'default' => null,
        'inherit' => null,
        'slate' => '#0f172a',
        'ink' => '#020617',
        'muted' => '#64748b',
        'white' => '#ffffff',
        'indigo' => '#4f46e5',
        'violet' => '#5b21b6',
        'rose' => '#be123c',
        'emerald' => '#047857',
        'amber' => '#92400e',
        'sky' => '#0369a1',
        'orange' => '#c2410c',
        'none' => null,
        'soft' => '#f8fafc',
        'indigo_mist' => '#eef2ff',
        'rose_mist' => '#fff1f2',
        'emerald_mist' => '#ecfdf5',
    ];

    /** @return array{v: int, blocks: array<int, array<string, mixed>>} */
    public static function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return ['v' => self::SCHEMA_VERSION, 'blocks' => []];
        }

        return [
            'v' => max(1, (int) ($raw['v'] ?? self::SCHEMA_VERSION)),
            'blocks' => is_array($raw['blocks'] ?? null) ? $raw['blocks'] : [],
        ];
    }

    /** @param  array<string, mixed>|null  $doc */
    public static function collectImagePaths(?array $doc): array
    {
        if (! $doc || ! isset($doc['blocks']) || ! is_array($doc['blocks'])) {
            return [];
        }

        return self::gatherImagePathsFromBlocks($doc['blocks']);
    }

    /** @param  array<int, array<string, mixed>>  $blocks */
    private static function gatherImagePathsFromBlocks(array $blocks): array
    {
        $paths = [];
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            if ($type === 'image' && ! empty($block['path']) && is_string($block['path'])) {
                $paths[] = self::sanitizeStoredPath((string) $block['path']);
            }
            if ($type === 'section' && isset($block['blocks']) && is_array($block['blocks'])) {
                $paths = array_merge($paths, self::gatherImagePathsFromBlocks($block['blocks']));
            }
        }

        return array_values(array_unique(array_filter($paths)));
    }

    public static function sanitizeStoredPath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        return $path;
    }

    public static function isAllowedImagePath(?string $path): bool
    {
        if (! $path || ! is_string($path)) {
            return false;
        }
        $path = self::sanitizeStoredPath($path);

        return str_starts_with($path, 'messages/block-doc/');
    }

    /**
     * @param  array{v: int, blocks: array}  $doc
     * @return array{v: int, blocks: array}
     */
    public static function attachUploadedImages(array $doc, \Illuminate\Http\Request $request): array
    {
        /** @var array<string, mixed> */
        $uploadsByBlockId = $request->file('structure_images') ?? [];

        $merge = function (array &$blocks) use (&$merge, $uploadsByBlockId): void {
            foreach ($blocks as &$block) {
                if (! is_array($block)) {
                    continue;
                }
                $type = (string) ($block['type'] ?? '');
                if ($type === 'image') {
                    $blockId = (string) ($block['id'] ?? '');
                    /** @var mixed $uploadRaw */
                    $uploadRaw = $uploadsByBlockId[$blockId] ?? null;
                    if ($uploadRaw instanceof UploadedFile) {
                        $block['path'] = $uploadRaw->store('messages/block-doc', 'public');
                    }
                }
                if ($type === 'section' && isset($block['blocks']) && is_array($block['blocks'])) {
                    $merge($block['blocks']);
                }
            }
        };

        $copy = $doc;
        $merge($copy['blocks']);

        return $copy;
    }

    /**
     * @param  array{v: int, blocks: array}  $doc
     *
     * @throws ValidationException
     */
    public static function validateAfterUpload(array $doc): void
    {
        $doc = self::normalize($doc);
        $errors = [];

        $rootCount = isset($doc['blocks']) && is_array($doc['blocks']) ? count($doc['blocks']) : 0;
        if ($rootCount > self::MAX_ROOT_BLOCKS) {
            $errors['content_document'][] = 'Too many top-level sections or blocks ('.self::MAX_ROOT_BLOCKS.' maximum). Divide content or simplify.';
        }

        $total = self::countBlocksRecursive($doc['blocks'] ?? []);
        if ($total > self::MAX_BLOCKS_TOTAL) {
            $errors['content_document'][] = 'Too many blocks in total ('.self::MAX_BLOCKS_TOTAL.' maximum). Remove some sections or rows.';
        }

        if ($total < 1) {
            $errors['content_document'][] = 'Add at least one content block.';
        }

        $buttons = self::countButtonsRecursive($doc['blocks'] ?? []);
        if ($buttons > self::MAX_BUTTONS) {
            $errors['content_document'][] = 'Too many buttons ('.self::MAX_BUTTONS.' maximum — keep one clear primary action plus alternates).';
        }

        $h1count = self::countHeadingsRecursive($doc['blocks'] ?? [], 1);
        if ($h1count > 1) {
            $errors['content_document'][] = 'Use at most one large title (heading level 1).';
        }

        $hasSubstance = self::hasSubstantiveBlock($doc['blocks'] ?? [], true);
        if (! $hasSubstance) {
            $errors['content_document'][] = 'Add real body content: at least one paragraph with text, an image, or a callout — not only titles and dividers.';
        }

        self::walkValidateBlocks($doc['blocks'] ?? [], $errors, 0);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /** @param  array<int, mixed>  $blocks */
    private static function countBlocksRecursive(array $blocks): int
    {
        $n = 0;
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $n++;
            $type = (string) ($block['type'] ?? '');
            if ($type === 'section' && isset($block['blocks']) && is_array($block['blocks'])) {
                $n += self::countBlocksRecursive($block['blocks']);
            }
        }

        return $n;
    }

    /** @param  array<int, mixed>  $blocks */
    private static function countButtonsRecursive(array $blocks): int
    {
        $n = 0;
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            if (($block['type'] ?? '') === 'button') {
                $n++;
            }
            if (($block['type'] ?? '') === 'section' && isset($block['blocks']) && is_array($block['blocks'])) {
                $n += self::countButtonsRecursive($block['blocks']);
            }
        }

        return $n;
    }

    /** @param  array<int, mixed>  $blocks */
    private static function countHeadingsRecursive(array $blocks, int $level): int
    {
        $n = 0;
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            if (($block['type'] ?? '') === 'heading' && (int) ($block['level'] ?? 2) === $level) {
                $n++;
            }
            if (($block['type'] ?? '') === 'section' && isset($block['blocks']) && is_array($block['blocks'])) {
                $n += self::countHeadingsRecursive($block['blocks'], $level);
            }
        }

        return $n;
    }

    /**
     * @param  array<int, mixed>  $blocks
     */
    private static function hasSubstantiveBlock(array $blocks, bool $isRoot): bool
    {
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            if ($type === 'paragraph' && filled(trim((string) ($block['text'] ?? '')))) {
                return true;
            }
            if ($type === 'callout' && filled(trim((string) ($block['text'] ?? '')))) {
                return true;
            }
            if ($type === 'image' && self::isAllowedImagePath((string) ($block['path'] ?? ''))) {
                return true;
            }
            if ($type === 'section' && isset($block['blocks']) && is_array($block['blocks'])) {
                if (self::hasSubstantiveBlock($block['blocks'], false)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<int, mixed>  $blocks
     * @param  array<string, array<int, string>>  $errors
     */
    private static function walkValidateBlocks(array $blocks, array &$errors, int $depth): void
    {
        if ($depth > 1) {
            $errors['content_document'][] = 'Nested sections beyond one level are not supported.';

            return;
        }

        $sectionBlocksThisLevel = count(array_filter($blocks, fn ($b) => is_array($b) && ($b['type'] ?? '') === 'section'));

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                $errors['content_document'][] = 'Invalid block structure.';

                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $bid = trim((string) ($block['id'] ?? ''));

            if ($bid === '' || preg_match('/[^a-zA-Z0-9_-]/', $bid)) {
                $errors['content_document'][] = 'Every block needs a stable id.';
            }

            switch ($type) {
                case 'section':
                    if ($depth !== 0) {
                        $errors['content_document'][] = 'Sections can only appear at the top level.';
                        break;
                    }
                    $kids = isset($block['blocks']) && is_array($block['blocks']) ? $block['blocks'] : [];
                    if ($kids === []) {
                        $errors['content_document'][] = 'Sections should contain at least one block.';
                    }
                    if (count($kids) > self::MAX_SECTION_CHILDREN) {
                        $errors['content_document'][] = 'Sections can contain at most '.self::MAX_SECTION_CHILDREN.' blocks.';
                    }
                    foreach ($kids as $ch) {
                        if (is_array($ch) && ($ch['type'] ?? '') === 'section') {
                            $errors['content_document'][] = 'Do not nest a section inside a section.';
                        }
                    }
                    $variant = (string) ($block['variant'] ?? 'default');
                    if (! in_array($variant, ['default', 'muted', 'accent', 'highlight'], true)) {
                        $errors['content_document'][] = 'Invalid section style.';
                    }
                    $title = isset($block['title']) ? (string) $block['title'] : '';
                    if (mb_strlen($title) > 160) {
                        $errors['content_document'][] = 'Section subtitle is too long.';
                    }

                    self::validateOptionalTypography($block, $errors, 'title_font', 'title_color', 'Section label');
                    self::rejectInvalidThemeToken($errors, isset($block['title_background']) ? trim((string) $block['title_background']) : '', 'title_background');

                    self::walkValidateBlocks($kids, $errors, $depth + 1);

                    break;

                case 'heading':
                    $lvl = (int) ($block['level'] ?? 2);
                    if (! in_array($lvl, [1, 2, 3], true)) {
                        $errors['content_document'][] = 'Use heading levels 1–3.';
                    }
                    $te = (string) ($block['text'] ?? '');
                    if (mb_strlen($te) > 220) {
                        $errors['content_document'][] = 'A heading must be concise (≤220 characters).';
                    }

                    self::validateOptionalTypography($block, $errors, 'font', 'color', 'Heading');
                    self::rejectInvalidThemeToken($errors, isset($block['background']) ? trim((string) $block['background']) : '', 'heading_background');
                    self::validateOptionalFontSize($block, $errors);
                    self::validateOptionalTextAlign($block, $errors);

                    break;

                case 'paragraph':
                    $te = (string) ($block['text'] ?? '');
                    if (mb_strlen($te) > 6000) {
                        $errors['content_document'][] = 'A paragraph exceeds the maximum length; split into two blocks.';
                    }

                    self::validateOptionalTypography($block, $errors, 'font', 'color', 'Paragraph');
                    self::rejectInvalidThemeToken($errors, isset($block['background']) ? trim((string) $block['background']) : '', 'paragraph_background');
                    self::validateOptionalFontSize($block, $errors);
                    self::validateOptionalTextAlign($block, $errors);

                    break;

                case 'callout':
                    $variant = (string) ($block['variant'] ?? 'tip');
                    if (! in_array($variant, ['tip', 'warning', 'info'], true)) {
                        $errors['content_document'][] = 'Invalid callout type.';
                    }
                    $tx = (string) ($block['text'] ?? '');
                    if (mb_strlen($tx) > 1600) {
                        $errors['content_document'][] = 'Callout text is too long.';
                    }

                    self::validateOptionalTypography($block, $errors, 'font', 'color', 'Callout');
                    self::rejectInvalidThemeToken($errors, isset($block['background']) ? trim((string) $block['background']) : '', 'callout_background');
                    self::validateOptionalFontSize($block, $errors);
                    self::validateOptionalTextAlign($block, $errors);

                    break;

                case 'image':
                    $path = (string) ($block['path'] ?? '');
                    if (! self::isAllowedImagePath($path)) {
                        $errors['content_document'][] = 'Each image needs a valid uploaded file.';
                    }
                    $alt = (string) ($block['alt'] ?? '');
                    if (mb_strlen($alt) > 200) {
                        $errors['content_document'][] = 'Image description is too long.';
                    }

                    break;

                case 'button':
                    $label = (string) ($block['label'] ?? '');
                    if (mb_strlen($label) > 140) {
                        $errors['content_document'][] = 'Button label must be shorter.';
                    }
                    if (trim($label) === '') {
                        $errors['content_document'][] = 'Every button needs a label.';
                    }
                    $kind = (string) ($block['kind'] ?? 'custom');
                    if (! in_array($kind, ['rsvp', 'feedback', 'meeting', 'custom'], true)) {
                        $errors['content_document'][] = 'Invalid button link target.';

                        break;
                    }

                    if ($kind === 'custom') {
                        $cu = (string) ($block['custom_url'] ?? '');
                        if (trim($cu) === '') {
                            $errors['content_document'][] = 'Custom buttons need a destination URL.';
                        } elseif ($cu !== '#' && filter_var(self::prependHttpPlaceholder($cu), FILTER_VALIDATE_URL) === false && ! self::looksLikeTaggedUrl($cu)) {
                            // allow tokens like https://site.com?page={rsvp_link} after personalization - accept if contains brace or scheme
                            if (! preg_match('#^(https?:)?//#i', trim($cu)) && ! preg_match('/\{[a-z_]+\}/', $cu)) {
                                $errors['content_document'][] = 'Custom button URL looks invalid.';
                            }
                        }
                    }

                    self::validateOptionalTypography($block, $errors, 'btn_font', 'btn_text_color', 'Button text');
                    self::rejectInvalidThemeToken($errors, isset($block['btn_bg']) ? trim((string) $block['btn_bg']) : '', 'btn_bg');

                    break;

                case 'divider':
                case 'spacer':
                    if ($type === 'spacer') {
                        $size = (string) ($block['size'] ?? 'md');
                        if (! in_array($size, ['sm', 'md', 'lg'], true)) {
                            $errors['content_document'][] = 'Invalid spacer height.';
                        }
                    }

                    break;

                default:
                    $errors['content_document'][] = 'Unknown block type: '.$type;
            }
        }

        if ($depth === 0 && $sectionBlocksThisLevel > 10) {
            $errors['content_document'][] = 'Limit the layout to roughly ten sections — readers skim better.';
        }
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  array<string, array<int, string>>  $errors
     */
    private static function validateOptionalTypography(array $block, array &$errors, string $fontKey, string $colorKey, string $labelPrefix): void
    {
        $rawFont = array_key_exists($fontKey, $block) ? trim((string) $block[$fontKey]) : '';
        if ($rawFont !== '' && ! in_array(strtolower($rawFont), self::FONT_KEYS, true)) {
            $errors['content_document'][] = $labelPrefix.' typeface is not supported.';
        }

        self::rejectInvalidThemeToken($errors, isset($block[$colorKey]) ? trim((string) $block[$colorKey]) : '', $colorKey);
    }

    /** @param  array<string, mixed>  $block */
    private static function validateOptionalFontSize(array &$block, array &$errors): void
    {
        $raw = array_key_exists('font_size', $block) ? trim((string) $block['font_size']) : 'default';
        if ($raw === '') {
            $block['font_size'] = 'default';

            return;
        }

        $k = strtolower($raw);
        if (! in_array($k, self::FONT_SIZE_KEYS, true)) {
            $errors['content_document'][] = 'Invalid font size preset.';
        } else {
            $block['font_size'] = $k;
        }
    }

    /** @param  array<string, mixed>  $block */
    private static function validateOptionalTextAlign(array &$block, array &$errors): void
    {
        $raw = array_key_exists('text_align', $block) ? trim((string) $block['text_align']) : 'left';
        if ($raw === '') {
            $block['text_align'] = 'left';

            return;
        }

        $k = strtolower($raw);
        if (! in_array($k, self::TEXT_ALIGN_KEYS, true)) {
            $errors['content_document'][] = 'Invalid text alignment.';
        } else {
            $block['text_align'] = $k;
        }
    }

    private static function rejectInvalidThemeToken(array &$errors, string $raw, string $fieldLabel): void
    {
        if ($raw === '') {
            return;
        }

        if (self::parseThemeTokenToHex($raw) === false) {
            $errors['content_document'][] = 'Invalid color · use a preset or #RRGGBB only.';
        }
    }

    /**
     * Resolve preset key or #RRGGBB to lowercase hex including # prefix, null for default/inherit, false if invalid string.
     */
    private static function parseThemeTokenToHex(?string $raw): string|false|null
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $lk = strtolower($raw);
        if (array_key_exists($lk, self::COLOR_PRESETS)) {
            $v = self::COLOR_PRESETS[$lk];

            return $v !== null ? strtolower((string) $v) : null;
        }

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $raw)) {
            return '#'.substr(strtolower($raw), -6);
        }

        return false;
    }

    private static function fontStackCss(?string $key): string
    {
        $k = strtolower(trim((string) ($key ?: 'default')));

        return match ($k) {
            'sans' => "font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;",
            'serif' => "font-family:Georgia,'Times New Roman',Times,serif;",
            'modern' => 'font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;',
            'mono' => 'font-family:ui-monospace,Consolas,Menlo,Monaco,monospace;',
            default => '',
        };
    }

    private static function fontSizePxCss(?string $raw): string
    {
        $k = strtolower(trim((string) ($raw ?? 'default')));
        if ($k === '' || $k === 'default') {
            return '';
        }

        if (! in_array($k, self::FONT_SIZE_KEYS, true)) {
            return '';
        }

        return 'font-size:'.$k.'px;';
    }

    /** Text alignment inside the block wrapper. */
    private static function textAlignCss(?string $raw): string
    {
        $a = strtolower(trim((string) ($raw ?? 'left')));
        if ($a === '' || $a === 'left') {
            return '';
        }

        return in_array($a, self::TEXT_ALIGN_KEYS, true) ? 'text-align:'.$a.';' : '';
    }

    /**
     * @param  string|null  $fallbackHexWhenUnsetOrDefaultPreset  Heading uses #020617; paragraph omit with null for inherit.
     */
    private static function textColorCssFromToken(?string $raw, ?string $fallbackHexWhenUnsetOrDefaultPreset): string
    {
        $t = trim((string) ($raw ?? ''));
        if ($t === '') {
            return $fallbackHexWhenUnsetOrDefaultPreset !== null ? 'color:'.$fallbackHexWhenUnsetOrDefaultPreset.';' : '';
        }

        $hex = self::parseThemeTokenToHex($t);
        if ($hex === false) {
            return '';
        }
        if ($hex !== null) {
            return 'color:'.$hex.';';
        }

        return $fallbackHexWhenUnsetOrDefaultPreset !== null ? 'color:'.$fallbackHexWhenUnsetOrDefaultPreset.';' : '';
    }

    private static function backgroundCssFromToken(?string $raw, string $fallbackHex): string
    {
        $t = trim((string) ($raw ?? ''));
        if ($t === '') {
            return 'background-color:'.$fallbackHex.';';
        }

        $hexResolved = self::parseThemeTokenToHex($t);
        if ($hexResolved === false) {
            return 'background-color:'.$fallbackHex.';';
        }

        $useHex = ($hexResolved !== null) ? $hexResolved : $fallbackHex;

        return 'background-color:'.$useHex.';';
    }

    /**
     * Optional padded highlight strip behind headings / paragraphs (not button fill).
     */
    private static function contentBlockHighlightCss(?string $raw): string
    {
        $t = trim((string) ($raw ?? ''));
        if ($t === '' || strtolower($t) === 'none' || strtolower($t) === 'default' || strtolower($t) === 'inherit') {
            return '';
        }

        $hex = self::parseThemeTokenToHex($t);
        if ($hex === false || $hex === null) {
            return '';
        }

        return 'background-color:'.$hex.';padding:14px 16px;border-radius:12px;display:block;box-sizing:border-box;';
    }

    private static function buttonColorFragment(?string $raw, string $fallbackHex, string $prop): string
    {
        $t = trim((string) ($raw ?? ''));
        if ($t === '') {
            return $prop.':'.$fallbackHex.';';
        }

        $hexResolved = self::parseThemeTokenToHex($t);
        if ($hexResolved === false) {
            return $prop.':'.$fallbackHex.';';
        }

        $useHex = ($hexResolved !== null) ? $hexResolved : $fallbackHex;

        return $prop.':'.$useHex.';';
    }

    private static function prependHttpPlaceholder(string $url): string
    {
        $u = trim($url);
        if (str_starts_with($u, 'http') || str_starts_with($u, '{')) {
            return str_replace('{', 'x', $u);
        }

        return 'https://'.ltrim($u, '/');
    }

    /**
     * Check if URL is likely a template with placeholders (relax validation).
     */
    private static function looksLikeTaggedUrl(string $url): bool
    {
        return preg_match('/\{rsvp_link\}|\{feedback_link\}|\{meeting_link\}/', $url) === 1;
    }

    public static function buildPlainPreview(array $doc, int $maxLen = 200): string
    {
        $parts = [];
        self::gatherPlainPieces($doc['blocks'] ?? [], $parts);

        return \Illuminate\Support\Str::limit(trim(implode(' ', array_filter($parts))), $maxLen);
    }

    /** @param  array<int, mixed>  $blocks @param  array<int, string>  $pieces */
    private static function gatherPlainPieces(array $blocks, array &$pieces): void
    {
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            switch ($type) {
                case 'heading':
                    $pieces[] = (string) ($block['text'] ?? '');

                    break;
                case 'paragraph':
                    $pieces[] = (string) ($block['text'] ?? '');

                    break;
                case 'callout':
                    $pieces[] = (string) ($block['text'] ?? '');

                    break;
                case 'image':
                    $pieces[] = '[Image]';

                    break;
                case 'button':
                    $pieces[] = '['.trim((string) ($block['label'] ?? '')).']';

                    break;
                case 'section':
                    if (filled($block['title'] ?? '')) {
                        $pieces[] = (string) $block['title'];
                    }
                    if (isset($block['blocks']) && is_array($block['blocks'])) {
                        self::gatherPlainPieces($block['blocks'], $pieces);
                    }

                    break;
            }
        }
    }

    public static function renderForAttendee(array $doc, Message $message, Attendee $attendee, bool $html): string
    {
        return $html
            ? '<div style="font-family:\'Helvetica Neue\',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#0f172a;">'
                .self::renderBlocksInnerHtml($doc['blocks'] ?? [], $message, $attendee)
                .'</div>'
            : self::renderPlain($doc['blocks'] ?? [], $message, $attendee);
    }

    /** @param  array<int, mixed>  $blocks */
    private static function renderBlocksInnerHtml(array $blocks, Message $message, Attendee $attendee): string
    {
        $chunks = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $chunks[] = match ((string) ($block['type'] ?? '')) {
                'heading' => self::chunkHeadingHtml($block, $message, $attendee),
                'paragraph' => self::chunkParagraphHtml($block, $message, $attendee),
                'callout' => self::chunkCalloutHtml($block, $message, $attendee),
                'image' => self::chunkImageHtml($block),
                'button' => self::chunkButtonHtml($block, $message, $attendee),
                'divider' => '<hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">',
                'spacer' => self::chunkSpacerHtml((string) ($block['size'] ?? 'md')),
                'section' => self::chunkSectionHtml($block, $message, $attendee),
                default => '',
            };
        }

        return implode("\n", array_filter($chunks));
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function chunkHeadingHtml(array $block, Message $message, Attendee $attendee): string
    {
        $lvl = max(1, min(3, (int) ($block['level'] ?? 2)));
        $sizes = ['1' => '28px', '2' => '22px', '3' => '18px'];
        $sz = $sizes[(string) $lvl] ?? '22px';
        $fzOverride = strtolower(trim((string) ($block['font_size'] ?? 'default')));
        $fsRule = (($fzOverride !== '' && $fzOverride !== 'default') && in_array($fzOverride, self::FONT_SIZE_KEYS, true))
            ? self::fontSizePxCss($fzOverride)
            : ('font-size:'.$sz.';');
        $font = self::fontStackCss($block['font'] ?? 'default');
        $color = self::textColorCssFromToken($block['color'] ?? '', '#020617');
        $align = self::textAlignCss($block['text_align'] ?? 'left');
        $highlight = self::contentBlockHighlightCss($block['background'] ?? '');
        $inner = '<div style="'.$fsRule.'font-weight:700;line-height:1.25;margin:0;'.$font.$color.$align.'">'.
            self::formatStructuredInlineHtml((string) ($block['text'] ?? ''), $message, $attendee).
            '</div>';

        if ($highlight === '') {
            return '<div style="margin:20px 0 8px 0;">'.$inner.'</div>';
        }

        return '<div style="margin:20px 0 8px 0;"><div style="'.$highlight.'">'.$inner.'</div></div>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function chunkParagraphHtml(array $block, Message $message, Attendee $attendee): string
    {
        $raw = trim((string) ($block['text'] ?? ''));
        $font = self::fontStackCss($block['font'] ?? 'default');
        $fs = self::fontSizePxCss($block['font_size'] ?? 'default');
        $color = self::textColorCssFromToken($block['color'] ?? '', null);
        $align = self::textAlignCss($block['text_align'] ?? 'left');
        $highlight = self::contentBlockHighlightCss($block['background'] ?? '');
        $inner = '<div style="margin:0;'.$font.$fs.$color.$align.'">'.
            self::formatStructuredParagraphHtml($raw, $message, $attendee).
            '</div>';

        if ($highlight === '') {
            return '<div style="margin:0 0 16px;">'.$inner.'</div>';
        }

        return '<div style="margin:0 0 16px;"><div style="'.$highlight.'">'.$inner.'</div></div>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function chunkCalloutHtml(array $block, Message $message, Attendee $attendee): string
    {
        $variant = (string) ($block['variant'] ?? 'tip');
        [$bg, $border] = match ($variant) {
            'warning' => ['#fffbeb', '#f59e0b'],
            'info' => ['#eff6ff', '#2563eb'],
            default => ['#f8fafc', '#64748b'],
        };

        $font = self::fontStackCss($block['font'] ?? 'default');
        $fs = self::fontSizePxCss($block['font_size'] ?? 'default');
        $innerColor = self::textColorCssFromToken($block['color'] ?? '', '#0f172a');
        $align = self::textAlignCss($block['text_align'] ?? 'left');
        $highlight = self::contentBlockHighlightCss($block['background'] ?? '');
        $body = self::formatStructuredParagraphHtml((string) ($block['text'] ?? ''), $message, $attendee);
        $bodyWrapped = $highlight !== ''
            ? '<div style="'.$highlight.'">'.$body.'</div>'
            : $body;

        return '<table role="presentation" width="100%" cellspacing="0" cellpadding="16" '
            .'style="margin:16px 0;border-radius:12px;background:'.$bg.';border:1px solid '.$border.';"><tr><td style="'.$font.$fs.$innerColor.$align.'">'
            .$bodyWrapped
            .'</td></tr></table>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function chunkImageHtml(array $block): string
    {
        $path = (string) ($block['path'] ?? '');
        if (! self::isAllowedImagePath($path)) {
            return '';
        }

        $url = e(Storage::disk('public')->url($path));
        $alt = e((string) ($block['alt'] ?? ''));

        return '<p style="margin:16px 0;"><img src="'.$url.'" alt="'.$alt.'" '
            .'style="max-width:100%;height:auto;border-radius:12px;display:block;"></p>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function chunkButtonHtml(array $block, Message $message, Attendee $attendee): string
    {
        $href = e(self::resolveButtonHref($block, $message, $attendee));
        $label = e(trim((string) ($block['label'] ?? '')));

        $font = self::fontStackCss($block['btn_font'] ?? 'default');
        $bg = self::backgroundCssFromToken($block['btn_bg'] ?? '', '#4f46e5');
        $fg = self::buttonColorFragment($block['btn_text_color'] ?? '', '#ffffff', 'color');

        return '<div style="margin:20px 0;">'
            .'<a href="'.$href.'" style="display:inline-block;padding:12px 22px;'.$bg.$fg.$font
            .'text-decoration:none;border-radius:10px;font-weight:600;font-size:15px;">'
            .$label.'</a></div>';
    }

    private static function chunkSpacerHtml(string $size): string
    {
        $h = match ($size) {
            'sm' => '8px',
            'lg' => '32px',
            default => '16px',
        };

        return '<div style="height:'.$h.';line-height:'.$h.';">&nbsp;</div>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function chunkSectionHtml(array $block, Message $message, Attendee $attendee): string
    {
        $variant = (string) ($block['variant'] ?? 'default');
        [$bg, $border] = match ($variant) {
            'muted' => ['#f8fafc', '#e2e8f0'],
            'accent' => ['#eef2ff', '#c7d2fe'],
            'highlight' => ['#faf5ff', '#e9d5ff'],
            default => ['#ffffff', '#e2e8f0'],
        };

        $title = trim((string) ($block['title'] ?? ''));
        $tf = self::fontStackCss($block['title_font'] ?? 'default');
        $tc = self::textColorCssFromToken($block['title_color'] ?? '', '#64748b');
        $tbg = self::contentBlockHighlightCss($block['title_background'] ?? '');
        $header = $title !== ''
            ? ($tbg === ''
                ? '<div style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:12px;'.$tf.$tc.'">'
                    .e($message->personalizeContentTokens($title, $attendee)).'</div>'
                : '<div style="margin-bottom:12px;"><div style="'.$tbg.'"><div style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin:0;'.$tf.$tc.'">'
                    .e($message->personalizeContentTokens($title, $attendee)).'</div></div></div>')
            : '';

        $inner = self::renderBlocksInnerHtml(is_array($block['blocks'] ?? null) ? $block['blocks'] : [], $message, $attendee);

        return '<div style="margin:20px 0;padding:24px;border-radius:16px;background:'.$bg.';border:1px solid '.$border.';">'
            .$header
            .'<div>'.$inner.'</div></div>';
    }

    /** @param  array<int, mixed>  $blocks */
    private static function renderPlain(array $blocks, Message $message, Attendee $attendee): string
    {
        $lines = [];
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            switch ($type) {
                case 'heading':
                    $lines[] = self::plainLine($block['text'] ?? '', $message, $attendee);
                    $lines[] = '';

                    break;
                case 'paragraph':
                    $lines[] = self::plainLine($block['text'] ?? '', $message, $attendee);
                    $lines[] = '';

                    break;
                case 'callout':
                    $lines[] = self::plainLine($block['text'] ?? '', $message, $attendee);
                    $lines[] = '';

                    break;
                case 'image':
                    $path = (string) ($block['path'] ?? '');
                    if (self::isAllowedImagePath($path)) {
                        $lines[] = Storage::disk('public')->url($path);
                        $lines[] = '';
                    }

                    break;
                case 'button':
                    $url = self::resolveButtonHref($block, $message, $attendee);
                    $lines[] = trim((string) ($block['label'] ?? '')).': '.$url;
                    $lines[] = '';

                    break;
                case 'divider':
                    $lines[] = '—';
                    $lines[] = '';

                    break;
                case 'spacer':
                    $lines[] = '';

                    break;
                case 'section':
                    if (filled($block['title'] ?? '')) {
                        $lines[] = self::plainLine((string) $block['title'], $message, $attendee);
                    }
                    $lines = array_merge($lines, explode("\n", self::renderPlain(is_array($block['blocks'] ?? null) ? $block['blocks'] : [], $message, $attendee)));

                    break;
            }
        }

        return trim(implode("\n", $lines));
    }

    private static function plainLine(string $text, Message $message, Attendee $attendee): string
    {
        return $message->personalizeContentTokens($text, $attendee);
    }

    private static function escapePersonalizedHtml(string $text, Message $message, Attendee $attendee): string
    {
        $t = $message->personalizeContentTokens($text, $attendee);

        return e($t);
    }

    /**
     * After htmlspecialchars, turn a small intentional subset into safe inline tags for email HTML.
     * Uses **bold** and _italic_ (both non-greedy, single-line).
     */
    private static function applySimpleRichTextSpans(string $escapedPersonalizedPlain): string
    {
        $s = $escapedPersonalizedPlain;
        $s = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $s) ?? $s;
        $s = preg_replace('/\+\+([^+\n]+)\+\+/', '<span style="text-decoration:underline">$1</span>', $s) ?? $s;
        $s = preg_replace('/_([^_\n]+)_/', '<em>$1</em>', $s) ?? $s;
        $s = preg_replace_callback(
            '/\[([^\]\n]+)\]\((https?:\/\/[^\s)]+)\)/',
            static function (array $m): string {
                $href = $m[2];
                if (! preg_match('#^https?://#i', $href)) {
                    return htmlspecialchars($m[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                return '<a href="'.htmlspecialchars($href, ENT_QUOTES | ENT_HTML5, 'UTF-8').'" style="color:#2563eb;text-decoration:underline">'.$m[1].'</a>';
            },
            $s
        ) ?? $s;

        return $s;
    }

    /**
     * After inline spans, turn line-based markers into blocks: --- on its own line (HR), lines starting with &gt; (blockquote).
     * Plain runs are joined with {@see nl2br()}.
     */
    private static function richEscapedBodyWithBlocks(string $escapedWithSpans): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $escapedWithSpans) ?: [];

        $parts = [];
        $quoteLines = [];
        $plainLines = [];

        $flushQuote = function () use (&$parts, &$quoteLines): void {
            if ($quoteLines === []) {
                return;
            }

            $parts[] = '<blockquote style="margin:12px 0;padding:10px 14px;border-left:4px solid #cbd5e1;background:#f8fafc;color:#334155;">'
                .implode('<br>', $quoteLines)
                .'</blockquote>';
            $quoteLines = [];
        };

        $flushPlain = function () use (&$parts, &$plainLines): void {
            if ($plainLines === []) {
                return;
            }

            $parts[] = nl2br(implode("\n", $plainLines));
            $plainLines = [];
        };

        foreach ($lines as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed !== '' && preg_match('/^-{3,}$/', $trimmed) === 1) {
                $flushQuote();
                $flushPlain();
                $parts[] = '<hr style="border:none;border-top:1px solid #e2e8f0;margin:14px 0;">';

                continue;
            }

            if (preg_match('/^&gt;\s?(.*)$/', (string) $line, $m) === 1) {
                $flushPlain();
                $quoteLines[] = $m[1];

                continue;
            }

            $flushQuote();
            $plainLines[] = $line;
        }

        $flushQuote();
        $flushPlain();

        return implode('', $parts);
    }

    private static function formatStructuredParagraphHtml(string $raw, Message $message, Attendee $attendee): string
    {
        $escaped = self::escapePersonalizedHtml((string) $raw, $message, $attendee);
        $spanned = self::applySimpleRichTextSpans($escaped);

        return self::richEscapedBodyWithBlocks($spanned);
    }

    /** @uses applySimpleRichTextSpans() */
    private static function formatStructuredInlineHtml(string $raw, Message $message, Attendee $attendee): string
    {
        $escaped = self::escapePersonalizedHtml($raw, $message, $attendee);

        return self::applySimpleRichTextSpans($escaped);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function resolveButtonHref(array $block, Message $message, Attendee $attendee): string
    {
        $kind = (string) ($block['kind'] ?? 'custom');

        return match ($kind) {
            'rsvp' => $message->getRsvpLinkForAttendee($attendee),
            'feedback' => $message->getFeedbackLinkForAttendee($attendee),
            'meeting' => (string) ($message->event->meeting_link ?? ''),
            default => $message->personalizeContentTokens((string) ($block['custom_url'] ?? ''), $attendee),
        };
    }
}
