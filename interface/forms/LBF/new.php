<?php
/**
 * LBF form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
if ($GLOBALS['gbl_portal_cms_enable']) {
    require_once("$include_root/cmsportal/portal.inc.php");
}
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once("$srcdir/FeeSheetHtml.class.php");

use OpenEMR\Core\Header;

$CPR = 4; // cells per row

$pprow = array();

$alertmsg = '';

function end_cell()
{
    global $item_count, $cell_count, $historical_ids;
    if ($item_count > 0) {
        // echo "&nbsp;</td>";
        echo "</td>";

        foreach ($historical_ids as $key => $dummy) {
            // $historical_ids[$key] .= "&nbsp;</td>";
            $historical_ids[$key] .= "</td>";
        }

        $item_count = 0;
    }
}

function end_row()
{
    global $cell_count, $CPR, $historical_ids;
    end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR; ++$cell_count) {
            echo "<td></td>";
            foreach ($historical_ids as $key => $dummy) {
                $historical_ids[$key] .= "<td></td>";
            }
        }

        foreach ($historical_ids as $key => $dummy) {
            echo $historical_ids[$key];
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

// $is_lbf is defined in trend_form.php and indicates that we are being
// invoked from there; in that case the current encounter is irrelevant.
$from_trend_form = !empty($is_lbf);
// Yet another invocation from somewhere other than encounter.
// don't show any action buttons.
$from_lbf_edit = isset($_GET['isShow']) ? true : false;
// This is true if the page is loaded into an iframe in add_edit_issue.php.
$from_issue_form = !empty($_REQUEST['from_issue_form']);

$formname = isset($_GET['formname']) ? $_GET['formname'] : '';
$formid = isset($_GET['id']) ? intval($_GET['id']) : 0;
$portalid = isset($_GET['portalid']) ? intval($_GET['portalid']) : 0;

// Shared LBF UI flags — extend these matchers for additional formnames.
//
// REFERENCE chrome: lt_ward_dietary_requirements. Patient Transfer and Referral
// match that look (header/actions flush is universal via lbf-card-flush).
// Ward-style forms get mild valign-middle + Add More border kill — NOT the
// meal-satisfaction nuclear flex vcenter (flex breaks multi-column CPR rows).
$lbf_ward_style = (stripos($formname, 'ward_dietary') !== false)
    || (stripos($formname, 'dietary_requirements') !== false)
    || (stripos($formname, 'patient_transfer') !== false)
    || (stripos($formname, 'referral') !== false)
    || (stripos($formname, 'lbtref') !== false);
// Meal Satisfaction only: nuclear flex vertical center.
$lbf_vcenter_fields = (stripos($formname, 'meal_satisfaction') !== false)
    || (stripos($formname, 'meal_sat') !== false)
    || (bool) preg_match('/meal.*satisfac|satisfac.*meal/i', (string) $formname);
// Suppress Bootstrap/LBF row border-top on Add More / stacked rows.
$lbf_suppress_row_borders = (stripos($formname, 'prescribed_feeding') !== false)
    || (stripos($formname, 'feeding_regime') !== false)
    || !empty($lbf_ward_style);

$visitid = intval(empty($_GET['visitid']) ? $encounter : $_GET['visitid']);

// If necessary get the encounter from the forms table entry for this form.
if ($formid && !$visitid) {
    $frow = sqlQuery(
        "SELECT pid, encounter FROM forms WHERE " .
        "form_id = ? AND formdir = ? AND deleted = 0",
        array($formid, $formname)
    );
    $visitid = intval($frow['encounter']);
    if ($frow['pid'] != $pid) {
        die("Internal error: patient ID mismatch!");
    }
}

if (!$from_trend_form && !$visitid && !$from_lbf_edit) {
    die("Internal error: we do not seem to be in an encounter!");
}

$grparr = array();
getLayoutProperties($formname, $grparr, '*');
$lobj = $grparr[''];
$formtitle = $lobj['grp_title'];
$formhistory = 0 + $lobj['grp_repeats'];
if (!empty($lobj['grp_columns'])) {
    $CPR = intval($lobj['grp_columns']);
}
// Also match by layout / group title.
if (!$lbf_ward_style && !empty($formtitle)) {
    $lbf_ward_style = (stripos($formtitle, 'Ward Dietary') !== false)
        || (stripos($formtitle, 'Dietary Requirements') !== false)
        || (stripos($formtitle, 'Patient Transfer') !== false)
        || (bool) preg_match('/ward\s*dietary|dietary\s*requirements|patient\s*transfer|\breferral\b/i', (string) $formtitle);
}
if (!$lbf_ward_style && is_array($grparr)) {
    foreach ($grparr as $gprop) {
        if (empty($gprop['grp_title'])) {
            continue;
        }
        if (preg_match('/ward\s*dietary|dietary\s*requirements|patient\s*transfer|referral\s*(information|details)|counter[\s-]*referral/i', (string) $gprop['grp_title'])) {
            $lbf_ward_style = true;
            break;
        }
    }
}
if ($lbf_ward_style) {
    $lbf_suppress_row_borders = true;
}
if (!$lbf_vcenter_fields && !empty($formtitle)) {
    $lbf_vcenter_fields = (stripos($formtitle, 'Meal Satisfaction') !== false)
        || (stripos($formtitle, 'MEAL SATISFACTION') !== false)
        || (bool) preg_match('/meal\s*satisfac/i', (string) $formtitle);
}
if (!$lbf_vcenter_fields && is_array($grparr)) {
    foreach ($grparr as $gprop) {
        if (empty($gprop['grp_title'])) {
            continue;
        }
        if (preg_match('/meal\s*satisfac/i', (string) $gprop['grp_title'])) {
            $lbf_vcenter_fields = true;
            break;
        }
    }
}
if (!$lbf_suppress_row_borders && !empty($formtitle)) {
    $lbf_suppress_row_borders = (stripos($formtitle, 'Prescribed Feeding') !== false)
        || (stripos($formtitle, 'Feeding Regime') !== false);
}
if (!empty($lobj['grp_size'])) {
    $FONTSIZE = intval($lobj['grp_size']);
}
if (!empty($lobj['grp_issue_type'])) {
    $LBF_ISSUE_TYPE = $lobj['grp_issue_type'];
}
if (!empty($lobj['grp_aco_spec'])) {
    $LBF_ACO = explode('|', $lobj['grp_aco_spec']);
}
if ($lobj['grp_services']) {
    $LBF_SERVICES_SECTION = $lobj['grp_services'] == '*' ? '' : $lobj['grp_services'];
}
if ($lobj['grp_products']) {
    $LBF_PRODUCTS_SECTION = $lobj['grp_products'] == '*' ? '' : $lobj['grp_products'];
}
if ($lobj['grp_diags']) {
    $LBF_DIAGS_SECTION = $lobj['grp_diags'] == '*' ? '' : $lobj['grp_diags'];
}

// Check access control.
if (!acl_check('admin', 'super') && !empty($LBF_ACO)) {
    $auth_aco_write = acl_check($LBF_ACO[0], $LBF_ACO[1], '', 'write');
    $auth_aco_addonly = acl_check($LBF_ACO[0], $LBF_ACO[1], '', 'addonly');
    // echo "\n<!-- '$auth_aco_write' '$auth_aco_addonly' -->\n"; // debugging
    if (!$auth_aco_write && !($auth_aco_addonly && !$formid)) {
        die(xlt('Access denied'));
    }
}

if (isset($LBF_SERVICES_SECTION) || isset($LBF_PRODUCTS_SECTION) || isset($LBF_DIAGS_SECTION)) {
    $fs = new FeeSheetHtml($pid, $visitid);
}

if (!$from_trend_form) {
    $fname = $GLOBALS['OE_SITE_DIR'] . "/LBF/$formname.plugin.php";
    if (file_exists($fname)) {
        include_once($fname);
    }
}

// If Save was clicked, save the info.
//
if (!empty($_POST['bn_save']) || !empty($_POST['bn_save_print']) || !empty($_POST['bn_save_continue'])) {
    $newid = 0;
    if (!$formid) {
        // Creating a new form. Get the new form_id by inserting and deleting a dummy row.
        // This is necessary to create the form instance even if it has no native data.
        $newid = sqlInsert("INSERT INTO lbf_data " .
            "( field_id, field_value ) VALUES ( '', '' )");
        sqlStatement("DELETE FROM lbf_data WHERE form_id = ? AND " .
            "field_id = ''", array($newid));
        addForm($visitid, $formtitle, $newid, $formname, $pid, $userauthorized);
    }

    $my_form_id = $formid ? $formid : $newid;

    // If there is an issue ID, update it in the forms table entry.
    if (isset($_POST['form_issue_id'])) {
        sqlStatement(
            "UPDATE forms SET issue_id = ? WHERE formdir = ? AND form_id = ? AND deleted = 0",
            array($_POST['form_issue_id'], $formname, $my_form_id)
        );
    }

    // If there is a provider ID, update it in the forms table entry.
    if (isset($_POST['form_provider_id'])) {
        sqlStatement(
            "UPDATE forms SET provider_id = ? WHERE formdir = ? AND form_id = ? AND deleted = 0",
            array($_POST['form_provider_id'], $formname, $my_form_id)
        );
    }

    $sets = "";
    $fres = sqlStatement("SELECT * FROM layout_options " .
        "WHERE form_id = ? AND uor > 0 AND field_id != '' AND " .
        "edit_options != 'H' AND edit_options NOT LIKE '%0%' " .
        "ORDER BY group_id, seq", array($formname));
    while ($frow = sqlFetchArray($fres)) {
        $field_id = $frow['field_id'];
        $data_type = $frow['data_type'];
        // If the field was not in the web form, skip it.
        // Except if it's checkboxes, if unchecked they are not returned.
        //
        // if ($data_type != 21 && !isset($_POST["form_$field_id"])) continue;
        //
        // The above statement commented out 2015-01-12 because a LBF plugin might conditionally
        // disable a field that is not applicable, and we need the ability to clear out the old
        // garbage in there so it does not show up in the "report" view of the data.  So we will
        // trust that it's OK to clear any field that is defined in the layout but not returned
        // by the form.
        //
        if ($data_type == 31) {
            continue; // skip static text fields
        }
        $value = get_layout_form_value($frow);
        // If edit option P or Q, save to the appropriate different table and skip the rest.
        $source = $frow['source'];
        if ($source == 'D' || $source == 'H') {
            // Save to patient_data, employer_data or history_data.
            if ($source == 'H') {
                $new = array($field_id => $value);
                updateHistoryData($pid, $new);
            } else if (strpos($field_id, 'em_') === 0) {
                $field_id = substr($field_id, 3);
                $new = array($field_id => $value);
                updateEmployerData($pid, $new);
            } else {
                $esc_field_id = escape_sql_column_name($field_id, array('patient_data'));
                sqlStatement(
                    "UPDATE patient_data SET `$esc_field_id` = ? WHERE pid = ?",
                    array($value, $pid)
                );
            }

            continue;
        } else if ($source == 'E') {
            // Save to shared_attributes. Can't delete entries for empty fields because with the P option
            // it's important to know when a current empty value overrides a previous value.
            sqlStatement(
                "REPLACE INTO shared_attributes SET " .
                "pid = ?, encounter = ?, field_id = ?, last_update = NOW(), " .
                "user_id = ?, field_value = ?",
                array($pid, $visitid, $field_id, $_SESSION['authUserID'], $value)
            );
            continue;
        } else if ($source == 'V') {
            // Save to form_encounter.
            $esc_field_id = escape_sql_column_name($field_id, array('form_encounter'));
            sqlStatement(
                "UPDATE form_encounter SET `$esc_field_id` = ? WHERE " .
                "pid = ? AND encounter = ?",
                array($value, $pid, $visitid)
            );
            continue;
        }

        // It's a normal form field, save to lbf_data.
        if ($formid) { // existing form
            if ($value === '') {
                $query = "DELETE FROM lbf_data WHERE " .
                    "form_id = ? AND field_id = ?";
                sqlStatement($query, array($formid, $field_id));
            } else {
                $query = "REPLACE INTO lbf_data SET field_value = ?, " .
                    "form_id = ?, field_id = ?";
                sqlStatement($query, array($value, $formid, $field_id));
            }
        } else { // new form
            if ($value !== '') {
                sqlStatement(
                    "INSERT INTO lbf_data " .
                    "( form_id, field_id, field_value ) VALUES ( ?, ?, ? )",
                    array($newid, $field_id, $value)
                );
            }
        }
    }

    if ($portalid) {
        // Delete the request from the portal.
        $result = cms_portal_call(array('action' => 'delpost', 'postid' => $portalid));
        if ($result['errmsg']) {
            die(text($result['errmsg']));
        }
    }

    if (isset($fs)) {
        $bill = is_array($_POST['form_fs_bill']) ? $_POST['form_fs_bill'] : null;
        $prod = is_array($_POST['form_fs_prod']) ? $_POST['form_fs_prod'] : null;
        $alertmsg = $fs->checkInventory($prod);
        // If there is an inventory error then no services or products will be saved, and
        // the form will be redisplayed with an error alert and everything else saved.
        if (!$alertmsg) {
            $fs->save($bill, $prod, null, null);
            $fs->updatePriceLevel($_POST['form_fs_pricelevel']);
        }
    }

    if (!$formid) {
        $formid = $newid;
    }

    if (!$alertmsg && !$from_issue_form && empty($_POST['bn_save_continue'])) {
        // Support custom behavior at save time, such as going to another form.
        if (function_exists($formname . '_save_exit')) {
            if (call_user_func($formname . '_save_exit')) {
                exit;
            }
        }
        formHeader("Redirecting....");
        // If Save and Print, write the JavaScript to open a window for printing.
        if (!empty($_POST['bn_save_print'])) {
            echo "<script language='Javascript'>\n" .
                "top.restoreSession();\n" .
                "window.open('$rootdir/forms/LBF/printable.php?" .
                "formname=" . urlencode($formname) .
                "&formid=" . urlencode($formid) .
                "&visitid=" . urlencode($visitid) .
                "&patientid=" . urlencode($pid) .
                "', '_blank');\n" .
                "</script>\n";
        }
        formJump();
        formFooter();
        exit;
    }
}

?>
<html>
<head>
    <?php Header::setupHeader(['opener', 'common', 'datetime-picker', 'jquery-ui',]); ?>

    <style>

        td, input, select, textarea {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
        }

        .table > tbody > tr > td {
            border-top: 0;
        }

        div.section {
            border: solid;
            border-width: 1px;
            border-color: #0000ff;
            margin: 0 0 0 10pt;
            padding: 5pt;
        }

        .form-control {
            width: auto;
            display: inline;
            height: auto;
        }

        .RS {
            border-style: solid;
            border-width: 0 0 1px 0;
            border-color: #999999;
        }

        .RO {
            border-style: solid;
            border-width: 1px 1px 1px 1px !important;
            border-color: #999999;
        }

        /*
         * Match Meal Satisfaction Questionnaire card chrome:
         * - Title (blue page-header) left edge flush with white card
         * - Save / Cancel / Back button group right edge flush with white card
         *
         * Title + actions sit in Bootstrap .col-xs-12 (15px gutters) while group
         * fields are bare .row children. Zero those gutters and right-align
         * actions so both edges match the card (same as lt_meal_satisfaction).
         */
        body.body_top > .container.lbf-card-flush,
        .container.lbf-card-flush {
            width: 100% !important;
            max-width: none !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .container.lbf-card-flush > form > .container-responsive > .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        .lbf-page-header-col,
        .lbf-actions-col {
            padding-left: 0 !important;
            padding-right: 0 !important;
            float: none !important;
            width: 100% !important;
        }
        .lbf-page-header-col > .page-header {
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-top: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .lbf-page-header-col > .page-header > h3 {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        /* Buttons: right edge flush with card (Meal Satisfaction look). */
        .lbf-actions-col {
            text-align: right !important;
        }
        .lbf-actions-col > .btn-group {
            float: none !important;
            display: inline-block;
            text-align: left;
            margin-right: 0 !important;
        }

        .lbf-actions-col > hr {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        /*
         * Ward Dietary / Patient Transfer / Referral multi-card reference:
         * Scoped strictly under body.lbf-ward-style so it has zero effect on
         * other LBFs while staying 100% available even if activated via JS fallback.
         * - Grey page background (#e9ecef); header bar OUTSIDE cards on top
         * - Blue #2a6ebb top bar: title + provider left, relocated actions right
         * - Each group/section = its OWN white rounded .lbf-group-card with gap
         */
        html:has(body.lbf-ward-style),
        html body.lbf-ward-style,
        html body.lbf-ward-style.body_top {
            margin: 0 !important;
            padding: 0 !important;
            background: #e9ecef !important;
            background-color: #e9ecef !important;
            min-height: 100%;
        }
        html body.lbf-ward-style.body_top > .container,
        html body.lbf-ward-style .container.lbf-card-flush,
        html body.lbf-ward-style form.lbf-form,
        html body.lbf-ward-style .container-responsive,
        html body.lbf-ward-style .container-responsive > .row,
        html body.lbf-ward-style .col-xs-12 {
            background: #e9ecef !important;
            background-color: #e9ecef !important;
            box-shadow: none !important;
        }
        html body.lbf-ward-style .container.lbf-card-flush {
            padding: 12px 14px !important;
            max-width: none !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        html body.lbf-ward-style .container-responsive > .row::before,
        html body.lbf-ward-style .container-responsive > .row::after {
            display: none !important;
        }
        /* Header bar OUTSIDE cards, flush with left/right card edges */
        html body.lbf-ward-style .lbf-top-bar {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px 14px !important;
            flex-wrap: wrap !important;
            background: #2a6ebb !important;
            background-color: #2a6ebb !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            margin: 0 0 16px 0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border: 0 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08) !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-title,
        html body.lbf-ward-style .lbf-top-bar .page-header {
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 6px 14px !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            color: #ffffff !important;
            background: transparent !important;
            background-color: transparent !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-title h3,
        html body.lbf-ward-style .lbf-top-bar .page-header h3 {
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            color: #ffffff !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            line-height: 1.3 !important;
            background: transparent !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-meta {
            display: inline-flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
            font-size: 12.5px !important;
            font-weight: 400 !important;
            color: #e8f1fb !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-title select,
        html body.lbf-ward-style .lbf-top-bar .page-header select {
            color: #1f2937 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 1px solid #c5d6ea !important;
            border-radius: 4px !important;
            padding: 3px 8px !important;
            height: 28px !important;
            font-size: 12.5px !important;
            line-height: 1.3 !important;
            margin: 0 !important;
        }
        html body.lbf-ward-style .lbf-top-actions {
            margin-left: auto !important;
            flex-shrink: 0 !important;
        }
        html body.lbf-ward-style .lbf-top-actions .btn-group {
            float: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            flex-wrap: wrap !important;
            background: transparent !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn,
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn-link,
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn-default {
            float: none !important;
            margin: 0 !important;
            margin-left: 0 !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #1f3b5b !important;
            border: 1px solid #d0dbe6 !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            padding: 5px 11px !important;
            font-size: 13px !important;
            line-height: 1.3 !important;
            font-weight: 500 !important;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05) !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn-save {
            font-weight: 600 !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn:hover,
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn-link:hover,
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions .btn-default:hover {
            background: #eef4fa !important;
            background-color: #eef4fa !important;
            color: #132840 !important;
            border-color: #b7c9dc !important;
        }
        html body.lbf-ward-style .lbf-top-bar .lbf-top-actions select {
            color: #1f2937 !important;
            background: #ffffff !important;
            border: 1px solid #c5d6ea !important;
            border-radius: 4px !important;
            padding: 3px 6px !important;
            height: 28px !important;
            font-size: 12.5px !important;
        }
        html body.lbf-ward-style .lbf-actions-col.lbf-actions-relocated,
        html body.lbf-ward-style br.lbf-actions-spacer-hidden {
            display: none !important;
        }
        /* Stack of SEPARATE white rounded cards (grey #e9ecef gap between each card) */
        html body.lbf-ward-style .lbf-cards-stack {
            display: block !important;
            width: 100% !important;
            background: transparent !important;
            background-color: transparent !important;
        }
        html body.lbf-ward-style .lbf-group-card {
            display: block !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 1px solid #c5ced8 !important;
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06) !important;
            padding: 14px 16px 16px 16px !important;
            margin: 0 0 18px 0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
            float: none !important;
            clear: both !important;
        }
        html body.lbf-ward-style .lbf-group-card:last-child {
            margin-bottom: 4px !important;
        }
        html body.lbf-ward-style .lbf-group-card-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin: 0 0 12px 0 !important;
            padding: 0 0 8px 0 !important;
            border: 0 !important;
            border-bottom: 1px solid #e6ebf0 !important;
            background: transparent !important;
            background-color: transparent !important;
            font-weight: 700 !important;
            font-size: 13.5px !important;
            line-height: 1.35 !important;
            color: #1f3b5b !important;
        }
        html body.lbf-ward-style .lbf-group-card-header .lbf-group-card-title,
        html body.lbf-ward-style .lbf-group-card-header > span {
            display: inline-flex !important;
            align-items: center !important;
            gap: 7px !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #1f3b5b !important;
            font-size: 13.5px !important;
            font-weight: 700 !important;
        }
        html body.lbf-ward-style .lbf-group-card-header b {
            color: #1f3b5b !important;
            font-weight: 700 !important;
        }
        html body.lbf-ward-style .lbf-group-card-header input[type=checkbox],
        html body.lbf-ward-style .lbf-group-card-header .lbf-group-toggle {
            margin: 0 !important;
            vertical-align: middle !important;
            flex-shrink: 0 !important;
            cursor: pointer;
        }
        html body.lbf-ward-style .lbf-group-card div.section,
        html body.lbf-ward-style .lbf-group-card .lbf-group-card-body,
        html body.lbf-ward-style div.section.lbf-group-card-body {
            border: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            background-color: transparent !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        html body.lbf-ward-style table.lbfdata {
            width: 100% !important;
            border-collapse: collapse !important;
            background: transparent !important;
            background-color: transparent !important;
            margin: 0 !important;
        }
        html body.lbf-ward-style table.lbfdata > tbody > tr > td,
        html body.lbf-ward-style table.lbfdata > tbody > tr > th,
        .table > tbody > tr > td.lbf-ward-cell {
            padding: 6px 8px !important;
            vertical-align: middle !important;
            background: transparent !important;
            background-color: transparent !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
            color: #1f2937 !important;
        }
        html body.lbf-ward-style table.lbfdata > tbody > tr > td:first-child {
            padding-left: 0 !important;
        }
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.bold,
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.required {
            font-weight: 600 !important;
            white-space: nowrap;
        }
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.bold:not([colspan]),
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.required:not([colspan]),
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.bold[colspan="1"],
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.required[colspan="1"] {
            width: 1% !important;
            padding-right: 14px !important;
        }
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.required,
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.required b {
            color: #b42318 !important;
        }
        html body.lbf-ward-style table.lbfdata > tbody > tr > td.lbf-group-subtitle {
            color: #4a607a !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            padding-top: 2px !important;
            padding-bottom: 6px !important;
        }
        html body.lbf-ward-style .lbf-group-card table.lbfdata td table {
            width: auto !important;
            max-width: 100% !important;
            background: transparent !important;
            margin: 0 !important;
        }
        html body.lbf-ward-style .lbf-group-card table.lbfdata td table td {
            padding: 3px 14px 3px 0 !important;
            vertical-align: middle !important;
            background: transparent !important;
            font-size: 13px !important;
        }
        /* Unified form controls inside cards (beats style_light.css harsh dark borders) */
        html body.lbf-ward-style .lbf-group-card input[type="text"],
        html body.lbf-ward-style .lbf-group-card input[type="number"],
        html body.lbf-ward-style .lbf-group-card input[type="date"],
        html body.lbf-ward-style .lbf-group-card input[type="email"],
        html body.lbf-ward-style .lbf-group-card input[type="tel"],
        html body.lbf-ward-style .lbf-group-card select,
        html body.lbf-ward-style .lbf-group-card textarea {
            background-color: #ffffff !important;
            border: 1px solid #c5ced8 !important;
            border-radius: 4px !important;
            padding: 5px 8px !important;
            margin: 0 !important;
            font-size: 13px !important;
            color: #1f2937 !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            box-shadow: none !important;
        }
        html body.lbf-ward-style .lbf-group-card input[type="text"],
        html body.lbf-ward-style .lbf-group-card input[type="number"],
        html body.lbf-ward-style .lbf-group-card input[type="date"],
        html body.lbf-ward-style .lbf-group-card input[type="email"],
        html body.lbf-ward-style .lbf-group-card input[type="tel"],
        html body.lbf-ward-style .lbf-group-card select {
            min-height: 30px !important;
            height: auto !important;
            line-height: 1.35 !important;
            display: inline-block !important;
        }
        html body.lbf-ward-style .lbf-group-card textarea {
            min-height: 68px !important;
            line-height: 1.4 !important;
            resize: vertical;
            display: block !important;
            width: 100% !important;
        }
        html body.lbf-ward-style .lbf-group-card input[type="text"]:focus,
        html body.lbf-ward-style .lbf-group-card select:focus,
        html body.lbf-ward-style .lbf-group-card textarea:focus {
            border-color: #2a6ebb !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(42, 110, 187, 0.15) !important;
        }
        html body.lbf-ward-style .lbf-group-card input[type="radio"],
        html body.lbf-ward-style .lbf-group-card input[type="checkbox"] {
            margin: 0 !important;
            vertical-align: middle !important;
            width: auto !important;
            height: auto !important;
            padding: 0 !important;
        }
        html body.lbf-ward-style .lbf-group-card label.lbf-radio-option,
        html body.lbf-ward-style .lbf-group-card label.lbf-checkbox-option {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin: 0 !important;
            font-weight: 400 !important;
            color: #1f2937 !important;
            white-space: nowrap;
        }
        html body.lbf-ward-style .lbf-group-card button,
        html body.lbf-ward-style .lbf-group-card input[type="button"] {
            background: #f8fafc !important;
            background-color: #f8fafc !important;
            color: #1f3b5b !important;
            border: 1px solid #c5ced8 !important;
            border-radius: 4px !important;
            padding: 4px 10px !important;
            font-size: 12.5px !important;
            font-weight: 500 !important;
            cursor: pointer;
        }
        html body.lbf-ward-style .lbf-group-card button:hover,
        html body.lbf-ward-style .lbf-group-card input[type="button"]:hover {
            background: #eef4fa !important;
            border-color: #9cb2c9 !important;
        }
        /* Kill legacy blue section box if still present outside cards */
        html body.lbf-ward-style div.section:not(.lbf-group-card-body) {
            border: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
        }

        <?php if (!empty($lbf_vcenter_fields)) { ?>
        /*
         * Meal Satisfaction nuclear flex vertical center (NOT used for ward dietary /
         * patient transfer / referral — those use lbf-ward-style mild valign instead).
         * Beats Bootstrap `.table td { vertical-align: top }` and LBF valign=top.
         */
        body.lbf-vcenter-fields form.lbf-form table.lbfdata,
        body.lbf-vcenter-fields table.lbfdata,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata {
            border-collapse: separate !important;
        }
        body.lbf-vcenter-fields form.lbf-form table.lbfdata > tbody > tr,
        body.lbf-vcenter-fields table.lbfdata > tbody > tr,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata > tbody > tr {
            display: flex !important;
            align-items: center !important;
            width: 100%;
        }
        body.lbf-vcenter-fields form.lbf-form table.lbfdata > tbody > tr > td,
        body.lbf-vcenter-fields form.lbf-form table.lbfdata td,
        body.lbf-vcenter-fields table.lbfdata > tbody > tr > td,
        body.lbf-vcenter-fields table.lbfdata td,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata > tbody > tr > td,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata td,
        .table > tbody > tr > td.lbf-vcenter-cell {
            vertical-align: middle !important;
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap;
            float: none !important;
        }
        body.lbf-vcenter-fields table.lbfdata td table,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata td table {
            width: auto !important;
            margin: 0 !important;
        }
        body.lbf-vcenter-fields table.lbfdata td table td,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata td table td {
            vertical-align: middle !important;
            display: table-cell !important;
            align-items: unset;
            flex-wrap: unset;
        }
        body.lbf-vcenter-fields table.lbfdata input,
        body.lbf-vcenter-fields table.lbfdata select,
        body.lbf-vcenter-fields table.lbfdata textarea,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata input,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata select,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata textarea {
            vertical-align: middle !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
        <?php } ?>

        <?php if (!empty($lbf_suppress_row_borders)) { ?>
        /*
         * Shared LBF row-border kill (feeding regime, ward dietary / patient transfer / referral Add More, …).
         * Wins over Bootstrap: .table > tbody > tr > td { border-top: 1px solid #ddd; }
         */
        html body form.lbf-form table,
        html body form.lbf-form table.lbfdata,
        html body form.lbf-form table.lbfdata-no-row-borders,
        html body form.lbf-form table.table,
        html body form.lbf-form .table,
        html body .section table,
        html body table.lbfdata,
        html body table.lbfdata-no-row-borders {
            border-collapse: collapse !important;
            border: 0 !important;
        }
        html body form.lbf-form table tr,
        html body form.lbf-form table td,
        html body form.lbf-form table th,
        html body form.lbf-form .table > tbody > tr > td,
        html body form.lbf-form .table > tbody > tr > th,
        html body .section table tr,
        html body .section table td,
        html body .section table th,
        html body table.lbfdata tr,
        html body table.lbfdata td,
        html body table.lbfdata th,
        html body table.lbfdata > tbody > tr > td,
        html body table.lbfdata-no-row-borders tr,
        html body table.lbfdata-no-row-borders td,
        html body table.lbfdata-no-row-borders th,
        html body table.lbfdata-no-row-borders > tbody > tr > td,
        html body tr.RS,
        html body tr.RO,
        html body td.RS,
        html body td.RO {
            border: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            box-shadow: none !important;
            outline: none !important;
        }
        <?php } ?>

    </style>

    <?php if (!empty($lbf_vcenter_fields)) { ?>
    <style id="lbf-nuke-vcenter">
        .lbf-vcenter-fields table.lbfdata > tbody > tr,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata > tbody > tr {
            display: flex !important;
            align-items: center !important;
        }
        .lbf-vcenter-fields table.lbfdata > tbody > tr > td,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata > tbody > tr > td {
            vertical-align: middle !important;
            display: flex !important;
            align-items: center !important;
        }
        .lbf-vcenter-fields table.lbfdata td table td,
        form.lbf-form[data-lbf-vcenter='1'] table.lbfdata td table td {
            display: table-cell !important;
            vertical-align: middle !important;
        }
    </style>
    <?php } ?>
    <?php if (!empty($lbf_suppress_row_borders)) { ?>
    <style id="lbf-nuke-row-borders">
        .table > tbody > tr > td,
        .table > tbody > tr > th,
        table.table > tbody > tr > td,
        table.lbfdata > tbody > tr > td,
        table.lbfdata > tbody > tr > th,
        table.lbfdata-no-row-borders > tbody > tr > td,
        table.lbfdata-no-row-borders > tbody > tr > th,
        form.lbf-form table > tbody > tr > td,
        form.lbf-form table > tbody > tr > th,
        form.lbf-form .table > tbody > tr > td,
        .section table > tbody > tr > td,
        .table-bordered > tbody > tr > td {
            border: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
        }
    </style>
    <?php } ?>

    <?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

    <!-- LiterallyCanvas support -->
    <?php echo lbf_canvas_head(); ?>

    <script language="JavaScript">

        // Support for beforeunload handler.
        var somethingChanged = false;

        function verifyCancel() {
            if (somethingChanged) {
                if (!confirm('<?php echo xl('You have unsaved changes. Do you really want to close this form?'); ?>')) {
                    return false;
                }
            }
            somethingChanged = false;
            parent.closeTab(window.name, false);
        }

        $(document).ready(function () {

            // Ward Dietary multi-card layout — Ward Dietary, Patient Transfer, Referral.
            (function () {
                var phpFlag = <?php echo !empty($lbf_ward_style) ? 'true' : 'false'; ?>;
                var SECTION_TITLE_RE = /^(WARD\s*INFORMATION|PATIENT\s*DIETARY\s*LIST|TRANSFER\s*INFORMATION|TRANSFER\s*DETAILS|CLINICAL\s*STATUS|CLINICAL\s*INFORMATION|CLINICAL\s*DETAILS|CLINICAL\s*SUMMARY|CLINICAL\s*NOTES|CLINICAL\s*HISTORY|PATIENT\s*INFORMATION|PATIENT\s*DETAILS|PATIENT\s*CONDITION|REFERRAL\s*INFORMATION|REFERRAL\s*DETAILS|REFERRAL\s*SUMMARY|REFERRAL\s*SOURCE|REFERRAL\s*TYPE|REFERRAL\s*REASON|REASON\s*FOR\s*REFERRAL|REFERRING\s*PROVIDER|REFERRING\s*FACILITY|REFERRING\s*DETAILS|REFERRED\s*BY|REFERRED\s*TO|RECEIVING\s*PROVIDER|RECEIVING\s*FACILITY|RECEIVING\s*DEPARTMENT|SPECIALIST\s*DETAILS|PROVIDER\s*INFORMATION|PROVIDER\s*DETAILS|COUNTER[\s-]*REFERRAL|FOLLOW[\s-]*UP(\s*INFORMATION|\s*DETAILS)?|ASSESSMENT|DIAGNOSIS|PROVISIONAL\s*DIAGNOSIS|TREATMENT\s*SUMMARY|MEDICATIONS|INVESTIGATIONS|VITALS|RECOMMENDATIONS|APPOINTMENT\s*DETAILS|ADDITIONAL\s*INFORMATION|ADDITIONAL\s*NOTES|ATTACHMENTS(\s*(&|AND)\s*DOCUMENTS)?|DOCUMENTS|AUTHORIZATION|SIGNATURES)$/i;

                function looksLikeWardStyle() {
                    if (phpFlag) { return true; }
                    var href = String(window.location.href || '');
                    if (/[?&]formname=[^&#]*(ward_dietary|dietary_requirements|patient_transfer|referral|lbtref)/i.test(href)) {
                        return true;
                    }
                    if (/ward_dietary|dietary_requirements|patient_transfer|lt_referral|patient_referral|lbtref/i.test(href)) {
                        return true;
                    }
                    var h3 = document.querySelector('.page-header h3, .lbf-top-title h3');
                    if (h3) {
                        var h3Txt = (h3.innerText || h3.textContent || '').replace(/\s+/g, ' ').trim();
                        var titlePart = h3Txt.split(/\s+for\s+/i)[0] || h3Txt;
                        if (/ward\s*dietary|dietary\s*requirements|patient\s*transfer|\breferral\b/i.test(titlePart)) {
                            return true;
                        }
                    }
                    var txt = (document.body && document.body.innerText) ? document.body.innerText : '';
                    return /WARD\s*DIETARY|DIETARY\s*REQUIREMENTS|PATIENT\s*TRANSFER|TRANSFER\s*INFORMATION|CLINICAL\s*STATUS|REFERRAL\s*INFORMATION|REFERRAL\s*DETAILS|COUNTER[\s-]*REFERRAL/i.test(txt);
                }

                function ensureWardHtmlBg() {
                    if (document.documentElement) {
                        document.documentElement.style.setProperty('background-color', '#e9ecef', 'important');
                        document.documentElement.style.setProperty('margin', '0', 'important');
                        document.documentElement.style.setProperty('padding', '0', 'important');
                    }
                }

                function titleText(el) {
                    if (!el) { return ''; }
                    var t = (el.innerText || el.textContent || '').replace(/\u00a0/g, ' ').replace(/\s+/g, ' ').trim();
                    // Strip leading checkbox artifacts
                    return t.replace(/^[\u2610\u2611]\s*/, '').trim();
                }

                function cleanHeadingLabel(t) {
                    return String(t || '').replace(/:+\s*$/, '').trim();
                }

                function isSectionTitleNode(el) {
                    if (!el || el.nodeType !== 1) { return false; }
                    if (el.closest && el.closest('.lbf-top-bar')) { return false; }
                    if (el.closest && el.closest('.lbf-actions-col')) { return false; }
                    var t = cleanHeadingLabel(titleText(el));
                    if (!t || t.length > 80) { return false; }
                    if (SECTION_TITLE_RE.test(t)) { return true; }
                    // Group title span with checkbox + bold
                    if (/SPAN/i.test(el.tagName) && el.querySelector && el.querySelector('input[type=checkbox]') && el.querySelector('b')) {
                        return true;
                    }
                    // Subtitle row cell (blue bold colspan)
                    if (/TD|TH/i.test(el.tagName)) {
                        var st = (el.getAttribute('style') || '') + ' ' + (el.className || '');
                        if (/0000ff|color:\s*blue|bold/i.test(st) && SECTION_TITLE_RE.test(t)) { return true; }
                        if (el.classList && el.classList.contains('bold') && SECTION_TITLE_RE.test(t)) { return true; }
                    }
                    return false;
                }

                function isTableSectionHeadingRow(tr) {
                    if (!tr || tr.nodeType !== 1) { return false; }
                    if (tr.querySelector && tr.querySelector('input, select, textarea, button, a, canvas')) {
                        return false;
                    }
                    var whole = cleanHeadingLabel(titleText(tr));
                    if (!whole || whole.length < 2 || whole.length > 80) { return false; }
                    var cells = Array.prototype.slice.call(tr.cells || []);
                    var nonEmpty = [];
                    for (var i = 0; i < cells.length; i++) {
                        if (titleText(cells[i])) { nonEmpty.push(cells[i]); }
                    }
                    if (nonEmpty.length !== 1) { return false; }
                    var cell = nonEmpty[0];
                    if (isSectionTitleNode(cell) || SECTION_TITLE_RE.test(whole)) { return true; }
                    // Do not split on instructional static sentences or questions.
                    if (whole.length > 65 || /[.?]$/.test(whole)) { return false; }
                    if (cell.querySelector && cell.querySelector('h1, h2, h3, h4, h5, h6')) {
                        return true;
                    }
                    var rowType = (tr.getAttribute('data-lbf-row-type') || '').toLowerCase();
                    var colspan = parseInt(cell.getAttribute('colspan') || '1', 10);
                    var spansRow = (colspan >= 2) || (cells.length === 1);
                    var st = (cell.getAttribute('style') || '') + ' ' + (cell.className || '');
                    // Descriptive group subtitle (grp_subtitle) stays inside its card unless it matches a known section title.
                    if (cell.classList && cell.classList.contains('lbf-group-subtitle')) {
                        return false;
                    }
                    var isBlueSubtitle = /0000ff|color:\s*blue/i.test(st);
                    var isBoldHeading = !!(
                        (cell.classList && cell.classList.contains('bold'))
                        || (cell.querySelector && cell.querySelector('b, strong'))
                        || /font-weight\s*:\s*(bold|[6-9]00)/i.test(st)
                    );
                    var isAllCapsHeading = (whole.length >= 4 && whole === whole.toUpperCase() && /[A-Z]/.test(whole));
                    if (rowType === 'sp' && (isBoldHeading || isBlueSubtitle || isAllCapsHeading)) {
                        return true;
                    }
                    if (rowType === 'static' && (isBoldHeading || isBlueSubtitle || isAllCapsHeading)) {
                        return true;
                    }
                    if (spansRow && (isBlueSubtitle || isBoldHeading)) {
                        return true;
                    }
                    return false;
                }

                function isEmptySpacerRow(tr) {
                    if (!tr || tr.nodeType !== 1) { return true; }
                    if (tr.querySelector && tr.querySelector('input, select, textarea, button, img, canvas')) {
                        return false;
                    }
                    return titleText(tr) === '';
                }

                function makeCard(titleNode, bodyNodes) {
                    var card = document.createElement('div');
                    card.className = 'lbf-group-card';
                    if (titleNode) {
                        var header = document.createElement('div');
                        header.className = 'lbf-group-card-header';
                        if (typeof titleNode === 'string') {
                            var hs = document.createElement('b');
                            hs.textContent = cleanHeadingLabel(titleNode);
                            header.appendChild(hs);
                        } else if (titleNode.tagName && /TR|TD|TH/i.test(titleNode.tagName)) {
                            var h = document.createElement('b');
                            h.textContent = cleanHeadingLabel(titleText(titleNode));
                            header.appendChild(h);
                        } else {
                            if (titleNode.classList && !titleNode.classList.contains('lbf-group-card-title')) {
                                titleNode.classList.add('lbf-group-card-title');
                            }
                            header.appendChild(titleNode);
                        }
                        card.appendChild(header);
                    }
                    var body = document.createElement('div');
                    body.className = 'lbf-group-card-body section';
                    body.style.display = 'block';
                    body.setAttribute('data-lbf-ward-opened', '1');
                    for (var i = 0; i < bodyNodes.length; i++) {
                        if (bodyNodes[i]) { body.appendChild(bodyNodes[i]); }
                    }
                    card.appendChild(body);
                    return card;
                }

                function ensureCardsStack(form) {
                    var stack = form.querySelector('.lbf-cards-stack');
                    if (stack) { return stack; }
                    stack = document.createElement('div');
                    stack.className = 'lbf-cards-stack';
                    var topBar = form.querySelector('.lbf-top-bar');
                    var row = form.querySelector('.container-responsive > .row') || form;
                    if (topBar && topBar.parentNode) {
                        if (topBar.nextSibling) {
                            topBar.parentNode.insertBefore(stack, topBar.nextSibling);
                        } else {
                            topBar.parentNode.appendChild(stack);
                        }
                    } else {
                        row.appendChild(stack);
                    }
                    return stack;
                }

                function splitTableBySectionTitles(table, existingHeaderTitle) {
                    var rows = Array.prototype.slice.call(table.rows || []);
                    var breaks = [];
                    for (var r = 0; r < rows.length; r++) {
                        if (isTableSectionHeadingRow(rows[r])) {
                            breaks.push(r);
                        }
                    }
                    if (breaks.length === 0) { return null; }
                    var leadRows = [];
                    for (var p = 0; p < breaks[0]; p++) {
                        if (!isEmptySpacerRow(rows[p])) {
                            leadRows.push(rows[p]);
                        }
                    }
                    // Split when there are 2+ headings, OR 1 heading with non-empty leading rows before it,
                    // OR 1 leading heading on row 0 inside an untitled card/table.
                    var shouldSplit = (breaks.length >= 2)
                        || (breaks.length === 1 && leadRows.length > 0)
                        || (breaks.length === 1 && leadRows.length === 0 && !existingHeaderTitle);
                    if (!shouldSplit) { return null; }

                    var cards = [];
                    if (leadRows.length > 0) {
                        var leadTable = table.cloneNode(false);
                        var leadTb = document.createElement('tbody');
                        leadTable.appendChild(leadTb);
                        leadTable.className = table.className;
                        for (var lp = 0; lp < leadRows.length; lp++) {
                            leadTb.appendChild(leadRows[lp]);
                        }
                        cards.push(makeCard(existingHeaderTitle || null, [leadTable]));
                    }
                    for (var b = 0; b < breaks.length; b++) {
                        var start = breaks[b];
                        var end = (b + 1 < breaks.length) ? breaks[b + 1] : rows.length;
                        var titleRow = rows[start];
                        var newTable = table.cloneNode(false);
                        var tb = document.createElement('tbody');
                        newTable.appendChild(tb);
                        newTable.className = table.className;
                        var firstContentSeen = false;
                        for (var i = start + 1; i < end; i++) {
                            if (!firstContentSeen && isEmptySpacerRow(rows[i])) {
                                continue;
                            }
                            firstContentSeen = true;
                            tb.appendChild(rows[i]);
                        }
                        cards.push(makeCard(titleRow, [newTable]));
                    }
                    return cards;
                }

                function wrapExistingGroupCards(form, stack) {
                    var existing = Array.prototype.slice.call(form.querySelectorAll('.lbf-group-card'));
                    for (var i = 0; i < existing.length; i++) {
                        if (existing[i].parentNode !== stack) {
                            stack.appendChild(existing[i]);
                        }
                    }
                    return existing.length;
                }

                function wrapSectionDivs(form, stack) {
                    var sections = Array.prototype.slice.call(form.querySelectorAll('div.section'));
                    var count = 0;
                    for (var i = 0; i < sections.length; i++) {
                        var sec = sections[i];
                        if (sec.closest && sec.closest('.lbf-group-card')) { continue; }
                        var prev = sec.previousSibling;
                        while (prev && prev.nodeType === 3 && !String(prev.textContent || '').trim()) {
                            prev = prev.previousSibling;
                        }
                        while (prev && prev.nodeType === 1 && /^(BR|HR)$/i.test(prev.tagName || '')) {
                            var dead = prev;
                            prev = prev.previousSibling;
                            if (dead.parentNode) { dead.parentNode.removeChild(dead); }
                            while (prev && prev.nodeType === 3 && !String(prev.textContent || '').trim()) {
                                prev = prev.previousSibling;
                            }
                        }
                        var titleEl = (prev && prev.nodeType === 1 && isSectionTitleNode(prev)) ? prev : null;
                        var card = makeCard(titleEl, [sec]);
                        sec.classList.add('lbf-group-card-body');
                        stack.appendChild(card);
                        count++;
                    }
                    return count;
                }

                function rebuildFromSectionTitles(form, stack) {
                    // Split any table.lbfdata that contains internal section headings,
                    // inserting replacement cards in-place to preserve document order.
                    var tables = Array.prototype.slice.call(form.querySelectorAll('table.lbfdata'));
                    var built = 0;
                    for (var t = 0; t < tables.length; t++) {
                        var table = tables[t];
                        if (!table.parentNode) { continue; }
                        var host = table.closest ? table.closest('.lbf-group-card') : null;
                        var existingHeaderText = '';
                        if (host) {
                            var existingHeader = host.querySelector('.lbf-group-card-header');
                            if (existingHeader) {
                                existingHeaderText = titleText(existingHeader);
                            }
                        }
                        var cards = splitTableBySectionTitles(table, existingHeaderText);
                        if (!cards) { continue; }
                        if (host && host.parentNode) {
                            for (var c = 0; c < cards.length; c++) {
                                host.parentNode.insertBefore(cards[c], host);
                                built++;
                            }
                            host.parentNode.removeChild(host);
                        } else {
                            for (var c2 = 0; c2 < cards.length; c2++) {
                                stack.appendChild(cards[c2]);
                                built++;
                            }
                            if (table.parentNode) {
                                table.parentNode.removeChild(table);
                            }
                        }
                    }
                    return built;
                }

                function ensureFallbackCard(form, stack) {
                    // Wrap any bare table.lbfdata outside cards into its own card.
                    var tables = Array.prototype.slice.call(form.querySelectorAll('table.lbfdata'));
                    for (var t = 0; t < tables.length; t++) {
                        var table = tables[t];
                        if (table.closest && table.closest('.lbf-group-card')) { continue; }
                        var titleNode = null;
                        if (table.rows && table.rows.length > 1 && isTableSectionHeadingRow(table.rows[0])) {
                            titleNode = table.rows[0];
                            table.deleteRow(0);
                            while (table.rows.length > 0 && isEmptySpacerRow(table.rows[0])) {
                                table.deleteRow(0);
                            }
                        }
                        stack.appendChild(makeCard(titleNode, [table]));
                    }
                }

                function pruneEmptyCards(stack) {
                    var cards = Array.prototype.slice.call(stack.querySelectorAll('.lbf-group-card'));
                    for (var i = 0; i < cards.length; i++) {
                        var body = cards[i].querySelector('.lbf-group-card-body');
                        if (!body) { continue; }
                        var hasControls = body.querySelector('input, select, textarea, button, img, canvas, a');
                        if (!hasControls && !titleText(body)) {
                            if (cards[i].parentNode) {
                                cards[i].parentNode.removeChild(cards[i]);
                            }
                        }
                    }
                }

                function normalizeTopBarTitle(topBar) {
                    var titleBox = topBar.querySelector('.lbf-top-title, .page-header');
                    if (!titleBox) { return; }
                    titleBox.classList.add('lbf-top-title');
                    // Wrap any loose Provider / Case text + select nodes into .lbf-top-meta for clean inline layout.
                    if (!titleBox.querySelector('.lbf-top-meta')) {
                        var h3 = titleBox.querySelector('h3');
                        var extras = [];
                        var node = h3 ? h3.nextSibling : null;
                        while (node) {
                            var next = node.nextSibling;
                            if (node.nodeType === 1 || (node.nodeType === 3 && String(node.textContent || '').replace(/\u00a0/g, ' ').trim() !== '')) {
                                extras.push(node);
                            } else if (node.nodeType === 3) {
                                titleBox.removeChild(node);
                            }
                            node = next;
                        }
                        if (extras.length > 0) {
                            var meta = document.createElement('span');
                            meta.className = 'lbf-top-meta';
                            for (var i = 0; i < extras.length; i++) {
                                if (extras[i].nodeType === 3) {
                                    extras[i].textContent = String(extras[i].textContent || '').replace(/\u00a0/g, ' ').trim();
                                }
                                meta.appendChild(extras[i]);
                            }
                            titleBox.appendChild(meta);
                        }
                    }
                }

                function moveActionsToTopBar(form) {
                    var topBar = form.querySelector('.lbf-top-bar') || document.querySelector('.lbf-top-bar');
                    if (!topBar) { return; }
                    normalizeTopBarTitle(topBar);
                    var actionsSlot = topBar.querySelector('.lbf-top-actions');
                    if (!actionsSlot) {
                        actionsSlot = document.createElement('div');
                        actionsSlot.className = 'lbf-top-actions';
                        actionsSlot.setAttribute('aria-label', 'Form actions');
                        topBar.appendChild(actionsSlot);
                    }
                    var actionsCol = form.querySelector('.lbf-actions-col') || document.querySelector('.lbf-actions-col');
                    if (!actionsCol) { return; }
                    var btnGroup = actionsCol.querySelector('.btn-group');
                    if (!btnGroup) { return; }
                    // Strip raw &nbsp; / whitespace text nodes inside .btn-group so flex gap is uniform.
                    var child = btnGroup.firstChild;
                    while (child) {
                        var nextChild = child.nextSibling;
                        if (child.nodeType === 3 && !String(child.textContent || '').replace(/\u00a0/g, ' ').trim()) {
                            btnGroup.removeChild(child);
                        }
                        child = nextChild;
                    }
                    if (!actionsSlot.contains(btnGroup)) {
                        actionsSlot.appendChild(btnGroup);
                    }
                    actionsCol.classList.add('lbf-actions-relocated');
                    // Hide stray <br> immediately preceding the relocated actions row.
                    var prev = actionsCol.previousSibling;
                    while (prev && prev.nodeType === 3 && !String(prev.textContent || '').trim()) {
                        prev = prev.previousSibling;
                    }
                    if (prev && prev.nodeType === 1 && /^BR$/i.test(prev.tagName || '')) {
                        prev.classList.add('lbf-actions-spacer-hidden');
                    }
                    var cancelBtns = actionsSlot.querySelectorAll('.btn-cancel');
                    for (var b = 0; b < cancelBtns.length; b++) {
                        if (/cancel/i.test(cancelBtns[b].textContent || '')) {
                            cancelBtns[b].textContent = cancelBtns[b].textContent.replace(/Cancel/ig, "Don't Save");
                        }
                    }
                }

                function forceSectionsOpen(form) {
                    var cards = form.querySelectorAll('.lbf-group-card');
                    for (var i = 0; i < cards.length; i++) {
                        var sec = cards[i].querySelector('.section, .lbf-group-card-body');
                        if (sec && !sec.getAttribute('data-lbf-ward-opened')) {
                            sec.style.display = 'block';
                            sec.setAttribute('data-lbf-ward-opened', '1');
                            var cb = cards[i].querySelector('.lbf-group-card-header input[type=checkbox]');
                            if (cb) { cb.checked = true; }
                        }
                    }
                }

                function styleWardCells(root) {
                    var scope = root || document;
                    var cells = scope.querySelectorAll('table.lbfdata td, table.lbfdata th');
                    for (var c = 0; c < cells.length; c++) {
                        cells[c].classList.add('lbf-ward-cell');
                        cells[c].setAttribute('valign', 'middle');
                        cells[c].style.setProperty('vertical-align', 'middle', 'important');
                    }
                }

                function applyWardStyle() {
                    if (!looksLikeWardStyle()) { return; }
                    ensureWardHtmlBg();
                    if (document.body) {
                        document.body.classList.add('lbf-ward-style');
                        document.body.setAttribute('data-lbf-ward-style', '1');
                    }
                    var forms = document.querySelectorAll('form.lbf-form, form');
                    for (var f = 0; f < forms.length; f++) {
                        var form = forms[f];
                        form.setAttribute('data-lbf-ward-style', '1');
                        if (form.classList) { form.classList.add('lbf-form'); }

                        // Promote page-header column to top bar if JS fallback activated ward style.
                        var headerCol = form.querySelector('.lbf-page-header-col');
                        if (headerCol && !headerCol.classList.contains('lbf-top-bar')) {
                            headerCol.classList.add('lbf-top-bar');
                        }

                        moveActionsToTopBar(form);

                        var stack = ensureCardsStack(form);
                        wrapExistingGroupCards(form, stack);
                        wrapSectionDivs(form, stack);
                        rebuildFromSectionTitles(form, stack);
                        ensureFallbackCard(form, stack);
                        pruneEmptyCards(stack);
                        forceSectionsOpen(form);
                    }
                    styleWardCells(document);
                }

                applyWardStyle();
                setTimeout(applyWardStyle, 0);
                setTimeout(applyWardStyle, 50);
                setTimeout(applyWardStyle, 200);
            })();

            // Meal Satisfaction nuclear flex vertical center (meal only).
            (function () {
                var phpFlag = <?php echo !empty($lbf_vcenter_fields) ? 'true' : 'false'; ?>;
                function looksLikeVcenterForm() {
                    if (phpFlag) { return true; }
                    var href = String(window.location.href || '');
                    if (/meal_satisfaction|meal_sat|meal.*satisfac/i.test(href)) { return true; }
                    var txt = (document.body && document.body.innerText) ? document.body.innerText : '';
                    if (/MEAL\s*SATISFACTION|Did you need assistance with eating|What portion of your meal/i.test(txt)) {
                        return true;
                    }
                    return false;
                }
                function ensureCss() {
                    if (document.getElementById('lbf-nuke-vcenter-js')) { return; }
                    var s = document.createElement('style');
                    s.id = 'lbf-nuke-vcenter-js';
                    s.type = 'text/css';
                    s.appendChild(document.createTextNode(
                        'form.lbf-form[data-lbf-vcenter="1"] table.lbfdata > tbody > tr,' +
                        'body.lbf-vcenter-fields table.lbfdata > tbody > tr{' +
                        'display:flex!important;align-items:center!important;}' +
                        'form.lbf-form[data-lbf-vcenter="1"] table.lbfdata > tbody > tr > td,' +
                        'body.lbf-vcenter-fields table.lbfdata > tbody > tr > td{' +
                        'vertical-align:middle!important;display:flex!important;align-items:center!important;flex-wrap:wrap;}' +
                        'form.lbf-form[data-lbf-vcenter="1"] table.lbfdata td table td,' +
                        'body.lbf-vcenter-fields table.lbfdata td table td{' +
                        'display:table-cell!important;vertical-align:middle!important;}'
                    ));
                    (document.head || document.documentElement).appendChild(s);
                }
                function nukeVcenter() {
                    if (!looksLikeVcenterForm()) { return; }
                    ensureCss();
                    if (document.body) {
                        document.body.classList.add('lbf-vcenter-fields');
                        document.body.setAttribute('data-lbf-vcenter', '1');
                    }
                    var forms = document.querySelectorAll('form.lbf-form, form');
                    for (var f = 0; f < forms.length; f++) {
                        forms[f].setAttribute('data-lbf-vcenter', '1');
                        if (forms[f].classList) { forms[f].classList.add('lbf-form'); }
                    }
                    var rows = document.querySelectorAll('table.lbfdata > tbody > tr, table.lbfdata tr');
                    for (var r = 0; r < rows.length; r++) {
                        var tr = rows[r];
                        tr.style.setProperty('display', 'flex', 'important');
                        tr.style.setProperty('align-items', 'center', 'important');
                        var cells = tr.children;
                        for (var c = 0; c < cells.length; c++) {
                            var td = cells[c];
                            if (!td || !/TD|TH/i.test(td.tagName || '')) { continue; }
                            td.classList.add('lbf-vcenter-cell');
                            td.setAttribute('valign', 'middle');
                            td.style.setProperty('vertical-align', 'middle', 'important');
                            td.style.setProperty('display', 'flex', 'important');
                            td.style.setProperty('align-items', 'center', 'important');
                            td.style.setProperty('flex-wrap', 'wrap', 'important');
                            var nested = td.querySelectorAll('table td');
                            for (var n = 0; n < nested.length; n++) {
                                nested[n].style.setProperty('display', 'table-cell', 'important');
                                nested[n].style.setProperty('vertical-align', 'middle', 'important');
                            }
                        }
                    }
                }
                nukeVcenter();
                setTimeout(nukeVcenter, 0);
                setTimeout(nukeVcenter, 100);
            })();

            // Shared LBF row-border kill (feeding regime / ward dietary / patient transfer / referral Add More / …).
            (function () {
                var phpFlag = <?php echo !empty($lbf_suppress_row_borders) ? 'true' : 'false'; ?>;
                function looksLikeNoRowBorders() {
                    if (phpFlag) { return true; }
                    if (document.body && document.body.classList && document.body.classList.contains('lbf-ward-style')) {
                        return true;
                    }
                    var href = String(window.location.href || '');
                    if (/[?&]formname=[^&#]*(prescribed_feeding|feeding_regime|ward_dietary|dietary_requirements|patient_transfer|referral|lbtref)/i.test(href)) { return true; }
                    if (/prescribed_feeding|feeding_regime|ward_dietary|dietary_requirements|patient_transfer|lt_referral|patient_referral|lbtref/i.test(href)) { return true; }
                    var txt = (document.body && document.body.innerText) ? document.body.innerText : '';
                    var hasRoute = /Route/i.test(txt);
                    var hasVolume = /Volume/i.test(txt);
                    var hasEnergy = /Energy/i.test(txt);
                    var hasProtein = /Protein/i.test(txt);
                    return hasRoute && hasVolume && (hasEnergy || hasProtein);
                }
                function nukeEl(el) {
                    if (!el || el.nodeType !== 1) { return; }
                    el.removeAttribute('border');
                    if (el.classList) {
                        el.classList.remove('RS', 'RO');
                    }
                    try {
                        el.style.setProperty('border', '0', 'important');
                        el.style.setProperty('border-top', '0', 'important');
                        el.style.setProperty('border-bottom', '0', 'important');
                        el.style.setProperty('box-shadow', 'none', 'important');
                    } catch (e) {
                        el.style.borderTop = '0';
                        el.style.borderBottom = '0';
                    }
                }
                function ensureNukeCss() {
                    if (document.getElementById('lbf-nuke-row-borders-js')) { return; }
                    var s = document.createElement('style');
                    s.id = 'lbf-nuke-row-borders-js';
                    s.type = 'text/css';
                    s.appendChild(document.createTextNode(
                        '.table > tbody > tr > td,.table > tbody > tr > th,' +
                        'table.lbfdata > tbody > tr > td,table.lbfdata > tbody > tr > th,' +
                        'form.lbf-form table > tbody > tr > td,form.lbf-form table > tbody > tr > th,' +
                        '.section table > tbody > tr > td,.section table > tbody > tr > th{' +
                        'border:0!important;border-top:0!important;border-bottom:0!important;}'
                    ));
                    (document.head || document.documentElement).appendChild(s);
                }
                function stripRowBorders(root) {
                    if (!looksLikeNoRowBorders()) { return; }
                    ensureNukeCss();
                    var scope = root || document;
                    var tables = [];
                    if (scope.nodeType === 1 && /TABLE/i.test(scope.tagName || '')) {
                        tables.push(scope);
                    }
                    var found = (scope.querySelectorAll)
                        ? scope.querySelectorAll('form.lbf-form table, .section table, table.lbfdata, table.lbfdata-no-row-borders, table.table')
                        : [];
                    for (var i = 0; i < found.length; i++) { tables.push(found[i]); }
                    for (var t = 0; t < tables.length; t++) {
                        nukeEl(tables[t]);
                        var cells = tables[t].querySelectorAll('tr, td, th');
                        for (var c = 0; c < cells.length; c++) { nukeEl(cells[c]); }
                    }
                }
                function afterClone(node) {
                    stripRowBorders(node || document);
                    var isWard = document.body && document.body.classList && document.body.classList.contains('lbf-ward-style');
                    var rows = document.querySelectorAll('form.lbf-form tr, .section table tr, table.lbfdata tr');
                    for (var r = 0; r < rows.length; r++) {
                        nukeEl(rows[r]);
                        var tds = rows[r].cells || rows[r].querySelectorAll('td, th');
                        for (var d = 0; d < tds.length; d++) {
                            nukeEl(tds[d]);
                            if (isWard) {
                                tds[d].classList.add('lbf-ward-cell');
                                tds[d].setAttribute('valign', 'middle');
                                tds[d].style.setProperty('vertical-align', 'middle', 'important');
                            }
                        }
                    }
                }
                stripRowBorders(document);
                afterClone(document);
                if (window.MutationObserver) {
                    var mo = new MutationObserver(function (mutations) {
                        for (var i = 0; i < mutations.length; i++) {
                            var nodes = mutations[i].addedNodes;
                            for (var j = 0; j < nodes.length; j++) {
                                if (nodes[j].nodeType === 1) {
                                    afterClone(nodes[j]);
                                }
                            }
                        }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                }
                $(document).on(
                    'click',
                    '.fa-plus-circle, .fa-plus, .add-more, .addmore, button, a, [onclick*="duplicate"], [onclick*="Duplicate"], [onclick*="Add"], [onclick*="add"]',
                    function () {
                        setTimeout(function () { afterClone(document); }, 0);
                        setTimeout(function () { afterClone(document); }, 50);
                        setTimeout(function () { afterClone(document); }, 200);
                    }
                );
                var _origDup = window.duplicateRow;
                Object.defineProperty(window, 'duplicateRow', {
                    configurable: true,
                    set: function (fn) {
                        _origDup = function () {
                            var ret = fn.apply(this, arguments);
                            setTimeout(function () { afterClone(document); }, 0);
                            return ret;
                        };
                    },
                    get: function () { return _origDup; }
                });
            })();

            if (window.tabbify) {
                tabbify();
            }
            if (window.checkSkipConditions) {
                checkSkipConditions();
            }

            $(".iframe_medium").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 950, 550, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
                    ],
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            // Support for beforeunload handler.
            $('.lbfdata input, .lbfdata select, .lbfdata textarea').change(function () {
                somethingChanged = true;
            });
            window.addEventListener("beforeunload", function (e) {
                if (somethingChanged && !top.timed_out) {
                    var msg = "<?php echo xls('You have unsaved changes.'); ?>";
                    e.returnValue = msg;     // Gecko, Trident, Chrome 34+
                    return msg;              // Gecko, WebKit, Chrome <34
                }
            });

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
            $('.datetimepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

        // Supports customizable forms.
        function divclick(cb, divid) {
            var divstyle = document.getElementById(divid).style;
            if (cb.checked) {
                divstyle.display = 'block';
            } else {
                divstyle.display = 'none';
            }
            return true;
        }

        // The ID of the input element to receive a found code.
        var current_sel_name = '';

        // This is for callback by the find-code popup.
        // Appends to or erases the current list of related codes.
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            <?php if (isset($fs)) { ?>
            // This is the case of selecting a code for the Fee Sheet:
            if (!current_sel_name) {
                if (code) {
                    $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                        '?codetype=' + encodeURIComponent(codetype) +
                        '&code=' + encodeURIComponent(code) +
                        '&selector=' + encodeURIComponent(selector) +
                        '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel ? f.form_fs_pricelevel.value : ""));
                }
                return '';
            }
            <?php } ?>
            // frc will be the input element containing the codes.
            // frcd, if set, will be the input element containing their descriptions.
            var frc = f[current_sel_name];
            var frcd;
            var matches = current_sel_name.match(/^(.*)__desc$/);
            if (matches) {
                frcd = frc;
                frc = f[matches[1]];
            }
            // For LBFs we will allow only one code in a field.
            var s = ''; // frc.value;
            var sd = ''; // frcd ? frcd.value : s;
            if (code) {
                if (s.length > 0) {
                    s += ';';
                    sd += ';';
                }
                s += codetype + ':' + code;
                sd += codedesc;
            } else {
                s = '';
                sd = '';
            }
            frc.value = s;
            if (frcd) frcd.value = sd;
            return '';
        }

        // This invokes the "dynamic" find-code popup.
        function sel_related(elem, codetype) {
            current_sel_name = elem ? elem.name : '';
            var url = '<?php echo $rootdir ?>/patient_file/encounter/find_code_dynamic.php';
            if (codetype) url += '?codetype=' + codetype;
            dlgopen(url, '_blank', 800, 500);
        }

        // Compute the length of a string without leading and trailing spaces.
        function trimlen(s) {
            var i = 0;
            var j = s.length - 1;
            for (; i <= j && s.charAt(i) == ' '; ++i) ;
            for (; i <= j && s.charAt(j) == ' '; --j) ;
            if (i > j) return 0;
            return j + 1 - i;
        }

        // This capitalizes the first letter of each word in the passed input
        // element.  It also strips out extraneous spaces.
        function capitalizeMe(elem) {
            var a = elem.value.split(' ');
            var s = '';
            for (var i = 0; i < a.length; ++i) {
                if (a[i].length > 0) {
                    if (s.length > 0) s += ' ';
                    s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
                }
            }
            elem.value = s;
        }

        // Validation logic for form submission.
        function validate(f) {
            <?php generate_layout_validation($formname); ?>
            // Validation for Fee Sheet stuff. Skipping this because CV decided (2015-11-03)
            // that these warning messages are not appropriate for layout based visit forms.
            //
            // if (window.jsLineItemValidation && !jsLineItemValidation(f)) return false;
            somethingChanged = false; // turn off "are you sure you want to leave"
            top.restoreSession();
            return true;
        }

        <?php
        if (isset($fs)) {
        // jsLineItemValidation() function for the fee sheet stuff.
            echo $fs->jsLineItemValidation('form_fs_bill', 'form_fs_prod');
        ?>

        // Add a service line item.
        function fs_append_service(code_type, code, desc, price) {
            var telem = document.getElementById('fs_services_table');
            var lino = telem.rows.length - 1;
            var trelem = telem.insertRow(telem.rows.length);
            trelem.innerHTML =
                "<td class='text'>" + code + "&nbsp;</td>" +
                "<td class='text'>" + desc + "&nbsp;</td>" +
                "<td class='text'>" +
                "<select name='form_fs_bill[" + lino + "][provid]'>" +
                "<?php echo addslashes($fs->genProviderOptionList('-- ' . xl('Default') . ' --')) ?>" +
                "</select>&nbsp;" +
                "</td>" +
                "<td class='text' align='right'>" + price + "&nbsp;</td>" +
                "<td class='text' align='right'>" +
                "<input type='checkbox' name='form_fs_bill[" + lino + "][del]' value='1' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code_type]' value='" + code_type + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code]'      value='" + code + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][price]'     value='" + price + "' />" +
                "</td>";
        }

        // Add a product line item.
        function fs_append_product(code_type, code, desc, price, warehouses) {
            var telem = document.getElementById('fs_products_table');
            if (!telem) {
                alert('<?php echo xla('A product was selected but there is no product section in this form.'); ?>');
                return;
            }
            var lino = telem.rows.length - 1;
            var trelem = telem.insertRow(telem.rows.length);
            trelem.innerHTML =
                "<td class='text'>" + desc + "&nbsp;</td>" +
                "<td class='text'>" +
                "<select name='form_fs_prod[" + lino + "][warehouse]'>" + warehouses + "</select>&nbsp;" +
                "</td>" +
                "<td class='text' align='right'>" +
                "<input type='text' name='form_fs_prod[" + lino + "][units]' size='3' value='1' />&nbsp;" +
                "</td>" +
                "<td class='text' align='right'>" + price + "&nbsp;</td>" +
                "<td class='text' align='right'>" +
                "<input type='checkbox' name='form_fs_prod[" + lino + "][del]'     value='1' />" +
                "<input type='hidden'   name='form_fs_prod[" + lino + "][drug_id]' value='" + code + "' />" +
                "<input type='hidden'   name='form_fs_prod[" + lino + "][price]'   value='" + price + "' />" +
                "</td>";
        }

        // Add a diagnosis line item.
        function fs_append_diag(code_type, code, desc) {
            var telem = document.getElementById('fs_diags_table');
            // Adding 1000 because form_fs_bill[] is shared with services and we want to avoid collisions.
            var lino = telem.rows.length - 1 + 1000;
            var trelem = telem.insertRow(telem.rows.length);
            trelem.innerHTML =
                "<td class='text'>" + code + "&nbsp;</td>" +
                "<td class='text'>" + desc + "&nbsp;</td>" +
                "<td class='text' align='right'>" +
                "<input type='checkbox' name='form_fs_bill[" + lino + "][del]' value='1' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code_type]' value='" + code_type + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][code]'      value='" + code + "' />" +
                "<input type='hidden' name='form_fs_bill[" + lino + "][price]'     value='" + 0 + "' />" +
                "</td>";
        }

        // Respond to clicking a checkbox for adding (or removing) a specific service.
        function fs_service_clicked(cb) {
            var f = cb.form;
            // The checkbox value is a JSON array containing the service's code type, code, description,
            // and price for each price level.
            var a = JSON.parse(cb.value);
            if (!cb.checked) {
                // The checkbox was UNchecked.
                // Find last row with a matching code_type and code and set its del flag.
                var telem = document.getElementById('fs_services_table');
                var lino = telem.rows.length - 2;
                for (; lino >= 0; --lino) {
                    var pfx = "form_fs_bill[" + lino + "]";
                    if (f[pfx + "[code_type]"].value == a[0] && f[pfx + "[code]"].value == a[1]) {
                        f[pfx + "[del]"].checked = true;
                        break;
                    }
                }
                return;
            }
            $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                '?codetype=' + encodeURIComponent(a[0]) +
                '&code=' + encodeURIComponent(a[1]) +
                '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel.value));
        }

        // Respond to clicking a checkbox for adding (or removing) a specific product.
        function fs_product_clicked(cb) {
            var f = cb.form;
            // The checkbox value is a JSON array containing the product's code type, code and selector.
            var a = JSON.parse(cb.value);
            if (!cb.checked) {
                // The checkbox was UNchecked.
                // Find last row with a matching product ID and set its del flag.
                var telem = document.getElementById('fs_products_table');
                var lino = telem.rows.length - 2;
                for (; lino >= 0; --lino) {
                    var pfx = "form_fs_prod[" + lino + "]";
                    if (f[pfx + "[code_type]"].value == a[0] && f[pfx + "[code]"].value == a[1]) {
                        f[pfx + "[del]"].checked = true;
                        break;
                    }
                }
                return;
            }
            $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                '?codetype=' + encodeURIComponent(a[0]) +
                '&code=' + encodeURIComponent(a[1]) +
                '&selector=' + encodeURIComponent(a[2]) +
                '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel.value));
        }

        // Respond to clicking a checkbox for adding (or removing) a specific diagnosis.
        function fs_diag_clicked(cb) {
            var f = cb.form;
            // The checkbox value is a JSON array containing the diagnosis's code type, code, description.
            var a = JSON.parse(cb.value);
            if (!cb.checked) {
                // The checkbox was UNchecked.
                // Find last row with a matching code_type and code and set its del flag.
                var telem = document.getElementById('fs_diags_table');
                var lino = telem.rows.length - 2 + 1000;
                for (; lino >= 0; --lino) {
                    var pfx = "form_fs_bill[" + lino + "]";
                    if (f[pfx + "[code_type]"].value == a[0] && f[pfx + "[code]"].value == a[1]) {
                        f[pfx + "[del]"].checked = true;
                        break;
                    }
                }
                return;
            }
            $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                '?codetype=' + encodeURIComponent(a[0]) +
                '&code=' + encodeURIComponent(a[1]) +
                '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel ? f.form_fs_pricelevel.value : ""));
        }

        // Respond to selecting a package of codes.
        function fs_package_selected(sel) {
            var f = sel.form;
            // The option value is an encoded string of code types and codes.
            if (sel.value) {
                $.getScript('<?php echo $GLOBALS['web_root'] ?>/library/ajax/code_attributes_ajax.php' +
                    '?list=' + encodeURIComponent(sel.value) +
                    '&pricelevel=' + encodeURIComponent(f.form_fs_pricelevel ? f.form_fs_pricelevel.value : ""));
            }
            sel.selectedIndex = 0;
        }

        // This is called back by code_attributes_ajax.php to complete the appending of a line item.
        function code_attributes_handler(codetype, code, desc, price, warehouses) {
            if (codetype == 'PROD') {
                fs_append_product(codetype, code, desc, price, warehouses);
            }
            else if (codetype == 'ICD9' || codetype == 'ICD10') {
                fs_append_diag(codetype, code, desc);
            }
            else {
                fs_append_service(codetype, code, desc, price);
            }
        }

        function warehouse_changed(sel) {
            if (!confirm('<?php echo xls('Do you really want to change Warehouse?'); ?>')) {
                // They clicked Cancel so reset selection to its default state.
                for (var i = 0; i < sel.options.length; ++i) {
                    sel.options[i].selected = sel.options[i].defaultSelected;
                }
            }
        }

        <?php } // end if (isset($fs))

        if (function_exists($formname . '_javascript')) {
            call_user_func($formname . '_javascript');
        }
        ?>

    </script>
</head>

<?php
// Form-specific body class so CSS can target one LBF without affecting others.
$lbf_body_class = 'body_top';
if ($formname !== '') {
    $lbf_body_class .= ' lbf-form-' . preg_replace('/[^A-Za-z0-9_-]/', '', $formname);
}
if (!empty($lbf_ward_style)) {
    $lbf_body_class .= ' lbf-ward-style';
}
if (!empty($lbf_vcenter_fields)) {
    $lbf_body_class .= ' lbf-vcenter-fields';
}
if (!empty($lbf_suppress_row_borders)) {
    $lbf_body_class .= ' lbf-no-row-borders';
}
?>
<body class="<?php echo attr($lbf_body_class); ?>"<?php
if (!empty($lbf_ward_style)) {
    echo " data-lbf-ward-style='1'";
}
if (!empty($lbf_vcenter_fields)) {
    echo " data-lbf-vcenter='1'";
}
if (!empty($lbf_suppress_row_borders)) {
    echo " data-lbf-nuke-row-borders='1'";
}
if ($from_issue_form) {
    echo " style='background-color:#ffffff'";
}
?>>
<?php
// Full-bleed card chrome: title left + action buttons right flush with edges
// (Ward Dietary Requirements / Patient Transfer / Referral reference look).
?>
<div class='container lbf-card-flush'>
    <?php
    echo "<form method='post' class='lbf-form' data-lbf-formname='" . attr($formname) . "' " .
        (!empty($lbf_ward_style) ? "data-lbf-ward-style='1' " : "") .
        (!empty($lbf_vcenter_fields) ? "data-lbf-vcenter='1' " : "") .
        (!empty($lbf_suppress_row_borders) ? "data-lbf-nuke-row-borders='1' " : "") .
        "action='$rootdir/forms/LBF/new.php?formname=" . attr($formname) . "&id=$formid&portalid=$portalid' " .
        "onsubmit='return validate(this)'>\n";

    $cmsportal_login = '';
    $portalres = false;


    if (!$from_trend_form) {
        $enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, p.cmsportal_login, " .
        "fe.date FROM " .
        "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
        "p.pid = ? AND f.pid = p.pid AND f.encounter = ? AND " .
        "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
        "fe.id = f.form_id LIMIT 1", array($pid, $visitid)); ?>

    <div class="container-responsive">
        <div class="row">
            <!--
              lbf-page-header-col: no Bootstrap column gutter so the title /
              page-header left edge matches group fields (not indented).
            -->
            <div class="col-xs-12 lbf-page-header-col<?php echo !empty($lbf_ward_style) ? ' lbf-top-bar' : ''; ?>">
                <div class="<?php echo !empty($lbf_ward_style) ? 'lbf-top-title page-header' : 'page-header'; ?>">
                    <h3>
                        <?php echo text($formtitle) . " " . xlt('for') . ' ';
                        echo text($enrow['fname']) . ' ' . text($enrow['mname']) . ' ' . text($enrow['lname']);
                        echo ' ' . xlt('on') . ' ' . text(oeFormatShortDate(substr($enrow['date'], 0, 10))); ?>
                    </h3>
                    <?php
                    $firow = sqlQuery(
                        "SELECT issue_id, provider_id FROM forms WHERE " .
                        "formdir = ? AND form_id = ? AND deleted = 0",
                        array($formname, $formid)
                    );
                    $form_issue_id = empty($firow['issue_id']) ? 0 : intval($firow['issue_id']);
                    $form_provider_id = empty($firow['provider_id']) ? 0 : intval($firow['provider_id']);

                    // Provider selector.
                    if (!empty($lbf_ward_style)) {
                        echo "<span class='lbf-top-meta'>";
                        echo "<span>" . xlt('Provider') . ":</span> ";
                    } else {
                        echo "&nbsp;&nbsp;";
                        echo xlt('Provider') . ": ";
                    }
                    // TBD: Refactor this function out of the FeeSheetHTML class as that is not the best place for it.
                    echo FeeSheetHtml::genProviderSelect('form_provider_id', '-- ' . xl("Please Select") . ' --', $form_provider_id);

                    // If appropriate build a drop-down selector of issues of this type for this patient.
                    // We skip this if in an issue form tab because removing and adding visit form tabs is
                    // beyond the current scope of that code.
                    if (!empty($LBF_ISSUE_TYPE) && !$from_issue_form) {
                        if (empty($lbf_ward_style)) {
                            echo "&nbsp;&nbsp;";
                        }
                        $query = "SELECT id, title, date, begdate FROM lists WHERE pid = ? AND type = ? " .
                            "ORDER BY COALESCE(begdate, date) DESC, id DESC";
                        $ires = sqlStatement($query, array($pid, $LBF_ISSUE_TYPE));
                        echo "<select name='form_issue_id'>\n";
                        echo " <option value='0'>-- " . xlt('Select Case') . " --</option>\n";
                        while ($irow = sqlFetchArray($ires)) {
                            $issueid = $irow['id'];
                            $issuedate = oeFormatShortDate(empty($irow['begdate']) ? $irow['date'] : $irow['begdate']);
                            echo " <option value='" . attr($issueid) . "'";
                            if ($issueid == $form_issue_id) {
                                echo " selected";
                            }
                            echo ">" . text("$issuedate " . $irow['title']) . "</option>\n";
                        }
                        echo "</select>\n";
                    }
                    if (!empty($lbf_ward_style)) {
                        echo "</span>";
                    }
                    ?>
                </div>
                <?php if (!empty($lbf_ward_style)) { ?>
                <div class="lbf-top-actions" aria-label="Form actions"></div>
                <?php } ?>
            </div>

            <?php $cmsportal_login = $enrow['cmsportal_login'];
    } // end not from trend form

            // If loading data from portal, get the data.
    if ($GLOBALS['gbl_portal_cms_enable'] && $portalid) {
        $portalres = cms_portal_call(array('action' => 'getpost', 'postid' => $portalid));
        if ($portalres['errmsg']) {
            die(text($portalres['errmsg']));
        }
    }
            ?>

            <!-- This is where a chart might display. -->
            <div id="chart"></div>

            <?php
            $shrow = getHistoryData($pid);

            // Determine if this layout uses edit option "I" anywhere.
            // If not we default to only the first group being initially open.
            $tmprow = sqlQuery("SELECT form_id FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 AND edit_options LIKE '%I%' " .
                "LIMIT 1", array($formname));
            $some_group_is_open = !empty($tmprow['form_id']);

            $fres = sqlStatement("SELECT * FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 " .
                "ORDER BY group_id, seq", array($formname));
            $cell_count = 0;
            $item_count = 0;
            $display_style = 'block';

            // This string is the active group levels. Each leading substring represents an instance of nesting.
            $group_levels = '';

            // This indicates if </table> will need to be written to end the fields in a group.
            $group_table_active = false;

            // This is an array keyed on forms.form_id for other occurrences of this
            // form type.  The maximum number of such other occurrences to display is
            // in list_options.option_value for this form's list item.  Values in this
            // array are work areas for building the ending HTML for each displayed row.
            //
            $historical_ids = array();

            // True if any data items in this form can be graphed.
            $form_is_graphable = false;

            $condition_str = '';

            while ($frow = sqlFetchArray($fres)) {
                $this_group = $frow['group_id'];
                $titlecols = $frow['titlecols'];
                $datacols = $frow['datacols'];
                $data_type = $frow['data_type'];
                $field_id = $frow['field_id'];
                $list_id = $frow['list_id'];
                $edit_options = $frow['edit_options'];
                $source = $frow['source'];

                $graphable = isOption($edit_options, 'G') !== false;
                if ($graphable) {
                    $form_is_graphable = true;
                }

                // Accumulate action conditions into a JSON expression for the browser side.
                accumActionConditions($field_id, $condition_str, $frow['conditions']);

                $currvalue = '';

                if (isOption($edit_options, 'H') !== false) {
                    // This data comes from static history
                    if (isset($shrow[$field_id])) {
                        $currvalue = $shrow[$field_id];
                    }
                } else {
                    if (!$formid && $portalres) {
                        // Copying CMS Portal form data into this field if appropriate.
                        $currvalue = cms_field_to_lbf($data_type, $field_id, $portalres['fields']);
                    }

                    if ($currvalue === '') {
                        $currvalue = lbf_current_value($frow, $formid, $is_lbf ? 0 : $encounter);
                    }

                    if ($currvalue === false) {
                        continue; // column does not exist, should not happen
                    }

                    // Handle "P" edit option to default to the previous value of a form field.
                    if (!$from_trend_form && empty($currvalue) && isOption($edit_options, 'P') !== false) {
                        if ($source == 'F' && !$formid) {
                            // Form attribute for new form, get value from most recent form instance.
                            // Form attributes of existing forms are expected to have existing values.
                            $tmp = sqlQuery(
                                "SELECT encounter, form_id FROM forms WHERE " .
                                "pid = ? AND formdir = ? AND deleted = 0 " .
                                "ORDER BY date DESC LIMIT 1",
                                array($pid, $formname)
                            );
                            if (!empty($tmp['encounter'])) {
                                $currvalue = lbf_current_value($frow, $tmp['form_id'], $tmp['encounter']);
                            }
                        } else if ($source == 'E') {
                            // Visit attribute, get most recent value as of this visit.
                            // Even if the form already exists for this visit it may have a readonly value that only
                            // exists in a previous visit and was created from a different form.
                            $tmp = sqlQuery(
                                "SELECT sa.field_value FROM form_encounter AS e1 " .
                                "JOIN form_encounter AS e2 ON " .
                                "e2.pid = e1.pid AND (e2.date < e1.date OR (e2.date = e1.date AND e2.encounter <= e1.encounter)) " .
                                "JOIN shared_attributes AS sa ON " .
                                "sa.pid = e2.pid AND sa.encounter = e2.encounter AND sa.field_id = ?" .
                                "WHERE e1.pid = ? AND e1.encounter = ? " .
                                "ORDER BY e2.date DESC, e2.encounter DESC LIMIT 1",
                                array($field_id, $pid, $visitid)
                            );
                            if (isset($tmp['field_value'])) {
                                $currvalue = $tmp['field_value'];
                            }
                        }
                    } // End "P" option logic.
                }

                $this_levels = $this_group;
                $i = 0;
                $mincount = min(strlen($this_levels), strlen($group_levels));
                while ($i < $mincount && $this_levels[$i] == $group_levels[$i]) {
                    ++$i;
                }
                // $i is now the number of initial matching levels.

                // If ending a group or starting a subgroup, terminate the current row and its table.
                if ($group_table_active && ($i != strlen($group_levels) || $i != strlen($this_levels))) {
                    end_row();
                    echo " </table>\n";
                    $group_table_active = false;
                }

                // Close any groups that we are done with.
                while (strlen($group_levels) > $i) {
                    $gname = $grparr[$group_levels]['grp_title'];
                    $group_levels = substr($group_levels, 0, -1); // remove last character
                    // No div for an empty group name.
                    if (strlen($gname)) {
                        echo "</div>\n"; // .section
                        if (!empty($lbf_ward_style)) {
                            echo "</div>\n"; // .lbf-group-card
                        }
                    }
                }

                // If there are any new groups, open them.
                while ($i < strlen($this_levels)) {
                    end_row();
                    if ($group_table_active) {
                        echo " </table>\n";
                        $group_table_active = false;
                    }
                    $group_levels .= $this_levels[$i++];
                    $gname = $grparr[substr($group_levels, 0, $i)]['grp_title'];
                    $subtitle = $grparr[substr($group_levels, 0, $i)]['grp_subtitle'];
                    // Compute a short unique identifier for this group.
                    $group_seq = 'lbf' . $group_levels;
                    $group_name = $gname;

                    if (!empty($lbf_ward_style)) {
                        // Ward-style multi-card layout shows all group cards open by default.
                        $display_style = 'block';
                    } else if ($some_group_is_open) {
                        // Must have edit option "I" in first item for its group to be initially open.
                        $display_style = isOption($edit_options, 'I') === false ? 'none' : 'block';
                    }

                    // If group name is blank, no checkbox or div.
                    if (strlen($gname)) {
                        if (!empty($lbf_ward_style)) {
                            // Ward Dietary card: one white card per LBF group.
                            echo "<div class='lbf-group-card' data-lbf-group='" . attr($group_seq) . "'>\n";
                            echo "<div class='lbf-group-card-header'>\n";
                            echo "<span class='bold lbf-group-card-title'><input type='checkbox' class='lbf-group-toggle' name='form_cb_" . attr($group_seq) . "' value='1' " .
                                "onclick='return divclick(this,\"div_" . attr(addslashes($group_seq)) . "\");'";
                            if ($display_style == 'block') {
                                echo " checked";
                            }
                            echo " /><b>" . text(xl_layout_label($group_name)) . "</b></span>\n";
                            echo "</div>\n";
                            echo "<div id='div_" . attr($group_seq) . "' class='section table-responsive lbf-group-card-body' style='display:" . attr($display_style) . ";'>\n";
                        } else {
                            echo "<br /><span class='bold'><input type='checkbox' name='form_cb_" . attr($group_seq) . "' value='1' " .
                                "onclick='return divclick(this,\"div_" . attr(addslashes($group_seq)) . "\");'";
                            if ($display_style == 'block') {
                                echo " checked";
                            }

                            echo " /><b>" . text(xl_layout_label($group_name)) . "</b></span>\n";
                            echo "<div id='div_" . attr($group_seq) . "' class='section table-responsive' style='display:" . attr($display_style) . ";'>\n";
                        }
                    }

                    $group_table_active = true;
                    // lbfdata-no-row-borders → CSS kills Bootstrap td border-top (#ddd).
                    $lbf_table_class = 'lbfdata';
                    $lbf_table_style = '';
                    if (!empty($lbf_suppress_row_borders)) {
                        $lbf_table_class .= ' lbfdata-no-row-borders';
                        $lbf_table_style = " style='border:0!important;border-collapse:collapse!important;'";
                    }
                    echo " <table border='0' cellspacing='0' cellpadding='0' class='" . attr($lbf_table_class) . "'" . $lbf_table_style . ">\n";

                    if ($subtitle) {
                        // There is a group subtitle so show it.
                        $sub_border = !empty($lbf_suppress_row_borders)
                            ? "border:0!important;border-top:0!important;border-bottom:0!important;"
                            : '';
                        $sub_color = !empty($lbf_ward_style) ? '' : 'color:#0000ff;';
                        echo "<tr" . ($sub_border ? " style='" . $sub_border . "'" : "") . "><td class='bold lbf-group-subtitle' style='" . $sub_color . $sub_border . "' colspan='" . attr($CPR) . "'>" . text($subtitle) . "</td></tr>\n";
                        echo "<tr" . ($sub_border ? " style='" . $sub_border . "'" : "") . "><td class='bold' style='height:4pt;" . $sub_border . "' colspan='" . attr($CPR) . "'></td></tr>\n";
                    }

                    $display_style = !empty($lbf_ward_style) ? 'block' : 'none';

                    // Initialize historical data array and write date headers.
                    $historical_ids = array();
                    if ($formhistory > 0) {
                        echo " <tr>";
                        echo "<td colspan='" . attr($CPR) . "' align='right' class='bold'>";
                        if (empty($is_lbf)) {
                            // Including actual date per IPPF request 2012-08-23.
                            echo text(oeFormatShortDate(substr($enrow['date'], 0, 10)));
                            echo ' (' . htmlspecialchars(xl('Current')) . ')';
                        }

                        echo "&nbsp;</td>\n";
                        $hres = sqlStatement(
                            "SELECT f.form_id, fe.date " .
                            "FROM forms AS f, form_encounter AS fe WHERE " .
                            "f.pid = ? AND f.formdir = ? AND " .
                            "f.form_id != ? AND f.deleted = 0 AND " .
                            "fe.pid = f.pid AND fe.encounter = f.encounter " .
                            "ORDER BY fe.date DESC, f.encounter DESC, f.date DESC " .
                            "LIMIT ?",
                            array($pid, $formname, $formid, $formhistory)
                        );
                        // For some readings like vitals there may be multiple forms per encounter.
                        // We sort these sensibly, however only the encounter date is shown here;
                        // at some point we may wish to show also the data entry date/time.
                        while ($hrow = sqlFetchArray($hres)) {
                            echo "<td colspan='" . attr($CPR) . "' align='right' class='bold'>&nbsp;" .
                                text(oeFormatShortDate(substr($hrow['date'], 0, 10))) . "</td>\n";
                            $historical_ids[$hrow['form_id']] = '';
                        }

                        echo " </tr>";
                    }
                }

// Inline style that beats Bootstrap border-top:1px solid #ddd when suppressing.
                $lbf_cell_border_style = !empty($lbf_suppress_row_borders)
                    ? "border:0!important;border-top:0!important;border-bottom:0!important;"
                    : '';
                // Ward-style / meal vcenter use middle; meal also gets nuclear flex styles.
                $lbf_td_valign = (!empty($lbf_vcenter_fields) || !empty($lbf_ward_style)) ? 'middle' : 'top';
                if (!empty($lbf_vcenter_fields)) {
                    // Meal satisfaction only — flex center (single field-per-row layouts).
                    $lbf_td_vcenter_style = "vertical-align:middle!important;display:flex;align-items:center;flex-wrap:wrap;";
                    $lbf_td_vcenter_class = ' lbf-vcenter-cell';
                } else if (!empty($lbf_ward_style)) {
                    // Ward dietary reference: mild valign only (keep table cells).
                    $lbf_td_vcenter_style = "vertical-align:middle!important;";
                    $lbf_td_vcenter_class = ' lbf-ward-cell';
                } else {
                    $lbf_td_vcenter_style = '';
                    $lbf_td_vcenter_class = '';
                }

// Handle starting of a new row.
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
                    end_row();
                    $lbf_row_type_attr = '';
                    if (isOption($edit_options, 'SP')) {
                        $lbf_row_type_attr = " data-lbf-row-type='sp'";
                    } else if ($data_type == 31) {
                        $lbf_row_type_attr = " data-lbf-row-type='static'";
                    }
                    // Skip RS/RO border classes when this form suppresses row separators.
                    if (!$lbf_suppress_row_borders && isOption($edit_options, 'RS')) {
                        echo " <tr class='RS'" . $lbf_row_type_attr . ">";
                    } else if (!$lbf_suppress_row_borders && isOption($edit_options, 'RO')) {
                        echo " <tr class='RO'" . $lbf_row_type_attr . ">";
                    } else if (!empty($lbf_suppress_row_borders)) {
                        echo " <tr style='" . $lbf_cell_border_style . "'" . $lbf_row_type_attr . ">";
                    } else {
                        echo " <tr" . $lbf_row_type_attr . ">";
                    }

                    // Clear historical data string.
                    foreach ($historical_ids as $key => $dummy) {
                        $historical_ids[$key] = '';
                    }
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

// First item is on the "left-border"
                $leftborder = true;

// Handle starting of a new label cell.
                if ($titlecols > 0) {
                    end_cell();
                    if (isOption($edit_options, 'SP')) {
                        $datacols = 0;
                        $titlecols = $CPR;
                        echo "<td valign='" . $lbf_td_valign . "' colspan='" . attr($titlecols) . "'";
                    } else {
                        echo "<td valign='" . $lbf_td_valign . "' colspan='" . attr($titlecols) . "' nowrap";
                    }
                    echo " class='";
                    echo ($frow['uor'] == 2) ? "required" : "bold";
                    if ($graphable) {
                        echo " graph";
                    }
                    echo $lbf_td_vcenter_class;

                    echo "'";

                    $label_style = $lbf_cell_border_style . $lbf_td_vcenter_style;
                    if ($cell_count > 0) {
                        $label_style .= "padding-left:10pt;";
                    }
                    if ($label_style !== '') {
                        echo " style='" . $label_style . "'";
                    }
                    // This ID is used by action conditions and also show_graph().
                    echo " id='label_id_" . attr($field_id) . "'";
                    echo ">";

                    foreach ($historical_ids as $key => $dummy) {
                        $historical_ids[$key] .= "<td valign='" . $lbf_td_valign . "' colspan='" . attr($titlecols) . "' class='text" . $lbf_td_vcenter_class . "' nowrap"
                            . (($lbf_cell_border_style . $lbf_td_vcenter_style) !== '' ? " style='" . $lbf_cell_border_style . $lbf_td_vcenter_style . "'" : "")
                            . ">";
                    }

                    $cell_count += $titlecols;
                }

                ++$item_count;

                echo "<b>";
                if ($frow['title']) {
                    $tmp = xl_layout_label($frow['title']);
                    echo text($tmp);
                    // Append colon only if label does not end with punctuation.
                    if (strpos('?!.,:-=', substr($tmp, -1, 1)) === false) {
                        echo ':';
                    }
                } else {
                    echo "&nbsp;";
                }
                echo "</b>";

// Note the labels are not repeated in the history columns.

// Handle starting of a new data cell.
                if ($datacols > 0) {
                    end_cell();
                    if (!$lbf_suppress_row_borders && isOption($edit_options, 'DS')) {
                        echo "<td valign='" . $lbf_td_valign . "' colspan='" . attr($datacols) . "' class='text RS" . $lbf_td_vcenter_class . "'";
                    } else if (!$lbf_suppress_row_borders && isOption($edit_options, 'DO')) {
                        echo "<td valign='" . $lbf_td_valign . "' colspan='" . attr($datacols) . "' class='text RO" . $lbf_td_vcenter_class . "'";
                    } else {
                        echo "<td valign='" . $lbf_td_valign . "' colspan='" . attr($datacols) . "' class='text" . $lbf_td_vcenter_class . "'";
                    }
                    // This ID is used by action conditions.
                    echo " id='value_id_" . attr($field_id) . "'";
                    $value_style = $lbf_cell_border_style . $lbf_td_vcenter_style;
                    if ($cell_count > 0) {
                        $value_style .= "padding-left:5pt;";
                    }
                    if ($value_style !== '') {
                        echo " style='" . $value_style . "'";
                    }

                    echo ">";

                    foreach ($historical_ids as $key => $dummy) {
                        $historical_ids[$key] .= "<td valign='" . $lbf_td_valign . "' align='right' colspan='" . attr($datacols) . "' class='text" . $lbf_td_vcenter_class . "'"
                            . (($lbf_cell_border_style . $lbf_td_vcenter_style) !== '' ? " style='" . $lbf_cell_border_style . $lbf_td_vcenter_style . "'" : "")
                            . ">";
                    }

                    $cell_count += $datacols;
                }

                ++$item_count;

// Skip current-value fields for the display-only case.
                if (!$from_trend_form) {
                    if ($frow['edit_options'] == 'H') {
                        echo generate_display_field($frow, $currvalue);
                    } else {
                        generate_form_field($frow, $currvalue);
                    }
                }

// Append to historical data of other dates for this item.
                foreach ($historical_ids as $key => $dummy) {
                    $value = lbf_current_value($frow, $key, 0);
                    $historical_ids[$key] .= generate_display_field($frow, $value);
                }
            }

            // Close all open groups.
            if ($group_table_active) {
                end_row();
                echo " </table>\n";
                $group_table_active = false;
            }
            while (strlen($group_levels)) {
                $gname = $grparr[$group_levels]['grp_title'];
                $group_levels = substr($group_levels, 0, -1); // remove last character
                // No div for an empty group name.
                if (strlen($gname)) {
                    echo "</div>\n"; // .section
                    if (!empty($lbf_ward_style)) {
                        echo "</div>\n"; // .lbf-group-card
                    }
                }
            }

            $display_style = 'none';

            if (isset($LBF_SERVICES_SECTION) || isset($LBF_DIAGS_SECTION)) {
                $fs->loadServiceItems();
            }

            if (isset($LBF_SERVICES_SECTION)) {
                // Create the checkbox and div for the Services Section.
                echo "<br /><span class='bold'><input type='checkbox' name='form_cb_fs_services' value='1' " .
                    "onclick='return divclick(this, \"div_fs_services\");'";
                if ($display_style == 'block') {
                    echo " checked";
                }
                echo " /><b>" . xlt('Services') . "</b></span>\n";
                echo "<div id='div_fs_services' class='section' style='display:" . attr($display_style) . ";'>\n";
                echo "<center>\n";
                $display_style = 'none';

                // If there are associated codes, generate a checkbox for each one.
                if ($LBF_SERVICES_SECTION) {
                    echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
                    $cols = 3;
                    $tdpct = (int)(100 / $cols);
                    $count = 0;
                    $relcodes = explode(';', $LBF_SERVICES_SECTION);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        $codes_esc = htmlspecialchars($codestring, ENT_QUOTES);
                        $cbval = $fs->genCodeSelectorValue($codestring);
                        if ($count % $cols == 0) {
                            if ($count) {
                                echo " </tr>\n";
                            }
                            echo " <tr>\n";
                        }
                        echo "  <td width='" . attr($tdpct) . "%'>";
                        echo "<input type='checkbox' id='form_fs_services[$codes_esc]' " .
                            "onclick='fs_service_clicked(this)' value='" . attr($cbval) . "'";
                        if ($fs->code_is_in_fee_sheet) {
                            echo " checked";
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $title = lookup_code_descriptions($codestring);
                        $title = empty($title) ? $code : xl_list_label($title);
                        echo " />" . htmlspecialchars($title, ENT_NOQUOTES);
                        echo "</td>\n";
                        ++$count;
                    }
                    if ($count) {
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                }

                // A row for Search, Add Package, Main Provider.
                $ctype = $GLOBALS['ippf_specific'] ? 'MA' : '';
                echo "<p class='bold'>";
                echo "<input type='button' value='" . xla('Search Services') . "' onclick='sel_related(null,\"$ctype\")' />&nbsp;&nbsp;\n";
                $fscres = sqlStatement("SELECT * FROM fee_sheet_options ORDER BY fs_category, fs_option");
                if (sqlNumRows($fscres)) {
                    $last_category = '';
                    echo "<select onchange='fs_package_selected(this)'>\n";
                    echo " <option value=''>" . xlt('Add Package') . "</option>\n";
                    while ($row = sqlFetchArray($fscres)) {
                        $fs_category = $row['fs_category'];
                        $fs_option = $row['fs_option'];
                        $fs_codes = $row['fs_codes'];
                        if ($fs_category !== $last_category) {
                            if ($last_category) {
                                echo " </optgroup>\n";
                            }
                            echo " <optgroup label='" . xla(substr($fs_category, 1)) . "'>\n";
                            $last_category = $fs_category;
                        }
                        echo " <option value='" . attr($fs_codes) . "'>" . xlt(substr($fs_option, 1)) . "</option>\n";
                    }
                    if ($last_category) {
                        echo " </optgroup>\n";
                    }
                    echo "</select>&nbsp;&nbsp;\n";
                }
                echo xlt('Main Provider') . ": ";
                echo $fs->genProviderSelect("form_fs_provid", ' ', $fs->provider_id);
                echo "\n";
                echo "</p>\n";

                // Generate a line for each service already in this FS.
                echo "<table cellpadding='0' cellspacing='2' id='fs_services_table'>\n";
                echo " <tr>\n";
                echo "  <td class='bold' colspan='2'>" . xlt('Services Provided') . "&nbsp;</td>\n";
                echo "  <td class='bold'>" . xlt('Provider') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Price') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Delete') . "</td>\n";
                echo " </tr>\n";
                foreach ($fs->serviceitems as $lino => $li) {
                    // Skip diagnoses; those would be in the Diagnoses section below.
                    if ($code_types[$li['codetype']]['diag']) {
                        continue;
                    }
                    echo " <tr>\n";
                    echo "  <td class='text'>" . text($li['code']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" .
                        $fs->genProviderSelect("form_fs_bill[$lino][provid]", '-- ' . xl("Default") . ' --', $li['provid']) .
                        "  &nbsp;</td>\n";
                    echo "  <td class='text' align='right'>" . oeFormatMoney($li['price']) . "&nbsp;</td>\n";
                    echo "  <td class='text' align='right'>\n" .
                        "   <input type='checkbox' name='form_fs_bill[$lino][del]' " .
                        "value='1'" . ($li['del'] ? " checked" : "") . " />\n";
                    foreach ($li['hidden'] as $hname => $hvalue) {
                        echo "   <input type='hidden' name='form_fs_bill[$lino][" . attr($hname) . "]' value='" . attr($hvalue) . "' />\n";
                    }
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                echo "</table>\n";
                echo "</center>\n";
                echo "</div>\n";
            } // End Services Section

            if (isset($LBF_PRODUCTS_SECTION)) {
                // Create the checkbox and div for the Products Section.
                echo "<br /><span class='bold'><input type='checkbox' name='form_cb_fs_products' value='1' " .
                    "onclick='return divclick(this, \"div_fs_products\");'";
                if ($display_style == 'block') {
                    echo " checked";
                }
                echo " /><b>" . xlt('Products') . "</b></span>\n";
                echo "<div id='div_fs_products' class='section' style='display:" . attr($display_style) . ";'>\n";
                echo "<center>\n";
                $display_style = 'none';

                // If there are associated codes, generate a checkbox for each one.
                if ($LBF_PRODUCTS_SECTION) {
                    echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
                    $cols = 3;
                    $tdpct = (int)(100 / $cols);
                    $count = 0;
                    $relcodes = explode(';', $LBF_PRODUCTS_SECTION);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        $codes_esc = htmlspecialchars($codestring, ENT_QUOTES);
                        $cbval = $fs->genCodeSelectorValue($codestring);
                        if ($count % $cols == 0) {
                            if ($count) {
                                echo " </tr>\n";
                            }
                            echo " <tr>\n";
                        }
                        echo "  <td width='" . attr($tdpct) . "%'>";
                        echo "<input type='checkbox' id='form_fs_products[$codes_esc]' " .
                            "onclick='fs_product_clicked(this)' value='" . attr($cbval) . "'";
                        if ($fs->code_is_in_fee_sheet) {
                            echo " checked";
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $crow = sqlQuery(
                            "SELECT name FROM drugs WHERE " .
                            "drug_id = ? ORDER BY drug_id LIMIT 1",
                            array($code)
                        );
                        $title = empty($crow['name']) ? $code : xl_list_label($crow['name']);
                        echo " />" . htmlspecialchars($title, ENT_NOQUOTES);
                        echo "</td>\n";
                        ++$count;
                    }
                    if ($count) {
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                }

                // A row for Search
                $ctype = $GLOBALS['ippf_specific'] ? 'MA' : '';
                echo "<p class='bold'>";
                echo "<input type='button' value='" . xla('Search Products') . "' onclick='sel_related(null,\"PROD\")' />&nbsp;&nbsp;";
                echo "</p>\n";

                // Generate a line for each product already in this FS.
                echo "<table cellpadding='0' cellspacing='2' id='fs_products_table'>\n";
                echo " <tr>\n";
                echo "  <td class='bold'>" . xlt('Products Provided') . "&nbsp;</td>\n";
                echo "  <td class='bold'>" . xlt('Warehouse') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Quantity') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Price') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Delete') . "</td>\n";
                echo " </tr>\n";
                $fs->loadProductItems();
                foreach ($fs->productitems as $lino => $li) {
                    echo " <tr>\n";
                    echo "  <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" .
                        $fs->genWarehouseSelect("form_fs_prod[$lino][warehouse]", '', $li['warehouse'], false, $li['hidden']['drug_id'], true) .
                        "  &nbsp;</td>\n";
                    echo "  <td class='text' align='right'>" .
                        "<input type='text' name='form_fs_prod[$lino][units]' size='3' value='" . $li['units'] . "' />" .
                        "&nbsp;</td>\n";
                    echo "  <td class='text' align='right'>" . oeFormatMoney($li['price']) . "&nbsp;</td>\n";
                    echo "  <td class='text' align='right'>\n" .
                        "   <input type='checkbox' name='form_fs_prod[$lino][del]' " .
                        "value='1'" . ($li['del'] ? " checked" : "") . " />\n";
                    foreach ($li['hidden'] as $hname => $hvalue) {
                        echo "   <input type='hidden' name='form_fs_prod[$lino][" . attr($hname) . "]' value='" . attr($hvalue) . "' />\n";
                    }
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                echo "</table>\n";
                echo "</center>\n";
                echo "</div>\n";
            } // End Products Section

            if (isset($LBF_DIAGS_SECTION)) {
                // Create the checkbox and div for the Diagnoses Section.
                echo "<br /><span class='bold'><input type='checkbox' name='form_cb_fs_diags' value='1' " .
                    "onclick='return divclick(this, \"div_fs_diags\");'";
                if ($display_style == 'block') {
                    echo " checked";
                }
                echo " /><b>" . xlt('Diagnoses') . "</b></span>\n";
                echo "<div id='div_fs_diags' class='section' style='display:" . attr($display_style) . ";'>\n";
                echo "<center>\n";
                $display_style = 'none';

                // If there are associated codes, generate a checkbox for each one.
                if ($LBF_DIAGS_SECTION) {
                    echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
                    $cols = 3;
                    $tdpct = (int)(100 / $cols);
                    $count = 0;
                    $relcodes = explode(';', $LBF_DIAGS_SECTION);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }
                        $codes_esc = htmlspecialchars($codestring, ENT_QUOTES);
                        $cbval = $fs->genCodeSelectorValue($codestring);
                        if ($count % $cols == 0) {
                            if ($count) {
                                echo " </tr>\n";
                            }
                            echo " <tr>\n";
                        }
                        echo "  <td width='" . attr($tdpct) . "%'>";
                        echo "<input type='checkbox' id='form_fs_diags[$codes_esc]' " .
                            "onclick='fs_diag_clicked(this)' value='" . attr($cbval) . "'";
                        if ($fs->code_is_in_fee_sheet) {
                            echo " checked";
                        }
                        list($codetype, $code) = explode(':', $codestring);
                        $title = lookup_code_descriptions($codestring);
                        $title = empty($title) ? $code : xl_list_label($title);
                        echo " />" . htmlspecialchars($title, ENT_NOQUOTES);
                        echo "</td>\n";
                        ++$count;
                    }
                    if ($count) {
                        echo " </tr>\n";
                    }
                    echo "</table>\n";
                }

                // A row for Search.
                $ctype = collect_codetypes('diagnosis', 'csv');
                echo "<p class='bold'>";
                echo "<input type='button' value='" . xla('Search Diagnoses') . "' onclick='sel_related(null,\"$ctype\")' />";
                echo "</p>\n";

                // Generate a line for each diagnosis already in this FS.
                echo "<table cellpadding='0' cellspacing='2' id='fs_diags_table'>\n";
                echo " <tr>\n";
                echo "  <td class='bold' colspan='2'>" . xlt('Diagnosis') . "&nbsp;</td>\n";
                echo "  <td class='bold' align='right'>" . xlt('Delete') . "</td>\n";
                echo " </tr>\n";
                foreach ($fs->serviceitems as $lino => $li) {
                    // Skip anything that is not a diagnosis; those are in the Services section above.
                    if (!$code_types[$li['codetype']]['diag']) {
                        continue;
                    }
                    echo " <tr>\n";
                    echo "  <td class='text'>" . text($li['code']) . "&nbsp;</td>\n";
                    echo "  <td class='text'>" . text($li['code_text']) . "&nbsp;</td>\n";
                    // The Diagnoses section shares the form_fs_bill array with the Services section.
                    echo "  <td class='text' align='right'>\n" .
                        "   <input type='checkbox' name='form_fs_bill[$lino][del]' " .
                        "value='1'" . ($li['del'] ? " checked" : "") . " />\n";
                    foreach ($li['hidden'] as $hname => $hvalue) {
                        echo "   <input type='hidden' name='form_fs_bill[$lino][" . attr($hname) . "]' value='" . attr($hvalue) . "' />\n";
                    }
                    echo "  </td>\n";
                    echo " </tr>\n";
                }
                echo "</table>\n";
                echo "</center>\n";
                echo "</div>\n";
            } // End Diagnoses Section

            ?>
            <br>

            <!--
              lbf-actions-col: no Bootstrap gutter; right-align so Save/Cancel/Back
              right edge matches the white card (Meal Satisfaction / Ward Dietary look).
            -->
            <div class="col-xs-12 lbf-actions-col">
                <div class="btn-group">

                    <?php
                    if (!$from_trend_form && !$from_lbf_edit) {
                        // Generate price level selector if we are doing services or products.
                        if (isset($LBF_SERVICES_SECTION) || isset($LBF_PRODUCTS_SECTION)) {
                            echo xlt('Price Level') . ": ";
                            echo $fs->generatePriceLevelSelector('form_fs_pricelevel');
                            echo "&nbsp;&nbsp;";
                        }
                        ?>
                        <button type="submit" class="btn btn-default btn-save" name="bn_save"
                                value="<?php echo xla('Save'); ?>">
                            <?php echo xlt('Save'); ?>
                        </button>

                        &nbsp;
                        <button type='submit' class="btn btn-link" name='bn_save_continue'
                                value='<?php echo xla('Save and Continue') ?>'>
                            <?php echo xlt('Save and Continue'); ?>
                        </button>
                        <?php
                        if (!$from_issue_form) {
                            ?>
                            &nbsp;
                            <button type='submit' class="btn btn-link" name='bn_save_print'
                                    value='<?php echo xla('Save and Print') ?>'>
                                <?php echo xlt('Save and Print'); ?>
                            </button>
                            <?php
                            if (function_exists($formname . '_additional_buttons')) {
                                // Allow the plug-in to insert more action buttons here.
                                call_user_func($formname . '_additional_buttons');
                            }

                            if ($form_is_graphable) {
                                ?>
                                <button type='button' class="btn btn-default btn-graph"
                                        onclick="top.restoreSession();location='../../patient_file/encounter/trend_form.php?formname=<?php echo attr($formname); ?>'">
                                    <?php echo xlt('Show Graph') ?>
                                </button>
                                &nbsp;
                                <?php
                            } // end form is graphable
                            ?>
                            <button type='button' class="btn btn-link btn-cancel" onclick="verifyCancel()">
                                <?php echo xlt('Cancel'); ?>
                            </button>
                            <?php
                        } // end not from issue form
                        ?>
                        <?php
                    } elseif (!$from_lbf_edit) { // $from_trend_form is true but lbf edit doesn't want button
                        ?>
                        <button type='button' class="btn btn-default btn-back" onclick='window.history.back();'>
                            <?php echo xlt('Back') ?>
                        </button>
                        <?php
                    } // end from trend form
                    ?>
                </div>
                <hr>
            </div>
            <?php if (!$from_trend_form) { // end row and container divs ?>
        </div>
    </div>
<?php } ?>

    <input type='hidden' name='from_issue_form' value='<?php echo text($from_issue_form); ?>'/>

    </form>

    <!-- include support for the list-add selectbox feature -->
    <?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>

    <script language="JavaScript">

        // Array of action conditions for the checkSkipConditions() function.
        var skipArray = [
            <?php echo $condition_str; ?>
        ];

        <?php echo $date_init; ?>
        <?php
        if (function_exists($formname . '_javascript_onload')) {
            call_user_func($formname . '_javascript_onload');
        }

        // New form and this patient has a portal login and we have not loaded portal data.
        // Check if there is portal data pending for this patient and form type.
        if (!$alertmsg && !$formid && $GLOBALS['gbl_portal_cms_enable'] && $cmsportal_login && !$portalid) {
            $portalres = cms_portal_call(array('action' => 'checkptform', 'form' => $formname, 'patient' => $cmsportal_login));
            if ($portalres['errmsg']) {
                die(text($portalres['errmsg'])); // TBD: Change to alertmsg
            }

            $portalid = $portalres['postid'];
            if ($portalid) {
                echo "if (confirm('" . xls('The portal has data for this patient and form. Load it now?') . "')) {\n";
                echo " top.restoreSession();\n";
                echo " document.location.href = 'load_form.php?formname=" . attr($formname) . "&portalid=$portalid';\n";
                echo "}\n";
            }
        }

        if ($alertmsg) {
            echo "alert('" . addslashes($alertmsg) . "');\n";
        }
        ?>
    </script>
</div>
</body>
</html>
