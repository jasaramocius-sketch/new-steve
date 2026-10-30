# Return Request Rules Verification Plan

## Current behavior
- `ReturnController::create()` excludes `rejected` and `refunded` requests from `$existing`, so rejected quantities are restored.
- Only `pending`/`approved` requests reduce `available_qty` per product.
- `store()` validates submitted qty against remaining qty per product.
- `ReturnRequest` rows are never deleted; history remains.

## Rules
1. Rejected request ke baad user dobara request kar sakta hai — already supported because rejected rows are excluded.
2. Ek order item par ek samay mein sirf ek active return request — enforced via pending/approved qty aggregation.
3. Purani rejected requests history mein available — rows retained and shown in `user.returns.index`.
4. Per-product eligibility maintained — qty calculated per `product_id`.

## Validation
- Test scenario: order with 3 products, return 1 product, verify other 2 remain fully returnable.
- Test rejected return frees quantity.
- Test pending blocks duplicate active request.

## Open questions
- None. Implementation already aligns; only add automated feature tests if desired.
