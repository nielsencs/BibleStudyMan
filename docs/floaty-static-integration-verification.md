# Floaty/static integration verification

Branch verified: `ezra/integrate-good-stuff-develop`

Purpose: preserve the hard-won BSM floaty/static behaviour while integrating search, layout, and TCSB support work into the develop line.

## Source-of-truth behaviours checked

- `floaty=off` keeps the main menu/header wrapper sticky, matching the live non-floaty site behaviour.
- `floaty=off` puts the Bible/Plan control panel inside the page content, not inside the menu wrapper.
- `floaty=on` keeps the control panel inside the sticky menu wrapper.
- The translucent dark background applies to the menu generally and to `#controlPanel` only when it is inside `.menu-wrapper.is-floaty`.
- Interesting-word options are only wrapped in `#wordsSection.searchOptions` for floaty mode.
- Highlight colours use the lighter values from the final floaty/static work:
  - `#ffdd0050`
  - `#7700ff50`
  - `#00ff0050`

## Measured browser check

On Genesis 29, after scrolling 900px:

| URL mode | wrapper class | computed position | wrapper top | control panel location |
| --- | --- | --- | --- | --- |
| `floaty=off` | `menu-wrapper is-static` | `sticky` | `0px` | page content |
| `floaty=on` | `menu-wrapper is-floaty` | `sticky` | `0px` | menu wrapper |

Screenshots were captured locally under:

`/home/carl/.openclaw/workspace/bsm-screenshots/integration-sticky-check-20260610/`

## Known visual tradeoff

With `floaty=on`, the open floating controls occupy a large dark area over/above the passage while scrolled. That is known from verification; it is not the same issue as accidentally making non-floaty non-sticky.

## Why this file exists

This integration involved several long-lived branches whose commit ancestry did not guarantee final file content. Future review should check the behaviours above directly, not rely only on PR previews or “branch contains commit”.
