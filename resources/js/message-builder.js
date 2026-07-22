/**
 * Structured message blocks UI. Requires sibling elements:
 * — #structured-upload-staging (stable, never innerHTML cleared)
 * — #message-builder-mount #content_document (hidden JSON)
 */

const stagedImages = new Map();

function uuid() {
    return crypto.randomUUID();
}

function escToneAttr(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function escapeHtmlText(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;');
}

function escapeAttrText(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

const DEFAULT_SECTION = () => ({
    id: uuid(),
    type: 'section',
    variant: 'muted',
    title: 'Section',
    title_font: 'default',
    title_color: 'default',
    title_background: 'none',
    blocks: [
        {
            id: uuid(),
            type: 'heading',
            level: 2,
            text: 'Headline readers see first',
            font: 'default',
            color: 'default',
            background: 'none',
            font_size: 'default',
            text_align: 'left',
        },
        { id: uuid(), type: 'spacer', size: 'md' },
        {
            id: uuid(),
            type: 'paragraph',
            text: 'Hi {name},\n\nAdd your main message here. Use tags like {event_name}, {venue}, or {rsvp_link}.',
            font: 'default',
            color: 'default',
            background: 'none',
            font_size: 'default',
            text_align: 'left',
        },
    ],
});

const FONT_OPTS = [
    ['default', 'Default (recommended)'],
    ['sans', 'Clean sans-serif'],
    ['serif', 'Editorial serif'],
    ['modern', 'Modern system UI'],
    ['mono', 'Technical mono'],
];

const TEXT_COLOR_OPTS = [
    ['default', 'Default'],
    ['inherit', 'Follow container'],
    ['ink', 'Ink black'],
    ['slate', 'Slate'],
    ['muted', 'Muted gray'],
    ['white', 'White'],
    ['indigo', 'Indigo'],
    ['violet', 'Violet'],
    ['rose', 'Rose'],
    ['emerald', 'Emerald'],
    ['amber', 'Amber'],
    ['sky', 'Sky'],
    ['orange', 'Rust orange'],
    ['custom', 'Custom hex…'],
];

const BG_SURFACE_OPTS = [
    ['none', 'No highlight'],
    ['soft', 'Light gray strip'],
    ['indigo_mist', 'Light indigo'],
    ['rose_mist', 'Light rose'],
    ['emerald_mist', 'Light green'],
    ['white', 'White strip'],
    ['slate', 'Slate strip'],
    ['indigo', 'Indigo strip'],
    ['custom', 'Custom hex…'],
];

const BUTTON_FILL_OPTS = [
    ['default', 'Brand (indigo)'],
    ['indigo', 'Indigo'],
    ['slate', 'Slate'],
    ['ink', 'Ink'],
    ['emerald', 'Emerald'],
    ['rose', 'Rose'],
    ['violet', 'Violet'],
    ['sky', 'Sky'],
    ['orange', 'Orange'],
    ['white', 'White'],
];

function fontSelectHtml(selected, field) {
    field = field || 'font';

    return (
        `<label class="block text-xs font-semibold text-slate-700 mb-1">Font</label>` +
        `<select data-field="${field}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">` +
        FONT_OPTS.map(([k, lab]) => `<option value="${k}" ${selected === k ? 'selected' : ''}>${lab}</option>`).join('') +
        `</select>`
    );
}

/** @param {string} fallbackPreset when value blank */
function toneUiState(initialVal, fallbackPreset) {
    const raw = String(initialVal ?? '').trim();
    if (raw === '') {
        return { preset: fallbackPreset, hex: '' };
    }

    const isHex = /^#[0-9a-fA-F]{6}$/.test(raw);
    const low = raw.toLowerCase();

    return {
        preset: isHex ? 'custom' : (low || fallbackPreset),
        hex: isHex ? low : '',
    };
}

function appearanceToneHtml(initialVal, toneFieldKey, label, presets, fallbackPreset, hintLine) {
    const st = toneUiState(initialVal, fallbackPreset);
    const sp = String(st.preset).toLowerCase();
    const opts = presets.map(([k, lab]) => `<option value="${k}" ${sp === String(k).toLowerCase() ? 'selected' : ''}>${lab}</option>`).join('');
    const hexHidden = st.preset === 'custom' ? '' : 'hidden';

    const hintHtml = hintLine ? `<p class="text-xs text-slate-500 mb-2">${hintLine}</p>` : '';

    return (
        `<div data-tone-cluster="${escToneAttr(toneFieldKey)}" data-tone-empty-fallback="${escToneAttr(fallbackPreset)}">` +
        `<label class="block text-xs font-semibold text-slate-700 mb-1">${label}</label>` +
        hintHtml +
        `<select data-tone-preset class="tone-preset w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium shadow-sm focus:border-indigo-500">` +
        `${opts}</select>` +
        `<input type="text" maxlength="9" autocomplete="off" data-tone-hex placeholder="#RRGGBB" value="${escToneAttr(st.hex)}" ` +
        `class="mt-2 w-full rounded-lg border border-amber-300 px-3 py-2 text-sm font-mono placeholder:text-slate-500 ${hexHidden} bg-amber-50/80">` +
        `</div>`
    );
}

function toneControlsHtml(initialVal, toneFieldKey) {
    return appearanceToneHtml(initialVal, toneFieldKey, 'Text colour', TEXT_COLOR_OPTS, 'default', '');
}

function backgroundControlsHtml(initialVal, toneFieldKey) {
    return appearanceToneHtml(
        initialVal,
        toneFieldKey,
        'Strip background',
        BG_SURFACE_OPTS,
        'none',
        '',
    );
}

const FONT_SIZE_OPTS = [
    ['default', 'Default'],
    ['14', '14px'],
    ['15', '15px'],
    ['16', '16px'],
    ['18', '18px'],
    ['20', '20px'],
    ['22', '22px'],
    ['24', '24px'],
    ['28', '28px'],
    ['32', '32px'],
    ['36', '36px'],
];

const DEFAULT_MERGE_TAGS = [
    { tag: '{name}', label: 'Attendee name' },
    { tag: '{event_name}', label: 'Event name' },
    { tag: '{event_time}', label: 'Event date and time' },
    { tag: '{venue}', label: 'Venue' },
    { tag: '{meeting_link}', label: 'Meeting link' },
    { tag: '{rsvp_link}', label: 'RSVP link' },
    { tag: '{feedback_link}', label: 'Feedback link' },
];

function getMergeTagsForRte() {
    const t = window.__ISATA__?.mergeTags;

    return Array.isArray(t) && t.length ? t : DEFAULT_MERGE_TAGS;
}

/** @param {HTMLElement} rteRoot */
function rteSyncQuickColorFromBlock(rteRoot, colorVal) {
    const q = rteRoot.querySelector('[data-rte-quick-color]');
    if (! q) return;
    const raw = String(colorVal ?? 'default').trim().toLowerCase();
    const isHex = /^#[0-9a-f]{6}$/.test(raw);
    const want = isHex ? '__hex__' : raw;

    if ([...q.options].some((o) => o.value === want)) q.value = want;
}

/** Sync preset + hex widgets in the collapsible panel after toolbar picks. */
function rteSyncToneClusterDom(rteRoot, toneClusterField, rawVal, fallbackPreset) {
    const cluster = rteRoot.querySelector(`[data-tone-cluster="${toneClusterField}"]`);

    if (! cluster) return;
    const sel = cluster.querySelector('.tone-preset');
    const hex = cluster.querySelector('[data-tone-hex]');

    if (! sel || ! hex) return;
    const st = toneUiState(rawVal, fallbackPreset);

    sel.value = st.preset;
    hex.value = st.hex ? st.hex : '';
    if (st.preset === 'custom') {
        hex.classList.remove('hidden');
    } else {
        hex.classList.add('hidden');
    }
}

/** @param {HTMLElement} rteRoot */
function rteSyncQuickBgFromBlock(rteRoot, bgVal) {
    const q = rteRoot.querySelector('[data-rte-quick-bg]');

    if (! q) return;
    const raw = String(bgVal ?? 'none').trim().toLowerCase();
    const isHex = /^#[0-9a-f]{6}$/.test(raw);
    const want = isHex ? '__hex_bg__' : raw;

    if ([...q.options].some((o) => o.value === want)) q.value = want;
}

function rteToolbarBtnClass() {
    return 'rte-cms-toolbar-btn inline-flex min-h-[36px] min-w-[36px] shrink-0 items-center justify-center rounded border border-neutral-300 bg-white text-[13px] font-semibold text-neutral-800 shadow-sm hover:bg-neutral-50';
}

function rteCmsTbSep() {
    return `<span class="mx-1 h-6 w-px shrink-0 bg-neutral-300" aria-hidden="true"></span>`;
}

/** @returns {string} */
function rteParagraphStyleSelectHtml(block, kind) {
    if (kind === 'heading') {
        const lv = Number(block.level ?? 2);

        return (
            `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-white px-1 py-0.5 shadow-sm" title="Style">` +
            `<span class="hidden pl-1 text-neutral-600 sm:inline" aria-hidden="true">¶</span>` +
            `<select data-field="level" class="max-w-[10rem] border-0 bg-transparent py-1 pl-1 pr-2 text-xs font-medium text-neutral-900 focus:ring-0">` +
            `<option value="1" ${lv === 1 ? 'selected' : ''}>Heading 1</option>` +
            `<option value="2" ${lv === 2 ? 'selected' : ''}>Heading 2</option>` +
            `<option value="3" ${lv === 3 ? 'selected' : ''}>Heading 3</option>` +
            `</select></span>`
        );

    }

    if (kind === 'callout') {
        return (
            `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-neutral-50 px-1 py-0.5 opacity-75" title="Block type">` +
            `<span class="hidden pl-1 text-neutral-600 sm:inline" aria-hidden="true">¶</span>` +
            `<select disabled class="cursor-not-allowed border-0 bg-transparent py-1 text-xs font-medium text-neutral-600"><option>Callout</option></select></span>`
        );


    }


    return (
        `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-neutral-50 px-1 py-0.5 opacity-75" title="Block type">` +
        `<span class="hidden pl-1 text-neutral-600 sm:inline" aria-hidden="true">¶</span>` +
        `<select disabled class="cursor-not-allowed border-0 bg-transparent py-1 text-xs font-medium text-neutral-600"><option>Paragraph</option></select></span>`
    );
}

/** @returns {string} */
function rteCmsToolbarRow1(block, kind) {
    const b = rteToolbarBtnClass();

    const fz = block.font_size || 'default';
    const al = block.text_align || 'left';

    const fsel = block.font || 'default';
    const dis = `${b} opacity-40 cursor-not-allowed hover:bg-white`;

    return (
        `<div class="flex flex-wrap items-center gap-1 px-2 py-1">` +
        rteParagraphStyleSelectHtml(block, kind) +
        `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-white px-1 py-0.5 shadow-sm" title="Font">` +
        `<span class="hidden pl-1 text-xs font-bold text-neutral-500 sm:inline">T</span>` +
        `<select data-field="font" class="max-w-[7.5rem] border-0 bg-transparent py-1 text-xs font-medium focus:ring-0">` +
        FONT_OPTS.map(([k, lab]) => `<option value="${escapeAttrText(k)}" ${fsel === k ? 'selected' : ''}>${escapeHtmlText(lab)}</option>`).join('') +
        `</select></span>` +
        `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-white px-1 py-0.5 shadow-sm" title="Font size">` +
        `<span class="hidden pl-1 text-xs text-neutral-500 sm:inline" aria-hidden="true">↕</span>` +
        `<select data-field="font_size" class="border-0 bg-transparent py-1 text-xs font-medium focus:ring-0">` +
        FONT_SIZE_OPTS.map(([k, lab]) => `<option value="${escapeAttrText(k)}" ${fz === k ? 'selected' : ''}>${escapeHtmlText(lab)}</option>`).join('') +
        `</select></span>` +
        `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-white px-1 py-0.5 shadow-sm" title="Text color">` +
        `<span class="pl-1 text-sm font-bold text-neutral-800 underline decoration-4 decoration-neutral-900" aria-hidden="true">A</span>` +
        rteQuickColorSelectHtml(block) +
        `</span>` +
        `<span class="inline-flex items-center gap-1 rounded border border-neutral-300 bg-white px-1 py-0.5 shadow-sm" title="Highlight strip behind text">` +
        `<span class="pl-1 text-neutral-700" aria-hidden="true"></span>` +
        rteQuickBgSelectHtml(block) +
        `</span>` +
        rteCmsTbSep() +
        `<button type="button" data-rich-cmd="clearMd" class="${b}" title="Clear inline marks">⌗</button>` +
        rteCmsTbSep() +
        `<button type="button" data-rich-cmd="bold" class="${b}" title="Bold (**text**)">B</button>` +
        `<button type="button" data-rich-cmd="italic" class="${b} italic font-serif" title="Italic (_text_)">I</button>` +
        `<button type="button" data-rich-cmd="underline" class="${b} underline decoration-2 underline-offset-2" title="Underline (++text++)">U</button>` +
        rteCmsTbSep() +
        `<button type="button" data-rich-cmd="number" class="${b}" title="Numbered list">1.</button>` +
        `<button type="button" data-rich-cmd="bullet" class="${b}" title="Bulleted list">•</button>` +
        rteCmsTbSep() +
        `<span class="inline-flex items-center rounded border border-neutral-300 bg-white px-1 py-0.5 shadow-sm" title="Alignment">` +
        `<span class="hidden pl-1 text-neutral-500 sm:inline">≡</span>` +
        `<select data-field="text_align" class="max-w-[4.75rem] border-0 bg-transparent py-1 text-xs font-medium focus:ring-0">` +
        [
            ['left', '◀'],
            ['center', '▣'],
            ['right', '▶'],
            ['justify', '▤'],
        ]
            .map(([k]) => `<option value="${k}" ${al === k ? 'selected' : ''}>${k.charAt(0).toUpperCase() + k.slice(1)}</option>`)
            .join('') +
        `</select></span>` +
        `<button type="button" data-rich-cmd="indentLess" class="${b}" title="Outdent">⟨</button>` +
        `<button type="button" data-rich-cmd="indentMore" class="${b}" title="Indent">⟩</button>` +
        rteCmsTbSep() +
        `<button type="button" disabled class="${dis}" title="Use Image block below">IMG</button>` +
        `<button type="button" disabled class="${dis}" title="Not available in email snippets">Vid</button>` +
        `<button type="button" disabled class="${dis}" title="Not available in email snippets">Aud</button>` +
        `<button type="button" disabled class="${dis}" title="Not available in email snippets">File</button>` +
        `<button type="button" data-rich-cmd="blockquote" class="${b}" title="Quote line (&gt; )">❝</button>` +
        `<button type="button" data-rich-cmd="link" class="${b}" title="Link [label](https://…)">🔗</button>` +
        `<button type="button" disabled class="${dis}" title="Tables skipped for email simplicity">⊞</button>` +
        `</div>`
    );
}

/** @returns {string} */
function rteCmsToolbarRow2() {
    const b = rteToolbarBtnClass();

    const dis = `${b} opacity-35 cursor-not-allowed hover:bg-white`;
    const opts = getMergeTagsForRte()
        .map((x) => `<option value="${escapeAttrText(x.tag)}">${escapeHtmlText(x.label)} (${escapeHtmlText(x.tag)})</option>`)
        .join('');


    return (
        `<div class="flex flex-wrap items-center gap-1 px-2 py-1">` +
        `<button type="button" data-rich-cmd="hr" class="${b}" title="Horizontal rule ---">⊖</button>` +
        rteCmsTbSep() +
        `<button type="button" data-rich-cmd="eraseSame" class="${b}" title="Clear formatting (selection)">▫</button>` +
        rteCmsTbSep() +
        `<button type="button" disabled class="${dis}" title="Undo (coming soon)">↺</button>` +
        `<button type="button" disabled class="${dis}" title="Redo (coming soon)">↻</button>` +
        rteCmsTbSep() +
        `<button type="button" data-rich-cmd="codeView" class="${b}" title="Toggle monospace">${escapeHtmlText('</>')}</button>` +
        `<button type="button" data-rich-cmd="fullscreen" class="${b}" title="Focus mode">⤢</button>` +
        `<span class="ml-auto hidden min-w-0 shrink sm:inline-flex sm:max-w-[14rem]" title="Personalization merge tags">` +
        `<select data-merge-insert class="w-full max-w-[14rem] truncate rounded border border-neutral-300 bg-white px-1 py-1 text-[11px] font-medium"><option value="">Merge tag…</option>${opts}</select>` +
        `</span>` +
        `</div>` +
        `<div class="border-t border-neutral-200 px-2 py-1 sm:hidden">` +
        `<select data-merge-insert class="w-full rounded border border-neutral-300 bg-white px-2 py-1 text-xs"><option value="">Insert merge tag…</option>${opts}</select>` +
        `</div>`
    );

}

/** Two-row CMS toolbar over a bordered editor (screenshot-aligned). */
function cmsStructuredContentHtml(block, { multiline, kind, metaBeforeContent }) {

    /** @returns {string} */
    function advColours() {
        return (
            `<details class="group border-t border-neutral-200 bg-neutral-50">` +
            `<summary class="cursor-pointer px-3 py-2 text-xs font-medium text-neutral-600 hover:bg-neutral-100">Colour &amp; strip (# hex)</summary>` +
            `<div class="grid gap-3 border-t border-neutral-200 bg-white px-3 py-3 sm:grid-cols-2">` +
            `${toneControlsHtml(block.color, 'color')}` +
            `${backgroundControlsHtml(block.background, 'background')}` +
            `</div>` +
            `<div class="border-t border-neutral-100 bg-white px-3 pb-3">` +
            `<button type="button" data-rte-clear-appearance class="rounded border border-neutral-300 bg-white px-3 py-1 text-xs font-semibold text-neutral-800 hover:bg-neutral-50">Reset typography &amp; colours</button>` +
            `</div></details>`
        );


    }


    const field = multiline
        ? `<textarea data-rte-body data-field="text" rows="10" class="min-h-[220px] w-full resize-y border-0 p-4 text-[15px] leading-relaxed text-neutral-900 placeholder:text-neutral-400 focus:ring-0" placeholder="Your content here…">${escapeHtmlText(block.text || '')}</textarea>`
        : `<input type="text" data-rte-body data-field="text" class="w-full border-0 px-4 py-3 text-[15px] text-neutral-900 placeholder:text-neutral-400 focus:ring-0" value="${escapeAttrText(block.text || '')}" placeholder="Your content here…">`;

    const metaBit = metaBeforeContent || '';

    return (
        `<div data-rte-root class="space-y-2">` +
        `${metaBit}` +
        `<p class="text-base font-bold text-neutral-900">Content</p>` +
        `<div data-rte-shell class="overflow-hidden rounded border border-neutral-300 bg-white shadow-sm">` +
        `<div class="border-b border-neutral-200 bg-neutral-50">${rteCmsToolbarRow1(block, kind)}</div>` +
        `<div class="border-b border-neutral-200 bg-neutral-50">${rteCmsToolbarRow2()}</div>` +
        field +
        advColours() +
        `</div></div>`
    );


}

/** Plain “Message body” — mirrors structured Content chrome (toolbars disabled where not applicable). */
function plainClassicRichShellHtml() {
    const stub = { font: 'default', color: 'default', background: 'none', font_size: 'default', text_align: 'left' };

    return (
        `<div class="space-y-2">` +
        `<p class="text-base font-bold text-neutral-900">Content</p>` +
        `<div data-plain-rte-shell class="overflow-hidden rounded border border-neutral-300 bg-white shadow-sm">` +
        `<div class="border-b border-neutral-200 bg-neutral-50">${rteCmsToolbarRow1(stub, 'paragraph')}</div>` +
        `<div class="border-b border-neutral-200 bg-neutral-50">${rteCmsToolbarRow2()}</div>` +
        `<div data-plain-body-slot></div>` +
        `</div></div>`
    );
}

/** @param {HTMLElement|null} shell @param {HTMLTextAreaElement|null} ta */
function togglePlainFullscreen(shell, ta) {
    if (! shell) return;

    const on = shell.classList.toggle('rte-cms-fullscreen');

    if (on) {

        shell.classList.add(
            'fixed','z-[100]','inset-4','flex','flex-col','rounded-lg','shadow-2xl','ring-1','ring-neutral-300',
        );


        if (ta && ta.tagName === 'TEXTAREA') {

            ta.classList.add('min-h-[50vh]', 'flex-1');

        }

        document.body.classList.add('overflow-hidden');
    } else {

        shell.classList.remove(
            'fixed','z-[100]','inset-4','flex','flex-col','rounded-lg','shadow-2xl','ring-1','ring-neutral-300',
        );


        if (ta && ta.tagName === 'TEXTAREA') {

            ta.classList.remove('min-h-[50vh]', 'flex-1');

        }

        document.body.classList.remove('overflow-hidden');

    }


}

/** @param {HTMLElement} anchor @param {HTMLTextAreaElement} ta */

function bindPlainClassicEditor(anchor, ta) {
    const shell = anchor.querySelector('[data-plain-rte-shell]');

    anchor.querySelectorAll('[data-field="font"], [data-field="font_size"], [data-rte-quick-color], [data-rte-quick-bg]').forEach((el) => {

        el.disabled = true;

        el.classList.add('opacity-50', 'cursor-not-allowed');

        el.setAttribute('title', 'Use Structured layout to apply font & colours in email HTML.');

    });

    const align = anchor.querySelector('[data-field="text_align"]');


    if (align) {


        align.addEventListener('change', () => {
            ta.style.textAlign = align.value;

        });

        ta.style.textAlign = align.value;

    }

    anchor.querySelectorAll('[data-rich-cmd]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const cmd = btn.getAttribute('data-rich-cmd') || '';

            if (cmd === 'fullscreen') {
                togglePlainFullscreen(shell, ta);

                return;
            }

            if (cmd === 'codeView') {
                ta.classList.toggle('font-mono');


                ta.classList.toggle('text-[13px]');

                return;
            }

            rteApplyRichCmd(ta, cmd);

        });


    });


    anchor.querySelectorAll('[data-merge-insert]').forEach((mergeSel) => {


        mergeSel.addEventListener('change', () => {

            const m = /** @type {HTMLSelectElement} */ (mergeSel);

            const v = m.value;

            if (! v) return;

            rteInsertAtCaret(ta, v);

            m.selectedIndex = 0;

        });

    });

    anchor.addEventListener('focusin', (e) => {


        const t = e.target;

        if (t === ta) window.__lastStructuredField = ta;

    });

}

