# MarketLink - project rules

Farmers market pre-order platform (TechWiz SRS). Roles: customer, farmer, admin.
Stack: Laravel + Breeze (Blade), MySQL, Tailwind CSS 3, Alpine.js, iconify-icon, Leaflet + OpenStreetMap.

## Code style (most important)
- Code must read like a developer wrote it by hand. We will be interviewed on every file.
- Every file, class, function, variable and loop variable name must say exactly what it holds or does
  (e.g. $ordersWaitingForReply, reserveStockForOrder(), $approvedFarmer - never $data, $item2, $res, $temp).
- Comments Roman Urdu mein, chhote, sirf mushkil jagah pe (technical words English mein hi rehne do).
  Zyada tar 1 line, mushkil logic pe max 2-3 lines. Sirf business rule, tricky logic ya "kyun" likho -
  jo naam se samajh aa jaye us pe comment nahi. Example: `// urgent order sirf pehle 5 min mein cancel ho sakta hai`.
  No docblocks on every method (sirf @throws / @return jaise IDE wale tags), no comments about old or removed features.
- Keep controllers thin and readable. No clever one-liners that are hard to explain.
- Use existing enums in app/Enums instead of raw status strings.

## UI rules
- Use the existing design system only: colors leaf / tomato / cream / soil in tailwind.config.js,
  classes in resources/css/app.css (btn-primary, btn-accent, btn-outline, card, icon-chip, form-input, table-clean).
- Reuse components in resources/views/components/ui and the panel layout. Don't invent new colors.
- Real photos from public/images (see IMAGES.md), with object-cover and fixed aspect ratio. Never use an icon where a photo belongs.
- Icons go inside a soft round icon-chip. Buttons and badges are pill shaped.
- Fonts: Poppins for headings (font-display), Inter for text.
- Every page must be responsive, have alt text on images and visible keyboard focus.
- Copy: plain, specific, sentence case. No emoji, no ALL CAPS labels, no "unlock / seamless / elevate", no arrows in button text.
- Every feature is built with its finished UI at the same time, never "UI later".

## Workflow
- Don't touch files outside the task unless needed, and say which ones you changed.
- After each task, explain in simple words what each changed file does.