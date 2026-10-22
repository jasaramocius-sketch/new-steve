# Product Wishlist Icon Issue — Prompt

Purpose
- Help a developer diagnose and fix issues related to the product "wishlist" icon (visual state, click/toggle behavior, persistence, alignment, accessibility) in a Laravel + Blade + JS frontend.

Scope / Context
- Workspace: Laravel app with Blade views, JS in `resources/js` or `public/js`, CSS in `resources/css` or `public/css`.
- Typical files: Blade templates (e.g., `resources/views/product.blade.php`), frontend JS (e.g., `resources/js/app.js`), backend controllers/routes, models, and CSS.

Inputs (required)
- `file_paths` (array): paths to relevant files the assistant should inspect (Blade view, JS, CSS, controller, migration).
- `repro_steps` (string): clear reproduction steps (what you click, expected vs actual behavior).

Inputs (optional)
- `selected_code` (string): a code snippet or selection for focused analysis.
- `browser` (string): browser and version where issue reproduces.
- `console_logs` (string): any JS console or server errors.
- `user_auth_state` (string): whether the user is authenticated, guest, or toggles between states.
- `screenshots` (string): brief description or link to screenshot.

Desired Outputs
- Concise diagnosis: root causes prioritized (e.g., missing AJAX handler, state not saved, wrong CSS selector).
- Suggested fixes: one-line summary of each fix with rationale.
- Minimal patch(es): file diffs or exact code blocks for each changed file with file path headers.
- How-to test: steps to verify fix locally and edge cases.
- Optional: unit/feature test suggestions, accessibility notes (ARIA), performance concerns.

Assistant Instructions (how to use this prompt)
- Inspect provided `file_paths` and `selected_code` first.
- Reproduce mentally using `repro_steps`; call out any missing repro detail.
- Provide the shortest, safest patch that resolves the issue and preserves existing behavior.
- When suggesting JS or Blade changes, include exact snippets and indicate where to insert them.
- When the fix requires DB or backend changes, provide migration or controller edits and mention possible breaking changes.
- If state persistence is needed, prefer server-side storage (user wishlist table) with graceful guest fallback.

Output Format
1. Diagnosis: 2-4 bullets.
2. Fixes: numbered list, each with a short summary and a code patch block labeled by file path.
3. Test steps: 3-6 steps.
4. Notes: compatibility, migration needs, alternate approaches.

Example Invocation
- Describe the issue: "Wishlist heart toggles but resets on page reload for logged-in users."
- Provide files: `resources/views/product.blade.php`, `resources/js/app.js`, `app/Models/Wishlist.php`, `routes/web.php`.
- Provide repro steps: "1) Login 2) Click heart on product page 3) Reload page — heart is unfilled"

Example Assistant Response (abbreviated)
- Diagnosis: Missing AJAX save to server; client toggles only DOM class.
- Fix (Blade): add `data-product-id` attribute to heart element in `resources/views/product.blade.php`.

--- File: resources/views/product.blade.php ---
- (code snippet or replacement shown here)

- Fix (JS): send fetch/XHR to `/wishlist/toggle` on click, update DOM on success.

--- File: resources/js/app.js ---
- (code snippet)

Test steps:
- Login as user, click heart, check DB table `wishlists` for entry, reload page, verify heart persisted.

Clarifying Questions (when needed)
- Do you prefer server-side persistence or localStorage fallback for guests?
- Is there an existing wishlist API endpoint or should I add one?

Suggested Follow-ups / Customizations
- Variant: `product-wishlist-icon-frontend-only.prompt.md` (focus only on frontend JS/CSS).
- Variant: `product-wishlist-icon-backend-first.prompt.md` (focus on API + migrations + tests).


---
Saved as `prompts/product-wishlist-icon.prompt.md` — invoke with the inputs above to get a reproducible, patch-ready fix for wishlist icon issues.