/** When Plain text mode is chosen, reuse the CMS-style editor chrome on #content. */


function ensurePlainTextClassicEditor() {

    const textType = window.__ISATA__?.contentTypeText;
    /** @type {HTMLInputElement|null} */

    
    const typeRadio = /** @type {HTMLInputElement|null} */ (document.querySelector('input[name="content_type"]:checked'));

    const anchor = document.getElementById('plain-text-editor-anchor');


    const wrap = document.getElementById('classic-content-wrap');

    const ta = /** @type {HTMLTextAreaElement|null} */ (document.getElementById('content'));

    if (! anchor || ! ta) return;


    const isPlain = typeRadio && String(typeRadio.value) === String(textType ?? '');

    const resetTextareaPresentation = () => {

        ta.className =
            'mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500';


        ta.removeAttribute('placeholder');

        ta.style.textAlign = '';

    };

    if (! isPlain) {
        anchor.classList.add('hidden');

        if (anchor.contains(ta) && wrap) {

            wrap.insertBefore(ta, anchor.nextSibling);

        }

        anchor.innerHTML = '';
        delete anchor.dataset.plainChrome;
        resetTextareaPresentation();


        return;
    }


    anchor.classList.remove('hidden');

    if (anchor.dataset.plainChrome === '1') return;



    anchor.dataset.plainChrome = '1';


    anchor.innerHTML = plainClassicRichShellHtml();

    const slot = anchor.querySelector('[data-plain-body-slot]');

    if (! slot) return;


    ta.className =
        'min-h-[220px] w-full resize-y border-0 p-4 text-[15px] leading-relaxed text-neutral-900 placeholder:text-neutral-400 focus:ring-0 focus:outline-none';

    ta.setAttribute('placeholder', 'Your content here…');

    slot.replaceWith(ta);

    bindPlainClassicEditor(anchor, ta);


}

