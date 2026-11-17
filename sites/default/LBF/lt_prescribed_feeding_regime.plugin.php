<?php
/**
 * LBF plugin for Prescribed Feeding Regime (lt_prescribed_feeding_regime).
 *
 * Invoked by interface/forms/LBF/new.php when this form is loaded and the
 * plugin file exists under OE_SITE_DIR/LBF/. Reinforces removal of grey
 * horizontal separators between stacked / Add More rows (Route, Volume,
 * Energy, Protein, Delete, Add More).
 *
 * Primary fix is in LBF/new.php (no RS/RO classes + CSS + JS). This plugin
 * is a belt-and-suspenders CSS inject for environments where the form id
 * matches exactly.
 */

/**
 * Extra head JavaScript (and a style inject) for this LBF.
 */
function lt_prescribed_feeding_regime_javascript()
{
    // Exact Bootstrap rule being overridden:
    //   .table > tbody > tr > td { border-top: 1px solid #ddd; }
    echo "
(function () {
  if (document.getElementById('lt-pfr-no-row-borders')) { return; }
  var s = document.createElement('style');
  s.id = 'lt-pfr-no-row-borders';
  s.type = 'text/css';
  s.appendChild(document.createTextNode(
    'table.lbfdata-no-row-borders > tbody > tr > td,' +
    'table.lbfdata-no-row-borders.table > tbody > tr > td,' +
    'table.table.lbfdata-no-row-borders > tbody > tr > td {' +
    '  border-top: 0 !important;' +
    '  border-bottom: 0 !important;' +
    '}'
  ));
  (document.head || document.getElementsByTagName('head')[0]).appendChild(s);
})();
";
}

/**
 * Onload: strip border-top from data cells (and any Add More clones already present).
 */
function lt_prescribed_feeding_regime_javascript_onload()
{
    echo "
$('table.lbfdata-no-row-borders td, table.lbfdata-no-row-borders th').each(function () {
  this.style.setProperty('border-top', '0', 'important');
  this.style.setProperty('border-bottom', '0', 'important');
});
";
}
