---
name: shop-page-design
description: Redesign the storefront shop page to closely match the provided interior-showroom reference while preserving Livewire filters and product browsing.
type: project
---

# Shop page redesign

## Goal
Restyle the storefront `/shop` page so it closely matches the provided reference image: dark editorial layout, large hero banner, left filter sidebar, and dense product grid with warm gold accents. Preserve the current Livewire filtering, sorting, pagination, and product linking behavior.

## Scope
### In scope
- Redesign the shop page shell in `resources/views/livewire/shop-page.blade.php`.
- Update the shared shop layout in `resources/views/layouts/app-shop.blade.php` only if needed for spacing or page chrome.
- Refresh the product card presentation in `resources/views/components/product-card.blade.php` so cards visually align with the reference grid.
- Keep all current ShopPage Livewire state, query-string behavior, filters, and routing intact.
- Use existing product media and fallback images; do not change data models.

### Out of scope
- Changing product, category, brand, or cart business logic.
- Adding new filters or sorting modes.
- Introducing new data fields or migrations.
- Reworking unrelated storefront pages.

## Design direction
The page should feel like a luxury showroom catalogue:
- Full-width dark hero with a large background image and editorial typography.
- Narrow left sidebar with stacked filter groups and subtle divider lines.
- Right content area with compact controls, small sort select, and a 3-column card grid on desktop.
- Product cards with tight spacing, square imagery, title, price, and a warm gold add-to-cart button treatment.
- Mobile layout should collapse the sidebar above the grid while keeping the same visual language.

## Implementation approach
1. Keep the current Livewire component and query logic untouched.
2. Replace the current shop page markup with a two-part layout:
   - hero banner / intro section
   - content area with filters + product grid
3. Restyle the filter UI using the existing Livewire bindings.
4. Restyle `x-product-card` to match the screenshot’s card density and button hierarchy.
5. Reuse the existing pagination and empty-state behavior with matching styling.

## Visual fidelity notes
- Use dark blue-gray / charcoal backgrounds with warm gold buttons and accents.
- Make the hero image span the full viewport width and keep the copy aligned left.
- Keep filter labels small, uppercase, and lightly spaced.
- Use a clean grid with consistent tile heights and minimal chrome around cards.

## Risks and constraints
- The current product card component is used elsewhere, so changes should remain acceptable outside the shop page.
- Some products may have different image aspect ratios; the card must crop cleanly without breaking the grid.
- The screenshot includes filter content not currently present in the app; the redesign should visually echo that structure without inventing new data requirements.

## Verification
- Confirm `/shop` still loads categories, brands, search, sort, and pagination correctly.
- Confirm category routes like `/shop/{category}` still render the correct active category.
- Check desktop and mobile layouts for spacing, clipping, and grid alignment.
- Confirm product cards still link to the product detail page.
