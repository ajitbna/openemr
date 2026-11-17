-- Clear LBF row-border edit options (RS/RO/DS/DO) on Prescribed Feeding Regime.
-- These options cause thin grey horizontal lines between stacked / Add More rows.
-- Safe to re-run. Covers common form_id variants.
--
--   mysql ... < sql/lt_prescribed_feeding_regime_clear_row_borders.sql

-- Legacy concatenated edit_options (e.g. "PRS", "RS").
UPDATE layout_options
SET edit_options = REPLACE(REPLACE(REPLACE(REPLACE(edit_options, 'RS', ''), 'RO', ''), 'DS', ''), 'DO', '')
WHERE form_id LIKE '%prescribed_feeding%';

-- JSON array edit_options (e.g. ["P","RS"]).
UPDATE layout_options
SET edit_options = REPLACE(REPLACE(REPLACE(edit_options, '"RS",', ''), ',"RS"', ''), '"RS"', '')
WHERE form_id LIKE '%prescribed_feeding%' AND edit_options LIKE '%RS%';

UPDATE layout_options
SET edit_options = REPLACE(REPLACE(REPLACE(edit_options, '"RO",', ''), ',"RO"', ''), '"RO"', '')
WHERE form_id LIKE '%prescribed_feeding%' AND edit_options LIKE '%RO%';

UPDATE layout_options
SET edit_options = REPLACE(REPLACE(REPLACE(edit_options, '"DS",', ''), ',"DS"', ''), '"DS"', '')
WHERE form_id LIKE '%prescribed_feeding%' AND edit_options LIKE '%DS%';

UPDATE layout_options
SET edit_options = REPLACE(REPLACE(REPLACE(edit_options, '"DO",', ''), ',"DO"', ''), '"DO"', '')
WHERE form_id LIKE '%prescribed_feeding%' AND edit_options LIKE '%DO%';
