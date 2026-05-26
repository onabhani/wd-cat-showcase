# Woodmart Category Showcase

A dynamic Gutenberg block that renders a WooCommerce product category as a
structured section — label, title, view-all link, child-category tabs, and a
product grid — while delegating the product grid itself to Woodmart's native
`wd/products` block so product cards stay visually identical to the rest of
the site.

> **Core principle:** the plugin owns the section structure. It does **not**
> emit a single line of product card markup. Card design stays Woodmart's
> responsibility, forever.

---

## Status

`v0.1.0` — scaffolded, with compiled `build/` assets committed so the plugin
can be uploaded and activated without running a local Node build. Not yet
tested against a live Woodmart install.

## Requirements

| Dependency | Minimum |
|---|---|
| PHP | 8.2 |
| WordPress | 6.4 |
| WooCommerce | active (warns if missing) |
| Woodmart theme | recommended (graceful degrade if missing) |
| Node (dev only) | 20+ |

## Install (normal upload-and-activate)

1. Download a release zip (or create one with `npm run plugin-zip`).
2. In WordPress admin: **Plugins → Add New Plugin → Upload Plugin**.
3. Upload zip and activate **Woodmart Category Showcase**.

No Node/npm is required on the production server if you're installing from the
zip artifact because compiled `build/` assets are included.

## Install (development)

```bash
git clone https://github.com/onabhani/wd-cat-showcase.git
cd wd-cat-showcase
npm install
npm run build         # production build into ./build
npm run start         # dev watcher
```

Then symlink or copy the folder into `wp-content/plugins/` and activate
"Woodmart Category Showcase".

## Architecture

### Responsibility split

```
Plugin       → section wrapper, header, view-all, child-category nav, settings
Woodmart     → product grid + product cards (via wp:wd/products block)
WooCommerce  → product/term data
Theme        → typography, colors, card styling
```

### Render flow

```
Gutenberg block (dynamic, save=null)
   │
   └─► PHP render_callback: Renderer::render()
          │
          ├─ sanitize attrs (absint, casts)
          ├─ validate categoryId in product_cat
          ├─ render_header()       ── label, title, view-all (fallbacks: term name, term link)
          ├─ render_children()     ── get_terms(parent), <nav> with anchor links
          └─ render_products_grid()
                 │
                 ├─ build <!-- wp:wd/products {…} --> comment
                 │   with `categoriesIds` = (string) categoryId
                 └─ do_blocks( $comment )   ← Woodmart renders the cards
```

### File map

| Path | Role |
|---|---|
| `wd-cat-showcase.php` | Bootstrap: constants, requires, kickoff |
| `block.json` | Block metadata + attributes (single source of truth) |
| `includes/class-plugin.php` | Hooks `init`, `rest_api_init`, `admin_notices` |
| `includes/class-renderer.php` | Server-side render — the heart of the plugin |
| `includes/class-rest-controller.php` | `GET /wdcs/v1/categories` for the editor picker |
| `includes/class-dependencies.php` | WooCommerce / `wd/products` presence checks |
| `includes/helpers.php` | Term lookup + unique block-id helpers |
| `src/index.js` | `registerBlockType` entry |
| `src/edit.js` | Editor component (ServerSideRender preview + inspector) |
| `src/inspector-controls.js` | Sidebar panels: Category / Header / Products |
| `src/components/CategorySelect.js` | Async select fed by REST endpoint |
| `src/style.scss` | Frontend styles — **section only**, no product card rules |
| `src/editor.scss` | Editor-only placeholder styles |

### Naming conventions

- Block name: `hdqah/wd-cat-showcase`
- PHP namespace: `Hdqah\WDCS`
- Text domain: `wd-cat-showcase`
- CSS root class: `.wdcs` (all rules scoped under it — see `style.scss`)
- REST namespace: `wdcs/v1`
- Filter prefix: `wdcs_`

### Unique block ID handling

Woodmart uses `blockId` for scoped CSS/behavior. To prevent collisions when
multiple instances live on one page:

1. Editor: on first insert, `crypto.randomUUID()` generates `wdcs-<uuid>` and
   persists it to the block's `blockId` attribute.
2. Server: if `blockId` is empty (older saves), `unique_block_id()` falls back
   to a UUID at render time.
3. The Woodmart inner block receives `wd-<blockId>` to keep its namespace
   distinct from ours.

### Dependency degradation

| Missing | Frontend | Admin |
|---|---|---|
| WooCommerce | renders nothing | admin notice on every admin page |
| Woodmart `wd/products` | header + tabs render; product slot empty | inline admin-only hint inside the block |
| Selected category | renders nothing | inline admin-only placeholder |

## Block attributes

See `block.json` for the authoritative list. Highlights:

| Attribute | Type | Default | Notes |
|---|---|---|---|
| `categoryId` | number | 0 | Required. WooCommerce `product_cat` term id. |
| `label` | string | `""` | Small uppercase text above title. |
| `title` | string | `""` | Falls back to category name. |
| `showViewAll` | bool | `true` | |
| `viewAllText` | string | `"عرض الكل ←"` | Default is Arabic, override per instance. |
| `viewAllUrl` | string | `""` | Falls back to `get_term_link()`. |
| `showChildren` | bool | `true` | Hides nav entirely if no children. |
| `showParentAsFirstTab` | bool | `true` | |
| `itemsPerPage` | number | `4` | Passed to Woodmart's `items_per_page`. |
| `productSource` | string | `"bestselling"` | Maps to Woodmart `post_type`. |
| `orderby` / `order` | string | `popularity` / `DESC` | |
| `hideOutOfStock` | bool | `true` | |
| `spacing` | string | `"16"` | Woodmart grid spacing (px). |

## Extension points

```php
/**
 * Mutate the attributes passed to the inner wd/products block.
 *
 * Use this to enable Woodmart options not exposed in the inspector
 * (carousel mode, custom hover, etc.).
 */
add_filter( 'wdcs_woodmart_products_attrs', function( array $wd_attrs, array $block_attrs ): array {
	$wd_attrs['products_view'] = 'carousel';
	return $wd_attrs;
}, 10, 2 );
```

## Roadmap

| Version | Scope |
|---|---|
| **v0.1** | Scaffold + architecture (this commit) |
| **v0.2** | First working build, tested against Woodmart in a staging site |
| **v1.0** | Acceptance criteria from spec §25 all green |
| v1.1 | AJAX tab switching (with Woodmart script re-init) |
| v1.2 | Manual product selection |
| v1.3 | Layout variants (centered header, tabs-above-title, background section) |
| v1.4 | Multi-row category showcase |

## License

GPL-2.0-or-later.
