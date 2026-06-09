<?php if (!defined('FW')) die('Forbidden');

$manifest = array();

$manifest['name']        = __( 'Builder', 'fw' );
$manifest['description'] = __( 'Unyson Page Builder Extension', 'fw' );

$manifest['version']     = '1.2.51';

// Repository Info
$manifest['github_update'] = 'UnysonPlus/UnysonPlus-Builder-Extension';
$manifest['github_repo']   = 'https://github.com/UnysonPlus/UnysonPlus-Builder-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '5.8';

// Documentation
$manifest['uri'] = 'http://manual.unyson.io/en/latest/extension/builder/index.html';

/**
 * Changelog
 * -----------------------------------------------------------------------------
 * 1.2.50 - Smart click-to-add placement. Clicking an element icon used to drop
 *          every type into the root collection (base allowDestinationType(null)
 *          is true for all types), stranding bare columns and content shortcodes
 *          at root where they could not then be dragged into a section. Clicking
 *          now resolves a valid destination and auto-builds the missing container
 *          scaffold: sections/section-like still append to root; a column drops
 *          into the last section (a standard section is created when none exists);
 *          a content/media element drops into the last column on the page (a
 *          section -> 1/1 column scaffold is created when the page has no column
 *          yet). The new element is scrolled into view after insertion. Logic
 *          lives in the thumbnail-click handler in initialize-builder.js and
 *          reuses the existing forEachItemRecursive walk and the section-like
 *          registry (window.fwSectionLikeTypes). Drag-and-drop is unchanged.
 *
 * 1.2.44 - Full-template export envelope bumped to format_version 2. Per-element
 *          Custom CSS (shortcodes 1.4.87) now lives inside the builder `json`, so an
 *          exported full-page template carries its styling with it — the version
 *          marks that change for downstream readers / future migrations. Import is
 *          unaffected (it never gated on format_version); the CSS rides in `json`
 *          and is rendered by the per-page pipeline in framework 2.8.46.
 *
 * 1.2.43 - Page-Builder Templates: Full-component import / export. The Full
 *          Templates dropdown now exposes a per-row Export icon (downloads
 *          a `.json` file with an `_fw_template_export` envelope wrapping
 *          the saved `title` / `json` / `created`) and a top-of-list
 *          "Import…" button (validates the envelope, then writes the
 *          payload back to `wp_options` under the same deterministic
 *          `fw:bt:f:{builder_type}:{md5(json)}` key — idempotent on
 *          repeat). Two new AJAX actions wired up via this component's
 *          `_init()`: `wp_ajax_fw_builder_templates_full_export` and
 *          `wp_ajax_fw_builder_templates_full_import`, both nonce-gated
 *          with the existing `fw_builder_templates_full` nonce. The
 *          shared `templates/static/styles.css` gained `.template-export`
 *          (mirrors `.template-delete`'s float-right + show-on-hover
 *          behavior) so Section / Column components inherit the same
 *          visual treatment. Paired with framework 2.8.38 + shortcodes
 *          1.4.83.
 *
 * 1.2.32 - TEMPORARY DIAGNOSTIC #2 for the residual page-builder drag-helper
 *          drift (see framework 2.8.26). Diagnostic #1 (2.8.25 / 1.4.78 / 1.6.9)
 *          disabled the three new files I added this session
 *          (section-like-factory.js, section-sorter.js, section-sorter.css)
 *          and the user reported drift PERSISTS — so those are NOT the cause.
 *          Restored those enqueues; now disabling the two CSS rule additions
 *          that diffing OLD 2.7.40 vs CURRENT turns up in the drag-relevant
 *          paths. In `builder/static/css/builder.css` (this extension): the
 *          new rule `.fw-option-type-builder .builder-items .builder-item
 *          .builder-item span { width: 100%; }` is commented out. In
 *          page-builder/static/css/styles.css (shortcodes 1.4.79 /
 *          page-builder 1.6.10): the new `border-radius: .25rem; margin:
 *          .25rem;` declarations on `.fw-option-type-builder
 *          .fw-option-type-page-builder .builder-item-type` are commented
 *          out. The margin is the prime suspect: `.builder-item-type` is the
 *          element jQuery UI clones as the drag helper, and a non-zero
 *          margin on the source shifts the cursor-to-helper offset every
 *          time the helper crosses into a connected sortable. If drift
 *          disappears, we'll restore the cosmetics in a scoped form
 *          (e.g. `.thumbnails .builder-item-type` only, so the cloned helper
 *          doesn't pick up margin).
 *
 * 1.2.31 - Drag helper drift fixed by REVERTING the override layer accumulated
 *          across 1.2.26 / 1.2.27 / 1.2.28 / 1.2.30. The user pulled down an
 *          OLD copy of unysonplus (2.7.40) and confirmed the drift does NOT
 *          exist there with jQuery UI's bare-bones default draggable /
 *          sortable wiring; copying OLD's builder.js into CURRENT did not
 *          fix it, narrowing the regression to the additions in
 *          initialize-builder.js and the additions to the sortable in
 *          builder.js. Removed from both files:
 *          • `appendTo: '.fw-option-type-builder'` on the draggable AND
 *            the sortable
 *          • `_fwHelperAnchor` cursor-vs-helper anchor recording in both
 *            `start` handlers
 *          • the per-tick `drag` callback (draggable) and `sort` callback
 *            (sortable) that re-set `helper.style.left/top` to anchor +
 *            cursor + offsetParent.offset()
 *          • the `_fwHelperAnchor` cleanup in both `stop` handlers
 *          The drift the user was reporting wasn't in jQuery UI — it was
 *          in our anchor (1.2.29's diagnostic overlay caught the anchor
 *          drifting to nonsense values like `(-804, -269)` over a single
 *          drag because nested-sortable hand-offs fire `start` events many
 *          times per drag, and our re-record-on-every-start was capturing
 *          mid-init helper positions). 1.2.30 tried to guard the anchor;
 *          the actual fix is to not have an anchor in the first place.
 *
 *          KEPT (independent, correct on their own merits):
 *          • the simple→column hierarchy guard in `_rearrange` (1.2.21+;
 *            blocks `simple` items from committing into non-column
 *            containers — prevents column-shuffle during simple drags into
 *            section areas)
 *          • the defensive `.builder-item` transform/transition sweep at
 *            sortable `start` (clears stale FLIP styles)
 *          • the `this.refreshPositions(true)` call in the guard's
 *            early-return (keeps cached item rects in sync)
 *          • the `window.fwDragDebug` overlay (gated behind
 *            `window._fwDragDebug = true`, off by default; useful if drift
 *            ever returns)
 *
 * 1.2.30 - Root-cause fix for the residual drag helper drift that survived
 *          1.2.26 / 1.2.27 / 1.2.28. The 1.2.29 diagnostic overlay made
 *          the bug obvious on the first test drag: the SORTABLE's `start`
 *          event fired 15 times during a 7-pass reproducer (once per
 *          nested-sortable hand-off — each column's `.builder-items` is
 *          its own sortable instance and connectToSortable fires
 *          _mouseStart whenever the cursor first enters one). Our `start`
 *          handler was re-recording `_fwHelperAnchor` on every fire, and
 *          at that moment jQuery UI has only partially initialized the
 *          sortable's offsets so `ui.helper.offset()` is slightly off —
 *          each fresh recording captured the error as the new "correct"
 *          anchor. After 15 re-records the anchor was at `(-804, -269)`,
 *          and the per-tick re-pin was faithfully tracking the cursor
 *          relative to that bad anchor, so live delta read ≈ 0 every
 *          tick while the helper was wildly off-cursor.
 *
 *          Two-line fix:
 *          • Guard the sortable's anchor-record with
 *            `if ( ! ui.helper.data( '_fwHelperAnchor' ) )` so only the
 *            FIRST start records — every subsequent nested-sortable hand-
 *            off short-circuits.
 *          • Clear the anchor on both `stop` handlers (sortable.stop and
 *            draggable.stop) so the next drag records fresh. Needed
 *            mostly for existing-item drags where `helper: 'original'`
 *            keeps the helper element around across drags; thumbnail
 *            drags' cloned helpers get destroyed anyway but the cleanup
 *            is harmless.
 *
 *          The thumbnail draggable's `start` (in initialize-builder.js)
 *          already runs once per drag, so it doesn't need the same guard
 *          — it gets the first crack at recording the anchor for any
 *          thumbnail drag, and the sortable's guard prevents the
 *          subsequent nested-sortable starts from overwriting it.
 *
 * 1.2.29 - Diagnostic-only: a temporary on-screen overlay for the page-builder
 *          drag helper. OFF by default — turn on with
 *          `window._fwDragDebug = true` in the browser console; remove
 *          with `false`. While a drag is active the overlay (top-right of
 *          the viewport) shows real-time counters of every drag callback
 *          firing (draggable.start/drag/stop, sortable.start/sort/stop)
 *          plus the cursor / helper.offset() / drag-start anchor / delta.
 *          The delta = cursor - anchor - helperOffset; it should be (0, 0)
 *          on every tick — any non-zero value is the drift in pixels, with
 *          the overlay turning amber the moment it appears so we can see
 *          exactly which tick / callback leaks the drift. Intended for a
 *          single round of triage; remove again afterward.
 *          `window.fwDragDebug.reset()` zeroes the counters between drags.
 *
 * 1.2.28 - Eliminate residual drag helper drift via manual position override.
 *          The 1.2.26 / 1.2.27 rounds stabilized the helper's offsetParent
 *          (first `body`, then `.fw-option-type-builder`) which reduced
 *          drift but couldn't reach the part coming from jQuery UI
 *          sortable's internal offset bookkeeping. The page builder has
 *          nested sortables (each column's `.builder-items` is its own
 *          sortable), and connectToSortable / nested-sortable hand-offs
 *          transfer `offset.click` / `offset.parent` / `offset.relative`
 *          between instances; tiny per-transition recompute errors
 *          compounded visibly across column crossings (consistently in one
 *          direction — helper moving higher above the cursor each pass).
 *
 *          New approach: pin the helper to the cursor every mousemove tick
 *          using an anchor recorded at drag start.
 *          • The thumbnail draggable's `start` records
 *            `event.pageX - helper.offset().left` and same for top into
 *            `ui.helper.data('_fwHelperAnchor')`. A new `drag` callback
 *            re-sets `helper.style.left` / `top` each tick using
 *            `event.pageX - anchor.left - helper.offsetParent().offset().left`
 *            (and same for top). `drag` runs after jQuery UI's _mouseDrag
 *            positioning, so this overwrite wins before paint.
 *          • Same pattern in the items sortable: `start` records the
 *            anchor (right after the placeholder is sized), and a new
 *            `sort` callback re-pins the helper each tick.
 *          The cursor is now the only source of truth for helper position
 *          and accumulated offset drift can't pull the helper away from
 *          it. Border / box styling stays intact because
 *          `.fw-option-type-builder` is still the appendTo target.
 *
 * 1.2.27 - Follow-up to 1.2.26: keep the drag helper inside the scoped CSS
 *          context. The previous round set `appendTo: 'body'` on both the
 *          thumbnail draggable and the items sortable to stabilize the
 *          helper's offsetParent, but `body` is outside the
 *          `.fw-option-type-builder .builder-item-type { … }` selectors so
 *          the floating thumbnail rendered as a bare star + label (no
 *          border / box). Switch `appendTo` to `.fw-option-type-builder` in
 *          both places. The outer builder wrapper is in the scoped CSS
 *          context (styling preserved) AND doesn't flip position scheme
 *          during drag — only the inner `.builder-items-types` does — so
 *          the offsetParent-stability benefit is preserved. The two
 *          companion fixes from 1.2.26 (defensive transform/transition
 *          sweep at sortable `start`; `this.refreshPositions(true)` in the
 *          simple-only guard's early-return) are unchanged.
 *
 * 1.2.26 - Drag helper drift fix. The cloned thumbnail draggable (in
 *          `initialize-builder.js`) and the items sortable (in `builder.js`)
 *          now both set `appendTo: 'body'` so the dragged element lives in
 *          the document for the duration of the drag instead of its
 *          original parent. The thumbnail's parent
 *          (`.builder-items-types`) sits inside `.fw-options-tabs-wrapper`,
 *          whose positioning scheme the existing `data-fixed-header`
 *          logic flips between `static` and `fixed` on scroll; each flip
 *          (and other small layout nudges from placeholder commits)
 *          shifted the helper's offset relative to the cursor, and the
 *          shifts accumulated as the cursor crossed container boundaries
 *          — visible as the helper progressively pulling away from the
 *          cursor, and as a big jump when the cursor crossed back over
 *          the tabs wrapper. Pinning the helper to `<body>` keeps its
 *          offsetParent stable.
 *
 *          Two smaller companion fixes in `builder.js`:
 *          • The sortable `start` handler now wipes any residual inline
 *            `transform` / `transition` styles on every `.builder-item`,
 *            so an interrupted FLIP animation from a prior drag can't
 *            leak transforms into the new drag's `getBoundingClientRect`
 *            reads.
 *          • The simple-only hierarchy guard's early-return now calls
 *            `this.refreshPositions(true)` to keep jQuery UI's cached
 *            item rects consistent with the DOM the cursor is hovering
 *            over (the early-return previously skipped the
 *            `refreshPositions` that the normal doRearrange path calls).
 *
 * 1.2.25 - `_rearrange` hierarchy guard now also reads `data-builder-item-type`
 *          from the dragged element as a fallback when `fw-source-item-type`
 *          isn't present. v1.2.21/1.2.24's start handler only stashed the
 *          source type for EXISTING items being reordered — thumbnail
 *          draggables (new items from the metabox) returned early and never
 *          got the stash, which left the simple→column rule inactive during
 *          first-drop. As a result, dragging a Content/Media Elements thumb
 *          (e.g. Text Block) toward a column shuffled the columns inside
 *          every section the cursor passed over. The fallback uses the same
 *          attribute the receive handler reads to instantiate new items, so
 *          the rule now applies uniformly: placeholder only commits inside a
 *          column's .builder-items; while the cursor is over section / root
 *          area, the placeholder stays put. Layout-Elements thumbnails
 *          (Section, Column) are unaffected — they keep dragging freely.
 *
 * 1.2.24 - Fix (regression): the `_rearrange` hierarchy guard is now scoped to
 *          `simple` items only. v1.2.21/1.2.22 enforced a full type hierarchy
 *          including `column → row`, but rows are never present in the editor
 *          tree — they're synthesized by the page-builder items-corrector only
 *          at save/shortcode-generation time. In the editor a column sits
 *          directly inside a section, so the guard's expected `'row'` parent
 *          never matched and every column placeholder commit was blocked
 *          (columns draggable but not droppable, including across sections).
 *          The guard now only constrains `simple` items (must commit into a
 *          column) — which is the only behavior that was ever needed (stops a
 *          tall text-block from yanking sections during drag and makes loose
 *          root simples snap into a column). Columns / sections / section-like
 *          items are unconstrained again; their invalid drops stay rejected at
 *          `receive` by allowIncomingType / allowDestinationType. Removed the
 *          now-unused `STRICT_HIERARCHY` map and `isSectionLike` helper from
 *          the guard block. The `start`-handler `fw-source-item-type` stash is
 *          unchanged.
 *
 * 1.2.23 - `static/css/frontend-grid.css` — `.fw-row` and `.fw-row > *`
 *          now consume `--bs-gutter-x` / `--bs-gutter-y` (Bootstrap's
 *          native gutter custom-property names) instead of the previous
 *          private `--fw-gutter-x/y` namespace. The old `--fw-gutter-x/y`
 *          variables survive as a legacy fallback chain
 *          (`--bs-gutter-x: var(--fw-gutter-x, 1.5rem)`) so any custom
 *          CSS that already sets `--fw-gutter-x` on a parent keeps
 *          working. This is the missing piece that let Theme Settings →
 *          Default Gap actually take effect on `.fw-row` columns: the
 *          gap override in plugin's `framework/includes/css-tokens.php`
 *          writes `--bs-gutter-x` on `.row, .fw-row`, but `.fw-row > *`
 *          padding calc was reading `--fw-gutter-x` so the override was
 *          invisible. Same value name as Bootstrap's own `.row` rule
 *          means `.row` and `.fw-row` are now interchangeable for any
 *          gap-related utility class (`.g-N`, `.gx-N`, etc.). Pair with
 *          plugin 2.7.122 which strips the now-redundant `:root` boost
 *          and `!important` from the gap rules.
 *
 * 1.2.22 - `STRICT_HIERARCHY` enforcement in the `_rearrange` override
 *          generalized so any "section-like" type (not just literal
 *          `'section'`) is treated as a valid root-level container for rows.
 *          Reads `window.fwSectionLikeTypes.isSectionLike(type)` (registered by
 *          the page-builder extension's section-like framework, v1.6.5) when
 *          deciding whether a target container's parent type qualifies. Drop
 *          rules: section-like → root only; row → any section-like container;
 *          column → row; simple → column. Falls through silently if the
 *          registry hasn't loaded (defensive coding — the original literal
 *          check is restored as fallback).
 *
 * 1.2.21 - Drag-reorder hierarchy lock. The custom `_rearrange` override in
 *          `static/js/builder.js` now enforces that each item type can only
 *          commit its placeholder into the correct parent container —
 *          simple → column, column → row, row → section, section → root.
 *          Source item type is stashed onto `ui.item` in the existing `start`
 *          handler (`fw-source-item-type` jQuery data key); `_rearrange`
 *          resolves the target container's parent model via a DOM walk +
 *          `builder.findItemRecursive` and blocks the move before the 100ms
 *          cross-container settle timer starts. Stops two visible glitches:
 *          (a) tall text-blocks pushing the surrounding section down when the
 *          cursor briefly leaves the source column, and (b) root-level simples
 *          reordering against sibling sections while being dragged toward a
 *          column. Thumbnail draggables bypass the start handler so the guard
 *          reads `undefined` and falls through — receive validation remains the
 *          authority for new-item drops.
 *
 * 1.2.20 - Security: added CSRF nonce protection to all Builder AJAX endpoints:
 *          fw_builder_fullscreen_set/unset_storage_item (fullscreen toggle),
 *          fw_builder_templates_render (template picker), and
 *          fw_builder_templates_full_load / _save / _delete (full template
 *          CRUD). Nonces localized via wp_localize_script; JS callers updated
 *          to send `_nonce` field; PHP handlers verify via check_ajax_referer.
 */
