<?php
/**
 * Nutrition Screening & Meal Satisfaction Questionnaire - View/Edit existing record
 *
 * LICENSE: GNU GPL v2 or later. See LICENSE file.
 *
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "lt_meal_satisfaction_questionnaire";
$table     = "form_lt_meal_satisfaction_questionnaire";

formHeader("Form: $form_name");
$returnurl = 'encounter_top.php';

$id   = $_GET['id'];
$row  = sqlQuery("SELECT * FROM `$table` WHERE id = ?", array($id));

/** Helper: echo 'checked' when stored value matches option value */
function msq_checked($stored, $value)
{
    if ($stored === $value) {
        echo ' checked';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
/* Identical radio-layout rules as new.php – see that file for rationale */
.msq-form { max-width: 800px; margin: 0 auto; font-family: Arial, sans-serif; font-size: 14px; }
.msq-section { margin-bottom: 24px; padding: 16px; border: 1px solid #d0d0d0; border-radius: 4px; background: #fafafa; }
.msq-section h3 { margin: 0 0 14px 0; padding-bottom: 6px; border-bottom: 2px solid #4a7ba4; color: #2c5173; font-size: 15px; }
.msq-question { margin-bottom: 14px; }
.msq-question-label { display: block; font-weight: bold; margin-bottom: 6px; color: #333; }
.msq-radio-group { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; }
.msq-radio-group label { display: inline-flex; align-items: center; gap: 5px; cursor: pointer; white-space: nowrap; font-weight: normal; color: #444; }
.msq-radio-group label input[type="radio"] { margin: 0; flex-shrink: 0; cursor: pointer; }
.msq-subquestion { margin-left: 24px; margin-top: 10px; }
.msq-buttons { margin: 20px 0; }
.msq-buttons input[type="button"] { margin-right: 8px; padding: 6px 18px; }
</style>
</head>
<body class="body_top">

<form method="post"
      action="<?php echo $rootdir . '/forms/' . $form_name . '/save.php?mode=update&id=' . attr($id); ?>"
      name="my_form"
      id="my_form">

<div class="msq-form">

  <span class="title"><?php echo xlt('Nutrition Screening & Meal Satisfaction Questionnaire'); ?></span>
  <br><br>

  <div class="msq-buttons">
    <input type="button" class="save"    value="    <?php echo xla('Save'); ?>    ">
    <input type="button" class="dontsave" value="<?php echo xla("Don't Save"); ?>">
  </div>

  <!-- Section A -->
  <div class="msq-section">
    <h3><?php echo xlt('Section A: Appetite & Food Intake'); ?></h3>

    <div class="msq-question">
      <span class="msq-question-label"><?php echo xlt('1. Has your appetite changed in the past month?'); ?></span>
      <div class="msq-radio-group">
        <label><input type="radio" name="appetite_change" value="no_change"<?php msq_checked($row['appetite_change'], 'no_change'); ?>> <?php echo xlt('No change'); ?></label>
        <label><input type="radio" name="appetite_change" value="decreased"<?php msq_checked($row['appetite_change'], 'decreased'); ?>> <?php echo xlt('Decreased'); ?></label>
        <label><input type="radio" name="appetite_change" value="increased"<?php msq_checked($row['appetite_change'], 'increased'); ?>> <?php echo xlt('Increased'); ?></label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label"><?php echo xlt('2. Has your food intake changed recently?'); ?></span>
      <div class="msq-radio-group">
        <label><input type="radio" name="food_intake_change" value="no_change"<?php msq_checked($row['food_intake_change'], 'no_change'); ?>> <?php echo xlt('No change'); ?></label>
        <label><input type="radio" name="food_intake_change" value="more_than_usual"<?php msq_checked($row['food_intake_change'], 'more_than_usual'); ?>> <?php echo xlt('More than usual'); ?></label>
        <label><input type="radio" name="food_intake_change" value="less_than_usual"<?php msq_checked($row['food_intake_change'], 'less_than_usual'); ?>> <?php echo xlt('Less than usual'); ?></label>
      </div>
    </div>

    <div class="msq-question msq-subquestion">
      <span class="msq-question-label"><?php echo xlt('2a. If intake has changed, what best describes the change?'); ?></span>
      <div class="msq-radio-group">
        <label><input type="radio" name="intake_change_type" value="normal_food_less"<?php msq_checked($row['intake_change_type'], 'normal_food_less'); ?>> <?php echo xlt('Normal food, but less'); ?></label>
        <label><input type="radio" name="intake_change_type" value="small_amounts_solid"<?php msq_checked($row['intake_change_type'], 'small_amounts_solid'); ?>> <?php echo xlt('Small amounts of solid food'); ?></label>
        <label><input type="radio" name="intake_change_type" value="only_liquids"<?php msq_checked($row['intake_change_type'], 'only_liquids'); ?>> <?php echo xlt('Only liquids'); ?></label>
        <label><input type="radio" name="intake_change_type" value="very_little_anything"<?php msq_checked($row['intake_change_type'], 'very_little_anything'); ?>> <?php echo xlt('Very little of anything'); ?></label>
      </div>
    </div>
  </div>

  <!-- Section B -->
  <div class="msq-section">
    <h3><?php echo xlt('Section B: Meal Satisfaction'); ?></h3>

    <?php
    $satisfaction_opts = array(
        'very_satisfied'    => xlt('Very satisfied'),
        'satisfied'         => xlt('Satisfied'),
        'neutral'           => xlt('Neutral'),
        'dissatisfied'      => xlt('Dissatisfied'),
        'very_dissatisfied' => xlt('Very dissatisfied'),
    );
    $satisfaction_questions = array(
        'meal_satisfaction' => xlt('3. Overall, how satisfied are you with the meals you receive?'),
        'meal_variety'      => xlt('4. How satisfied are you with the variety of food offered?'),
        'meal_texture'      => xlt('6. How satisfied are you with the texture/consistency of food?'),
    );
    foreach ($satisfaction_questions as $fname => $qlabel) {
        ?>
        <div class="msq-question">
          <span class="msq-question-label"><?php echo $qlabel; ?></span>
          <div class="msq-radio-group">
            <?php foreach ($satisfaction_opts as $val => $label) { ?>
              <label>
                <input type="radio" name="<?php echo attr($fname); ?>" value="<?php echo attr($val); ?>"<?php msq_checked($row[$fname], $val); ?>>
                <?php echo $label; ?>
              </label>
            <?php } ?>
          </div>
        </div>
        <?php
    }
    ?>

    <div class="msq-question">
      <span class="msq-question-label"><?php echo xlt('5. How satisfied are you with the portion sizes?'); ?></span>
      <div class="msq-radio-group">
        <label><input type="radio" name="meal_portion" value="too_large"<?php msq_checked($row['meal_portion'], 'too_large'); ?>> <?php echo xlt('Too large'); ?></label>
        <label><input type="radio" name="meal_portion" value="just_right"<?php msq_checked($row['meal_portion'], 'just_right'); ?>> <?php echo xlt('Just right'); ?></label>
        <label><input type="radio" name="meal_portion" value="too_small"<?php msq_checked($row['meal_portion'], 'too_small'); ?>> <?php echo xlt('Too small'); ?></label>
      </div>
    </div>
  </div>

  <!-- Section C -->
  <div class="msq-section">
    <h3><?php echo xlt('Section C: Eating Difficulties'); ?></h3>
    <?php
    $yesno_opts = array(
        'no'        => xlt('No'),
        'sometimes' => xlt('Sometimes'),
        'yes'       => xlt('Yes'),
    );
    $difficulty_questions = array(
        'difficulty_chewing'    => xlt('7. Do you have difficulty chewing?'),
        'difficulty_swallowing' => xlt('8. Do you have difficulty swallowing?'),
        'nausea_vomiting'       => xlt('9. Do you experience nausea or vomiting?'),
        'pain_eating'           => xlt('10. Do you experience pain when eating?'),
    );
    foreach ($difficulty_questions as $fname => $qlabel) {
        ?>
        <div class="msq-question">
          <span class="msq-question-label"><?php echo $qlabel; ?></span>
          <div class="msq-radio-group">
            <?php foreach ($yesno_opts as $val => $label) { ?>
              <label>
                <input type="radio" name="<?php echo attr($fname); ?>" value="<?php echo attr($val); ?>"<?php msq_checked($row[$fname], $val); ?>>
                <?php echo $label; ?>
              </label>
            <?php } ?>
          </div>
        </div>
        <?php
    }
    ?>
  </div>

  <!-- Section D -->
  <div class="msq-section">
    <h3><?php echo xlt('Section D: Weight Changes'); ?></h3>

    <div class="msq-question">
      <span class="msq-question-label"><?php echo xlt('11. Have you lost weight without trying in the last 6 months?'); ?></span>
      <div class="msq-radio-group">
        <label><input type="radio" name="unintended_weight_loss" value="no"<?php msq_checked($row['unintended_weight_loss'], 'no'); ?>> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="unintended_weight_loss" value="unsure"<?php msq_checked($row['unintended_weight_loss'], 'unsure'); ?>> <?php echo xlt('Not sure'); ?></label>
        <label><input type="radio" name="unintended_weight_loss" value="yes"<?php msq_checked($row['unintended_weight_loss'], 'yes'); ?>> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="msq-question msq-subquestion">
      <span class="msq-question-label"><?php echo xlt('11a. If yes, approximately how much weight have you lost?'); ?></span>
      <div class="msq-radio-group">
        <label><input type="radio" name="weight_loss_amount" value="1_5_lbs"<?php msq_checked($row['weight_loss_amount'], '1_5_lbs'); ?>> <?php echo xlt('1–5 lbs'); ?></label>
        <label><input type="radio" name="weight_loss_amount" value="6_10_lbs"<?php msq_checked($row['weight_loss_amount'], '6_10_lbs'); ?>> <?php echo xlt('6–10 lbs'); ?></label>
        <label><input type="radio" name="weight_loss_amount" value="11_20_lbs"<?php msq_checked($row['weight_loss_amount'], '11_20_lbs'); ?>> <?php echo xlt('11–20 lbs'); ?></label>
        <label><input type="radio" name="weight_loss_amount" value="over_20_lbs"<?php msq_checked($row['weight_loss_amount'], 'over_20_lbs'); ?>> <?php echo xlt('More than 20 lbs'); ?></label>
      </div>
    </div>
  </div>

  <!-- Notes -->
  <div class="msq-section">
    <h3><?php echo xlt('Additional Notes'); ?></h3>
    <div class="msq-question">
      <textarea name="additional_notes" rows="4" cols="70"
                style="width:100%;box-sizing:border-box;"><?php echo text($row['additional_notes']); ?></textarea>
    </div>
  </div>

  <div class="msq-buttons">
    <input type="button" class="save"    value="    <?php echo xla('Save'); ?>    ">
    <input type="button" class="dontsave" value="<?php echo xla("Don't Save"); ?>">
  </div>

</div>
</form>

<script type="text/javascript"
        src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $(".save").click(function () { top.restoreSession(); $('#my_form').submit(); });
    $(".dontsave").click(function () { parent.closeTab(window.name, false); });
});
</script>
</body>
</html>