function rteQuickColorSelectHtml(block) {
    const raw = String(block.color ?? 'default').trim().toLowerCase();
    const hexPick = /^#[0-9a-f]{6}$/.test(raw);
    const customOpt = hexPick ? '<option value="__hex__" selected>Custom (#)</option>' : '';
    const body = TEXT_COLOR_OPTS.filter(([k]) => k !== 'custom')
        .map(
            ([k, lab]) =>
                `<option value="${escapeAttrText(k)}" ${! hexPick && raw === String(k).toLowerCase() ? 'selected' : ''}>${escapeHtmlText(lab)}</option>`,
        )
        .join('');

    return `<select data-rte-quick-color title="Text colour presets (custom hex via panel below)" class="max-w-[108px] cursor-pointer truncate border-0 bg-transparent py-1 pr-6 text-[11px] font-medium text-neutral-900">${customOpt}${body}</select>`;
}

function rteQuickBgSelectHtml(block) {
    const raw = String(block.background ?? 'none').trim().toLowerCase();
    const hexPick = /^#[0-9a-f]{6}$/.test(raw);
    const customOpt = hexPick ? '<option value="__hex_bg__" selected>Custom (#)</option>' : '';
    const body = BG_SURFACE_OPTS.filter(([k]) => k !== 'custom')
        .map(
            ([k, lab]) =>
                `<option value="${escapeAttrText(k)}" ${! hexPick && raw === String(k).toLowerCase() ? 'selected' : ''}>${escapeHtmlText(lab)}</option>`,
        )
        .join('');

    return `<select data-rte-quick-bg title="Strip background presets" class="max-w-[98px] cursor-pointer truncate border-0 bg-transparent py-1 pr-6 text-[11px] font-medium text-neutral-900">${customOpt}${body}</select>`;
}

