<?php
/**
 * Nutrition Screening & Assessment Form - Save
 *
 * LICENSE: GNU GPL v2 or later.
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "lt_nutrition_screening_assessment";
$table     = "form_lt_nutrition_screening_assessment";

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
    'additional_notes',
);

$data = array();
foreach ($fields as $f) {
    $data[$f] = isset($_POST[$f]) ? $_POST[$f] : '';
}

if ($mode == "new") {
    $newid = sqlInsert(
        "INSERT INTO `$table` SET
            date        = NOW(),
            pid         = ?,
            user        = ?,
            groupname   = ?,
            authorized  = ?,
            activity    = 1,
            employment_status        = ?,
            living_arrangement       = ?,
            meal_assistance          = ?,
            appetite                 = ?,
            food_intake              = ?,
            skip_meals               = ?,
            unintended_weight_change = ?,
            weight_change_direction  = ?,
            weight_change_amount     = ?,
            difficulty_chewing       = ?,
            difficulty_swallowing    = ?,
            nausea_vomiting          = ?,
            diarrhea_constipation    = ?,
            special_diet             = ?,
            special_diet_type        = ?,
            food_allergies           = ?,
            food_allergies_detail    = ?,
            needs_assistance_eating  = ?,
            nutrition_risk           = ?,
            additional_notes         = ?",
        array(
            $pid,
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            $userauthorized,
            $data['employment_status'],
            $data['living_arrangement'],
            $data['meal_assistance'],
            $data['appetite'],
            $data['food_intake'],
            $data['skip_meals'],
            $data['unintended_weight_change'],
            $data['weight_change_direction'],
            $data['weight_change_amount'],
            $data['difficulty_chewing'],
            $data['difficulty_swallowing'],
            $data['nausea_vomiting'],
            $data['diarrhea_constipation'],
            $data['special_diet'],
            $data['special_diet_type'],
            $data['food_allergies'],
            $data['food_allergies_detail'],
            $data['needs_assistance_eating'],
            $data['nutrition_risk'],
            $data['additional_notes'],
        )
    );
    addForm(
        $encounter,
        "Nutrition Screening & Assessment Form",
        $newid,
        $form_name,
        $pid,
        $userauthorized
    );
} elseif ($mode == "update") {
    sqlStatement(
        "UPDATE `$table` SET
            date                     = NOW(),
            authorized               = ?,
            employment_status        = ?,
            living_arrangement       = ?,
            meal_assistance          = ?,
            appetite                 = ?,
            food_intake              = ?,
            skip_meals               = ?,
            unintended_weight_change = ?,
            weight_change_direction  = ?,
            weight_change_amount     = ?,
            difficulty_chewing       = ?,
            difficulty_swallowing    = ?,
            nausea_vomiting          = ?,
            diarrhea_constipation    = ?,
            special_diet             = ?,
            special_diet_type        = ?,
            food_allergies           = ?,
            food_allergies_detail    = ?,
            needs_assistance_eating  = ?,
            nutrition_risk           = ?,
            additional_notes         = ?
        WHERE id = ?",
        array(
            $userauthorized,
            $data['employment_status'],
            $data['living_arrangement'],
            $data['meal_assistance'],
            $data['appetite'],
            $data['food_intake'],
            $data['skip_meals'],
            $data['unintended_weight_change'],
            $data['weight_change_direction'],
            $data['weight_change_amount'],
            $data['difficulty_chewing'],
            $data['difficulty_swallowing'],
            $data['nausea_vomiting'],
            $data['diarrhea_constipation'],
            $data['special_diet'],
            $data['special_diet_type'],
            $data['food_allergies'],
            $data['food_allergies_detail'],
            $data['needs_assistance_eating'],
            $data['nutrition_risk'],
            $data['additional_notes'],
            $_GET['id'],
        )
    );
}

formHeader("Redirecting...");
formJump();
formFooter();
