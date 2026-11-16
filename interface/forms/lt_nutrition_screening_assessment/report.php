<?php
/**
 * Nutrition Screening & Assessment Form - Report
 *
 * LICENSE: GNU GPL v2 or later.
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$table = "form_lt_nutrition_screening_assessment";

function nsa_display($key, $value)
{
    if ($value === null || $value === '') {
        return;
    }
    $label = str_replace('_', ' ', ucwords($key, '_'));
    echo "<tr><td><b>" . text($label) . ":</b></td><td>" . text($value) . "</td></tr>\n";
}

function lt_nutrition_screening_assessment_report($pid, $encounter, $cols, $id)
{
    global $table;
    $row = sqlQuery("SELECT * FROM `$table` WHERE id = ?", array($id));
    if (empty($row)) {
        return;
    }

    $fields = array(
        'employment_status',
        'living_arrangement',
        'meal_assistance',
        'appetite',
        'food_intake',
        'skip_meals',
        'unintended_weight_change',
        'weight_change_direction',
        'weight_change_amount',
        'difficulty_chewing',
        'difficulty_swallowing',
        'nausea_vomiting',
        'diarrhea_constipation',
        'special_diet',
        'special_diet_type',
        'food_allergies',
        'food_allergies_detail',
        'needs_assistance_eating',
        'nutrition_risk',
    );

    echo "<table>\n";
    foreach ($fields as $f) {
        nsa_display($f, $row[$f]);
    }
    echo "</table>\n";

    if (!empty($row['additional_notes'])) {
        echo "<p><b>" . xlt('Additional Notes') . ":</b> "
            . text($row['additional_notes']) . "</p>\n";
    }
}