function rteStripMarkdownMarkers(s) {
    return String(s)
        .replace(/\*\*([^*]+)\*\*/g, '$1')
        .replace(/\+\+([^+]+)\+\+/g, '$1')
        .replace(/_([^_]+)_/g, '$1');
}

function rteInsertAtCaret(el, chunk) {
    if (! el) return;

    const start = el.selectionStart ?? 0;
    const end = el.selectionEnd ?? 0;

    el.value = el.value.slice(0, start) + chunk + el.value.slice(end);
    const caret = start + chunk.length;

    try {
        el.selectionStart = el.selectionEnd = caret;

        el.focus();
    } catch {
        //
    }

    el.dispatchEvent(new Event('input', { bubbles: true }));
}

/** @param {HTMLInputElement|HTMLTextAreaElement|null} el */
function rteWrapSelection(el, open, close) {
    if (! el) return;

    const start = el.selectionStart ?? 0;
    const end = el.selectionEnd ?? 0;
    const v = el.value;
    const sel = v.slice(start, end);

    el.value = `${v.slice(0, start)}${open}${sel}${close}${v.slice(end)}`;

    const c0 = start + open.length;

    try {
        el.selectionStart = c0;

        el.selectionEnd = c0 + sel.length;

        el.focus();
    } catch {
        //
    }

    el.dispatchEvent(new Event('input', { bubbles: true }));
}

