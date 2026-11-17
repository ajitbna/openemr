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
    echo "
// Inject CSS that clears row separators even if page CSS order is unusual.
(function () {
  if (document.getElementById('lt-pfr-no-row-borders')) { return; }
  var s = document.createElement('style');
  s.id = 'lt-pfr-no-row-borders';
  s.type = 'text/css';
  s.appendChild(document.createTextNode(
    'form.lbf-form[data-lbf-formname*=\"prescribed_feeding\"] .lbfdata tr,' +
    'form.lbf-form[data-lbf-formname*=\"prescribed_feeding\"] .lbfdata td,' +
    'form[action*=\"prescribed_feeding\"] .lbfdata tr,' +
    'form[action*=\"prescribed_feeding\"] .lbfdata td,' +
    'tr.RS, td.RS, tr.RO, td.RO {' +
    '  border-top:none!important;border-bottom:none!important;' +
    '  border-width:0!important;box-shadow:none!important;' +
    '}'
  ));
  (document.head || document.getElementsByTagName('head')[0]).appendChild(s);
})();
";
}

/**
 * Onload: strip border classes from rendered rows.
 */
function lt_prescribed_feeding_regime_javascript_onload()
{
    echo "
$('form.lbf-form .lbfdata tr, form.lbf-form .lbfdata td').removeClass('RS RO').css({
  'border-top': 'none',
  'border-bottom': 'none',
  'border-width': '0'
});
";
}
