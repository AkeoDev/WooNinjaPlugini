-- ---------------------------------------------------------------------------
-- Najnižja cena (lowestprice) 1.4 -> wooninja-lowestprice 2.0.0
-- Copies the 30-day price history from the old table into the new one.
--
-- The plugin renamed its table but the schema is column-for-column identical:
--   old: wp_lowest_product_price
--   new: wp_lowest_product_price_archive
--   cols: id, product_id, variation_data, price, price_history, lowest_price, created_at
--
-- Without this, the PID / Omnibus "lowest price in the last 30 days" display
-- reads empty until 30 days of fresh data accumulate. That is a legal-display
-- requirement, so run it as part of the cutover, not later.
--
-- PREFIX: this file assumes the default `wp_` prefix (confirmed in wp-config.php).
--         If staging uses a different prefix, search-replace wp_ before running.
--
-- SAFE TO RUN TWICE: the WHERE NOT EXISTS guard skips rows already copied.
-- ADDITIVE ONLY: the source table is never modified. To undo, delete the rows
--                you inserted; wp_lowest_product_price stays intact either way.
-- ---------------------------------------------------------------------------


-- STEP 1 -- Before. Note both numbers down.
SELECT 'BEFORE: source (old)' AS label, COUNT(*) AS rows_found FROM wp_lowest_product_price
UNION ALL
SELECT 'BEFORE: target (new)', COUNT(*) FROM wp_lowest_product_price_archive;


-- STEP 2 -- The copy.
-- `id` is deliberately omitted so the target assigns its own AUTO_INCREMENT
-- values and cannot collide with rows the new plugin has already written.
INSERT INTO wp_lowest_product_price_archive
    (product_id, variation_data, price, price_history, lowest_price, created_at)
SELECT
    src.product_id,
    src.variation_data,
    src.price,
    src.price_history,
    src.lowest_price,
    src.created_at
FROM wp_lowest_product_price AS src
WHERE NOT EXISTS (
    SELECT 1
    FROM wp_lowest_product_price_archive AS dst
    WHERE dst.product_id     = src.product_id
      AND dst.variation_data = src.variation_data
      AND dst.created_at     = src.created_at
);


-- STEP 3 -- After. Target should have grown by (source count - anything
-- already present). Source must be unchanged from STEP 1.
SELECT 'AFTER: source (old)' AS label, COUNT(*) AS rows_found FROM wp_lowest_product_price
UNION ALL
SELECT 'AFTER: target (new)', COUNT(*) FROM wp_lowest_product_price_archive;


-- STEP 4 -- Sanity check: newest history row per table should now match.
SELECT 'newest in source' AS label, MAX(created_at) AS newest FROM wp_lowest_product_price
UNION ALL
SELECT 'newest in target', MAX(created_at) FROM wp_lowest_product_price_archive;