/** @param {HTMLInputElement|HTMLTextAreaElement|null} el */
function rteApplyRichCmd(el, cmd) {
    if (! el || ! cmd) return;

    switch (cmd) {
        case 'bold':
            rteWrapSelection(el, '**', '**');

            break;

        case 'italic':
            rteWrapSelection(el, '_', '_');

            break;

        case 'bullet':
            rteInsertAtCaret(el, '- ');

            break;

        case 'number':
            rteInsertAtCaret(el, '1. ');

            break;

        case 'clearMd':
        case 'eraseSame': {

            const s = el.selectionStart ?? 0;
            const e = el.selectionEnd ?? 0;
            const v = el.value;

            if (s === e) {
                el.value = rteStripMarkdownMarkers(v);
            } else {

                el.value = v.slice(0, s) + rteStripMarkdownMarkers(v.slice(s, e)) + v.slice(e);
            }

            el.dispatchEvent(new Event('input', { bubbles: true }));

            break;
        }

        case 'underline':
            rteWrapSelection(el, '++', '++');

            break;

        case 'hr':
            rteInsertAtCaret(el, '\n---\n');

            break;

        case 'blockquote':
            rteInsertAtCaret(el, '\n> ');

            break;

        case 'link': {

            const u = window.prompt('Link URL (must start with https:// or http://)', 'https://');

            if (! u || !/^https?:\/\//i.test(u)) return;

            const s = el.selectionStart ?? 0;
            const e = el.selectionEnd ?? 0;
            const v = el.value;

            const sel = v.slice(s, e) || 'Link text';
            const ins = `[${sel}](${u})`;

            el.value = v.slice(0, s) + ins + v.slice(e);

            const caret = s + ins.length;

            try {
                el.selectionStart = el.selectionEnd = caret;

                el.focus();
            } catch {
                //
            }

            el.dispatchEvent(new Event('input', { bubbles: true }));

            break;
        }

        case 'indentMore': {

            const s = el.selectionStart ?? 0;
            const v = el.value;

            const ls = v.lastIndexOf('\n', Math.max(0, s - 1)) + 1;

            el.value = v.slice(0, ls) + '    ' + v.slice(ls);

            try {
                el.selectionStart = el.selectionEnd = s + 4;

                el.focus();
            } catch {
                //
            }

            el.dispatchEvent(new Event('input', { bubbles: true }));

            break;
        }

        case 'indentLess': {

            const s = el.selectionStart ?? 0;
            const v = el.value;

            const ls = v.lastIndexOf('\n', Math.max(0, s - 1)) + 1;
            const rest = v.slice(ls);
            let take = 0;

            if (rest.startsWith('    ')) {

                take = 4;
            } else if (rest.startsWith('\t')) {

                take = 1;
            }

            if (take > 0) {
                el.value = v.slice(0, ls) + v.slice(ls + take);

                try {
                    el.selectionStart = el.selectionEnd = Math.max(ls, s - take);

                    el.focus();
                } catch {
                    //
                }

                el.dispatchEvent(new Event('input', { bubbles: true }));
            }

            break;
        }

        default:

            break;
    }
}

function buttonSelectOpts(opts, sel) {
    return opts.map(([k, lab]) => `<option value="${k}" ${sel === k ? 'selected' : ''}>${lab}</option>`).join('');
}

const BLOCK_PRESETS = {
    heading: () => ({
        id: uuid(),
        type: 'heading',
        level: 2,
        text: '',
        font: 'default',
        color: 'default',
        background: 'none',
        font_size: 'default',
        text_align: 'left',
    }),
    paragraph: () => ({
        id: uuid(),
        type: 'paragraph',
        text: '',
        font: 'default',
        color: 'default',
        background: 'none',
        font_size: 'default',
        text_align: 'left',
    }),
    spacer: () => ({ id: uuid(), type: 'spacer', size: 'md' }),
    divider: () => ({ id: uuid(), type: 'divider' }),
    callout: () => ({
        id: uuid(),
        type: 'callout',
        variant: 'tip',
        text: 'Short tip or important note.',
        font: 'default',
        color: 'default',
        background: 'none',
        font_size: 'default',
        text_align: 'left',
    }),
    button: () => ({
        id: uuid(),
        type: 'button',
        label: 'Take action',
        kind: 'rsvp',
        custom_url: '',
        btn_font: 'default',
        btn_bg: 'default',
        btn_text_color: 'default',
    }),
    image: () => ({ id: uuid(), type: 'image', path: null, alt: '' }),
    section: DEFAULT_SECTION,
};

const ADD_MENU = [
    { group: 'Structure', items: [{ k: 'section', label: 'Section (recommended)' }, { k: 'divider', label: 'Divider' }, { k: 'spacer', label: 'Spacer' }] },
    { group: 'Text', items: [{ k: 'heading', label: 'Heading' }, { k: 'paragraph', label: 'Paragraph' }, { k: 'callout', label: 'Callout' }] },
    { group: 'Media & actions', items: [{ k: 'image', label: 'Image' }, { k: 'button', label: 'Button' }] },
];

function parseInitial(mountEl) {
    try {
        return JSON.parse(mountEl.dataset.initial || '{"v":1,"blocks":[]}');
    } catch {
        return { v: 1, blocks: [] };
    }
}

function shallowClone(doc) {
    return JSON.parse(JSON.stringify(doc));
}

function countBlocks(nodes) {
    let n = 0;
    (nodes || []).forEach((b) => {
        if (! b || typeof b !== 'object') return;
        n += 1;
        if (b.type === 'section' && Array.isArray(b.blocks)) {
            n += countBlocks(b.blocks);
        }
    });

    return n;
}

function countButtons(nodes) {
    let n = 0;
    (nodes || []).forEach((b) => {
        if (! b) return;
        if (b.type === 'button') n += 1;
        if (b.type === 'section' && Array.isArray(b.blocks)) n += countButtons(b.blocks);
    });

    return n;
}

function hasSubstance(nodes) {
    for (const b of nodes || []) {
        if (! b) continue;
        if (b.type === 'paragraph' && String(b.text || '').trim()) return true;
        if (b.type === 'callout' && String(b.text || '').trim()) return true;
        if (b.type === 'image' && (b.path || stagedImages.has(b.id))) return true;
        if (b.type === 'section' && Array.isArray(b.blocks) && hasSubstance(b.blocks)) return true;
    }

    return false;
}

function buildHints(doc) {
    const blocks = doc.blocks || [];
    const hints = [];
    const total = countBlocks(blocks);
    const subs = hasSubstance(blocks);
    const buttons = countButtons(blocks);
    const docStr = JSON.stringify(blocks);
    const h1n = (docStr.match(/"level":1/g) || []).length;

    if (total < 2) hints.push({ ok: false, text: 'Add at least two blocks so the message is not a single line.' });
    else hints.push({ ok: true, text: 'You have multiple blocks — good scannability.' });

    if (! subs) hints.push({ ok: false, text: 'Add a paragraph, callout, or image with real content (not only titles). Required to save.' });
    else hints.push({ ok: true, text: 'Body content is present.' });

    if (blocks.length && blocks[0].type !== 'heading' && blocks[0].type !== 'section') {
        hints.push({ ok: false, text: 'Start with a heading or section so readers know what the message is about.' });
    } else {
        hints.push({ ok: true, text: 'Opens with a clear structure.' });
    }

    if (buttons === 0) hints.push({ ok: false, text: 'Optional: add a button for RSVP or your main action.' });
    else if (buttons > 3) hints.push({ ok: false, text: 'Many buttons — consider one primary action.' });
    else hints.push({ ok: true, text: 'Action buttons look balanced.' });

    if (h1n > 1) hints.push({ ok: false, text: 'Use at most one very large (level 1) heading.' });
    else hints.push({ ok: true, text: 'Heading levels look reasonable.' });

    return hints;
}

function storageUrl(path) {
    if (! path) return '';
    const base = window.__ISATA__?.storagePublicBase || '/storage';

    return `${String(base).replace(/\/$/, '')}/${String(path).replace(/^\//, '')}`;
}

function persist(state, hiddenInput, contentMirror) {
    hiddenInput.value = JSON.stringify(state);
    if (contentMirror) {
        const pieces = [];
        (state.blocks || []).forEach((b) => {
            if (b.type === 'heading') pieces.push(b.text);
            if (b.type === 'paragraph') pieces.push(b.text);
        });
        contentMirror.value = pieces.join(' ').slice(0, 500);
    }
    window.dispatchEvent(new CustomEvent('structured-doc-changed', { detail: { hints: buildHints(state) } }));
}

function moveInArray(arr, from, to) {
    if (to < 0 || to >= arr.length) return;
    const [it] = arr.splice(from, 1);
    arr.splice(to, 0, it);
}

function deleteBlockById(blocks, id) {
    for (let i = 0; i < blocks.length; i++) {
        const b = blocks[i];
        if (b.id === id) {
            blocks.splice(i, 1);
            stagedImages.delete(id);

            return true;
        }
        if (b.type === 'section' && Array.isArray(b.blocks) && deleteBlockById(b.blocks, id)) {
            return true;
        }
    }

    return false;
}

function syncStagedFilesToDom(stagingEl) {
    if (! stagingEl) return;
    stagingEl.innerHTML = '';
    stagedImages.forEach((file, id) => {
        const inp = document.createElement('input');
        inp.type = 'file';
        inp.name = `structure_images[${id}]`;
        inp.className = 'sr-only';
        inp.setAttribute('aria-hidden', 'true');
        const dt = new DataTransfer();
        dt.items.add(file);
        inp.files = dt.files;
        stagingEl.appendChild(inp);
    });
}

function mount() {
    const mount = document.getElementById('message-builder-mount');
    const hidden = document.getElementById('content_document');
    const staging = document.getElementById('structured-upload-staging');
    const contentMirror = document.getElementById('content');
    const form = document.getElementById('message-form');

    if (! mount || ! hidden) return;

    mount.addEventListener('focusin', (e) => {
        const t = e.target;
        if (t && (t.matches('textarea') || (t.matches('input[type="text"]') && ! t.classList.contains('sr-only')))) {
            window.__lastStructuredField = t;
        }
    });

    const state = shallowClone(parseInitial(mount));
    if (! state.blocks || state.blocks.length === 0) {
        state.v = 1;
        state.blocks = [DEFAULT_SECTION()];
    }

    function escapeAttr(s) {
        return escapeAttrText(s);
    }

    function escapeHtml(s) {
        return escapeHtmlText(s);
    }

    function bindRichTextBlockChrome(rowEl, block) {

        const root = rowEl.querySelector('[data-rte-root]');

        if (! root) return;

        const shell = root.querySelector('[data-rte-shell]');

        const ta = /** @type {HTMLInputElement|HTMLTextAreaElement|null} */ (root.querySelector('[data-field="text"]'));

        function bumpPersist() {
            persist(state, hidden, contentMirror);
        }

        function toggleFullscreen() {

            if (! shell) return;

            const on = shell.classList.toggle('rte-cms-fullscreen');

            if (on) {

                shell.classList.add(
                    'fixed','z-[100]','inset-4','flex','flex-col','rounded-lg','shadow-2xl','ring-1','ring-neutral-300',
                );

                if (ta && ta.tagName === 'TEXTAREA') {

                    ta.classList.add('min-h-[50vh]', 'flex-1');

                }

                document.body.classList.add('overflow-hidden');
            } else {

                shell.classList.remove(
                    'fixed','z-[100]','inset-4','flex','flex-col','rounded-lg','shadow-2xl','ring-1','ring-neutral-300',
                );

                if (ta && ta.tagName === 'TEXTAREA') {

                    ta.classList.remove('min-h-[50vh]', 'flex-1');

                }

                document.body.classList.remove('overflow-hidden');
            }

        }

        root.querySelectorAll('[data-rich-cmd]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const cmd = btn.getAttribute('data-rich-cmd') || '';

                if (cmd === 'fullscreen') {
                    toggleFullscreen();

                    bumpPersist();

                    return;
                }

                if (cmd === 'codeView') {

                    if (ta) {
                        ta.classList.toggle('font-mono');
                        ta.classList.toggle('text-[13px]');
                    }

                    bumpPersist();

                    return;
                }

                rteApplyRichCmd(ta, cmd);

                bumpPersist();
            });
        });

        const quick = root.querySelector('[data-rte-quick-color]');

        if (quick) {
            quick.addEventListener('change', () => {
                const sel = /** @type {HTMLSelectElement} */ (quick);
                const v = sel.value;

                if (v === '__hex__') return;

                block.color = v;
                rteSyncToneClusterDom(root, 'color', block.color, 'default');

                bumpPersist();
            });

        }

        const quickBg = root.querySelector('[data-rte-quick-bg]');

        if (quickBg) {
            quickBg.addEventListener('change', () => {
                const sel = /** @type {HTMLSelectElement} */ (quickBg);
                const v = sel.value;

                if (v === '__hex_bg__') return;

                block.background = v;
                rteSyncToneClusterDom(root, 'background', block.background, 'none');

                bumpPersist();
            });

        }

        root.querySelectorAll('[data-merge-insert]').forEach((mergeSel) => {

            if (! ta) return;

            mergeSel.addEventListener('change', () => {
                const m = /** @type {HTMLSelectElement} */ (mergeSel);

                const v = m.value;

                if (! v) return;

                rteInsertAtCaret(ta, v);

                m.selectedIndex = 0;

                bumpPersist();
            });

        });

        root.querySelector('[data-rte-clear-appearance]')?.addEventListener('click', () => {
            block.font = 'default';
            block.font_size = 'default';
            block.text_align = 'left';
            block.color = 'default';
            block.background = 'none';
            render();
        });

    }

    function bindFields(rowEl, block) {
        rowEl.querySelectorAll('[data-field]').forEach((inp) => {
            const key = inp.dataset.field;
            const apply = () => {
                let v = inp.value;
                if (key === 'level') block.level = Number(v);
                else block[key] = v;
                if (key === 'kind') {
                    const cw = rowEl.querySelector('[data-custom-wrap]');

                    if (cw) cw.classList.toggle('hidden', v !== 'custom');
                }
                persist(state, hidden, contentMirror);
            };

            inp.addEventListener('change', apply);
            inp.addEventListener('input', apply);
        });
    }

    /** @param {HTMLElement|undefined|null} rteRoot */
    function bindToneCluster(wrapEl, block, toneField, rteRoot) {

        if (! wrapEl || ! block || ! toneField) return;
        const sel = wrapEl.querySelector('.tone-preset');
        const hex = wrapEl.querySelector('[data-tone-hex]');

        if (! sel || ! hex) return;

        const fb = wrapEl.getAttribute('data-tone-empty-fallback') || 'default';

        function bump() {
            persist(state, hidden, contentMirror);

            if (rteRoot && toneField === 'color') rteSyncQuickColorFromBlock(rteRoot, block.color);

            if (rteRoot && toneField === 'background') rteSyncQuickBgFromBlock(rteRoot, block.background);
        }

        sel.addEventListener('change', () => {

            if (sel.value !== 'custom') {
                hex.value = '';
            }

            if (sel.value === 'custom') {

                hex.classList.remove('hidden');

                const hv = hex.value.trim().toLowerCase();

                block[toneField] = /^#[0-9a-f]{6}$/.test(hv) ? hv : fb;
            } else {

                hex.classList.add('hidden');

                block[toneField] = sel.value;

            }

            bump();
        });

        hex.addEventListener('input', () => {

            const hv = hex.value.trim().toLowerCase();

            if (sel.value !== 'custom') return;

            block[toneField] = /^#[0-9a-f]{6}$/.test(hv) ? hv : fb;

            bump();
        });

    }

    function mkBtn(text, cls, fn) {
        const el = document.createElement('button');
        el.type = 'button';
        el.textContent = text;
        el.className =
            cls || 'px-2 py-1 text-xs rounded border border-slate-200 text-slate-700 hover:bg-slate-100';
        el.addEventListener('click', fn);

        return el;
    }

    function mkAddToolbar(onPick, detailsClass) {
        const details = document.createElement('details');
        details.className = detailsClass || 'rounded-lg border border-dashed border-slate-300 bg-white p-3 text-sm';

        details.innerHTML =
            `<summary class="cursor-pointer font-medium text-slate-700">＋ Insert block…</summary>
      <div class="mt-3 space-y-2">` +
            ADD_MENU.map(
                (g) => `
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">${g.group}</p>
          <div class="mt-2 flex flex-wrap gap-2">` +
                g.items
                    .map(
                        (item) =>
                            `<button type="button" data-add="${item.k}" class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-700 hover:bg-slate-50">${item.label}</button>`,
                    )
                    .join('')
                +
                `
          </div>
        </div>`,
            ).join('') +
            `</div>`;

        details.querySelectorAll('button[data-add]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const k = e.currentTarget.dataset.add;
                const total = countBlocks(state.blocks);
                if (total >= 28) {
                    window.alert('Near the layout limit — remove blocks before adding more.');
                    details.open = false;

                    return;
                }
                if (total > 26 && ! window.confirm('Long messages are harder on mobile. Continue adding?')) {
                    details.open = false;

                    return;

                }

                if (!BLOCK_PRESETS[k]) return;
                onPick(k);
                details.open = false;
                render();
            });
        });

        return details;
    }

    function renderBlockEditor(b, depth, list, index) {
        const row = document.createElement('div');
        row.dataset.bid = b.id;
        row.className =
            depth === 0
                ? 'rounded-xl border border-slate-200 bg-white shadow-sm p-4 space-y-3'
                : 'rounded-lg border border-slate-200/80 bg-slate-50/80 p-3 space-y-2';

        const bar = document.createElement('div');
        bar.className = 'flex flex-wrap items-start justify-between gap-2';

        let typePieces = `<span class="rounded bg-slate-100 px-2 py-0.5">${b.type}</span>`;
        let innerHtml = '';

        switch (b.type) {
            case 'section':
                typePieces += '<span class="text-indigo-600 font-semibold">Layout</span>';
                b.title_font = b.title_font || 'default';
                b.title_color = b.title_color || 'default';
                b.title_background = b.title_background || 'none';
                innerHtml = `<label class="block text-xs font-medium text-slate-600">Section label</label>
        <input type="text" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="${escapeAttr(b.title)}" data-field="title">
        <label class="block text-xs font-medium text-slate-600 mt-2">Style</label>
        <select data-field="variant" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
          ${['default', 'muted', 'accent', 'highlight']
                      .map(
                          (v) =>
                              `<option value="${v}" ${b.variant === v ? 'selected' : ''}>${v}</option>`,
                      )
                      .join('')}
        </select>`;

                break;
            case 'heading':
                typePieces += '';
                b.font = b.font || 'default';
                b.color = b.color || 'default';
                b.background = b.background || 'none';
                b.font_size = b.font_size || 'default';
                b.text_align = b.text_align || 'left';
                innerHtml = cmsStructuredContentHtml(b, {
                    kind: 'heading',
                    multiline: false,
                    metaBeforeContent: '',
                });

                break;
            case 'paragraph':
                b.font = b.font || 'default';
                b.color = b.color || 'default';
                b.background = b.background || 'none';
                b.font_size = b.font_size || 'default';
                b.text_align = b.text_align || 'left';
                innerHtml = cmsStructuredContentHtml(b, {
                    kind: 'paragraph',
                    multiline: true,
                    metaBeforeContent: '',
                });

                break;
            case 'callout':
                b.font = b.font || 'default';
                b.color = b.color || 'default';
                b.background = b.background || 'none';
                b.font_size = b.font_size || 'default';
                b.text_align = b.text_align || 'left';
                innerHtml = cmsStructuredContentHtml(b, {
                    kind: 'callout',
                    multiline: true,
                    metaBeforeContent:
                        `<div class="flex flex-wrap items-center gap-2 pb-1 text-sm text-neutral-800">` +
                        `<span class="text-xs font-semibold text-neutral-600">Panel tone</span>` +
                        `<select data-field="variant" class="rounded border border-neutral-300 px-2 py-1 text-sm">${['tip', 'info', 'warning']
                            .map((v) => `<option value="${v}" ${b.variant === v ? 'selected' : ''}>${v}</option>`)
                            .join('')}</select>` +
                        `</div>`,
                });

                break;
            case 'divider':
                innerHtml = '<p class="text-xs text-slate-500">Visual break between ideas</p>';

                break;
            case 'spacer':
                innerHtml = `<select data-field="size" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
          ${['sm', 'md', 'lg'].map((s) => `<option value="${s}" ${b.size === s ? 'selected' : ''}>${s}</option>`).join('')}
        </select>`;

                break;
            case 'image':
                /** @type {File|undefined} */
                const pending = stagedImages.get(b.id);
                const preview = b.path
                    ? `<img src="${escapeAttr(storageUrl(b.path))}" alt="" class="max-h-28 rounded-lg border border-slate-200">`
                    : '';
                const pendingLabel = pending ? `<p class="text-xs text-emerald-800 font-medium">Selected: ${escapeHtml(pending.name)}</p>` : '';
                const need = ! b.path && ! pending ? '<p class="text-xs text-amber-800 font-medium">Choose an image file to include.</p>' : '';

                innerHtml = `${need}${preview}${pendingLabel}
        <label class="block text-xs font-medium text-slate-600 mt-2">Description for screen readers</label>
        <input type="text" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="${escapeAttr(b.alt)}" data-field="alt">
        <label class="mt-2 inline-flex items-center px-4 py-2 rounded-lg bg-indigo-50 text-indigo-800 text-sm font-medium cursor-pointer">
          ${b.path || pending ? 'Replace image' : 'Choose image'}
          <input type="file" class="hidden" data-image-pick="${b.id}" accept=".jpeg,.jpg,.png,.gif,.webp">
        </label>`;

                break;
            case 'button':
                b.btn_font = b.btn_font || 'default';
                b.btn_bg = b.btn_bg || 'default';
                b.btn_text_color = b.btn_text_color || 'default';
                innerHtml =
                    `<input type="text" data-field="label" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="${escapeAttr(b.label)}" placeholder="Button label">
        <select data-field="kind" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
          ${[
              ['rsvp', 'RSVP (signed link)'],
              ['feedback', 'Feedback survey'],
              ['meeting', 'Meeting link'],
              ['custom', 'Custom URL'],
          ]
              .map(([v, lbl]) => `<option value="${v}" ${b.kind === v ? 'selected' : ''}>${lbl}</option>`)
              .join('')}
        </select>
        <div data-custom-wrap class="${b.kind === 'custom' ? '' : 'hidden'} mt-2">
          <input type="text" data-field="custom_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" value="${escapeAttr(b.custom_url)}" placeholder="https://… or use tags">
        </div>
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 shadow-sm space-y-2">
          <p class="text-sm font-semibold text-emerald-950">Button look</p>
          <div class="grid sm:grid-cols-3 gap-3">
          <div>${fontSelectHtml(b.btn_font, 'btn_font')}</div><div>
            <label class="block text-xs font-medium text-slate-600">Fill color</label>
            <select data-field="btn_bg" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-2 text-sm">${buttonSelectOpts(BUTTON_FILL_OPTS, b.btn_bg)}</select>
          </div><div>
            <label class="block text-xs font-medium text-slate-600">Label color</label>
            <select data-field="btn_text_color" class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-2 text-sm">${buttonSelectOpts(
                TEXT_COLOR_OPTS.filter(([k]) => k !== 'custom'),
                b.btn_text_color,
            )}</select>
          </div></div></div>`;

                break;
        }

        bar.innerHTML = `<div class="text-xs font-semibold uppercase tracking-wide text-slate-500 flex flex-wrap items-center gap-2">${typePieces}</div>`;
        const ctr = document.createElement('div');
        ctr.className = 'flex flex-wrap gap-1';
        ctr.appendChild(mkBtn('↑', '', () => { if (index > 0) { moveInArray(list, index, index - 1); render(); } }));
        ctr.appendChild(
            mkBtn('↓', '', () => {
                if (index < list.length - 1) {
                    moveInArray(list, index, index + 1);
                    render();
                }
            }),
        );
        ctr.appendChild(
            mkBtn('Remove', 'text-red-700 border-red-100 hover:bg-red-50', () => {
                deleteBlockById(state.blocks, b.id);
                render();
            }),
        );
        bar.appendChild(ctr);

        row.appendChild(bar);

        const bodyWrap = document.createElement('div');
        bodyWrap.className = 'space-y-2';
        bodyWrap.innerHTML = innerHtml;
        row.appendChild(bodyWrap);

        bindFields(row, b);

        const rteEl = row.querySelector('[data-rte-root]');

        row.querySelectorAll('[data-tone-cluster]').forEach((cluster) => {
            const fld = cluster.getAttribute('data-tone-cluster');

            const syncRoot = (fld === 'color' || fld === 'background') ? rteEl : undefined;

            if (fld) bindToneCluster(cluster, b, fld, syncRoot || undefined);

        });

        bindRichTextBlockChrome(row, b);

        const ip = row.querySelector(`[data-image-pick="${b.id}"]`);
        if (ip) {
            ip.addEventListener('change', () => {
                const f = ip.files && ip.files[0];
                if (f) stagedImages.set(b.id, f);
                else stagedImages.delete(b.id);
                persist(state, hidden, contentMirror);
    
                render();
            });
        }

        if (b.type === 'section' && Array.isArray(b.blocks)) {
            const nest = document.createElement('div');
            nest.className = 'space-y-2 pl-2 border-l-2 border-indigo-100 ml-1';
            b.blocks.forEach((child, cidx) => {
                nest.appendChild(renderBlockEditor(child, depth + 1, b.blocks, cidx));
            });
            row.appendChild(nest);
            row.appendChild(
                mkAddToolbar((kind) => {
                    b.blocks.push(BLOCK_PRESETS[kind]());
                }, 'rounded-lg border border-dashed border-indigo-200 bg-indigo-50/50 p-3 text-sm'),
            );
        }

        return row;
    }

    function render() {
        mount.innerHTML = '';

        mount.appendChild(
            mkAddToolbar((kind) => {
                state.blocks.push(BLOCK_PRESETS[kind]());
            }),
        );

        const listEl = document.createElement('div');
        listEl.className = 'mt-4 space-y-4';
        state.blocks.forEach((block, idx) => {
            listEl.appendChild(renderBlockEditor(block, 0, state.blocks, idx));
        });
        mount.appendChild(listEl);

        persist(state, hidden, contentMirror);
        syncStagedFilesToDom(staging);
    }

    if (form) {
        form.addEventListener('submit', () => {
            persist(state, hidden, contentMirror);
            syncStagedFilesToDom(staging);
        });
    }

    render();
}

document.addEventListener('DOMContentLoaded', () => {
    mount();
    window.ensurePlainTextClassicEditor = ensurePlainTextClassicEditor;

    window.ensurePlainTextClassicEditor();


});
