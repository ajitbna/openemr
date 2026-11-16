<?php
/**
 * Nutrition Screening & Meal Satisfaction Questionnaire - Save
 *
 * LICENSE: GNU GPL v2 or later. See LICENSE file.
 *
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "lt_meal_satisfaction_questionnaire";
$table     = "form_lt_meal_satisfaction_questionnaire";

// Sanitised fields to store
$fields = array(
    'appetite_change',
    'food_intake_change',
    'intake_change_type',
    'meal_satisfaction',
    'meal_variety',
    'meal_portion',
    'meal_texture',
    'difficulty_chewing',
    'difficulty_swallowing',
    'nausea_vomiting',
    'pain_eating',
    'unintended_weight_loss',
    'weight_loss_amount',
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
            appetite_change          = ?,
            food_intake_change       = ?,
            intake_change_type       = ?,
            meal_satisfaction        = ?,
            meal_variety             = ?,
            meal_portion             = ?,
            meal_texture             = ?,
            difficulty_chewing       = ?,
            difficulty_swallowing    = ?,
            nausea_vomiting          = ?,
            pain_eating              = ?,
            unintended_weight_loss   = ?,
            weight_loss_amount       = ?,
            additional_notes         = ?",
        array(
            $pid,
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            $userauthorized,
            $data['appetite_change'],
            $data['food_intake_change'],
            $data['intake_change_type'],
            $data['meal_satisfaction'],
            $data['meal_variety'],
            $data['meal_portion'],
            $data['meal_texture'],
            $data['difficulty_chewing'],
            $data['difficulty_swallowing'],
            $data['nausea_vomiting'],
            $data['pain_eating'],
            $data['unintended_weight_loss'],
            $data['weight_loss_amount'],
            $data['additional_notes'],
        )
    );
    addForm($encounter, "Nutrition Screening & Meal Satisfaction Questionnaire",
            $newid, $form_name, $pid, $userauthorized);
} elseif ($mode == "update") {
    sqlStatement(
        "UPDATE `$table` SET
            date                     = NOW(),
            authorized               = ?,
            appetite_change          = ?,
            food_intake_change       = ?,
            intake_change_type       = ?,
            meal_satisfaction        = ?,
            meal_variety             = ?,
            meal_portion             = ?,
            meal_texture             = ?,
            difficulty_chewing       = ?,
            difficulty_swallowing    = ?,
            nausea_vomiting          = ?,
            pain_eating              = ?,
            unintended_weight_loss   = ?,
            weight_loss_amount       = ?,
            additional_notes         = ?
        WHERE id = ?",
        array(
            $userauthorized,
            $data['appetite_change'],
            $data['food_intake_change'],
            $data['intake_change_type'],
            $data['meal_satisfaction'],
            $data['meal_variety'],
            $data['meal_portion'],
            $data['meal_texture'],
            $data['difficulty_chewing'],
            $data['difficulty_swallowing'],
            $data['nausea_vomiting'],
            $data['pain_eating'],
            $data['unintended_weight_loss'],
            $data['weight_loss_amount'],
            $data['additional_notes'],
            $_GET['id'],
        )
    );
}

formHeader("Redirecting...");
formJump();
formFooter();
