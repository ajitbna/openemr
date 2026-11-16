<?php
/**
 * Nutrition Screening & Meal Satisfaction Questionnaire - Report
 *
 * LICENSE: GNU GPL v2 or later. See LICENSE file.
 *
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$table = "form_lt_meal_satisfaction_questionnaire";

function msq_display($key, $value)
{
    if (empty($value)) {
        return;
    }
    $label = str_replace('_', ' ', ucwords($key, '_'));
    echo "<tr><td><b>" . text($label) . ":</b></td><td>" . text($value) . "</td></tr>\n";
}

function lt_meal_satisfaction_questionnaire_report($pid, $encounter, $cols, $id)
{
    global $table;
    $row = sqlQuery("SELECT * FROM `$table` WHERE id = ?", array($id));
    if (empty($row)) {
        return;
    }

    $fields = array(
        'appetite_change', 'food_intake_change', 'intake_change_type',
        'meal_satisfaction', 'meal_variety', 'meal_portion', 'meal_texture',
        'difficulty_chewing', 'difficulty_swallowing', 'nausea_vomiting', 'pain_eating',
        'unintended_weight_loss', 'weight_loss_amount',
    );

    echo "<table>\n";
    foreach ($fields as $f) {
        msq_display($f, $row[$f]);
    }
    echo "</table>\n";

    if (!empty($row['additional_notes'])) {
        echo "<p><b>" . xlt('Additional Notes') . ":</b> " . text($row['additional_notes']) . "</p>\n";
    }
}
