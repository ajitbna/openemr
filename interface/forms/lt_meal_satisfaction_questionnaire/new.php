<?php
/**
 * Nutrition Screening & Meal Satisfaction Questionnaire - New Form
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

formHeader("Form: lt_meal_satisfaction_questionnaire");
$returnurl = 'encounter_top.php';
$form_name = "lt_meal_satisfaction_questionnaire";
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
/* ---------------------------------------------------------------
   Meal Satisfaction Questionnaire – radio button layout fix.

   The core problem: when <input type="radio"> and its text label
   are placed as bare inline nodes (or inside block-level elements
   without an explicit display model) they can collapse on top of
   each other.  The fix is to wrap every option in a <label> that
   uses inline-flex so the circle and text are always side-by-side
   with a small gap, and can never overlap regardless of the
   surrounding block context.
--------------------------------------------------------------- */

.msq-form {
    max-width: 800px;
    margin: 0 auto;
    font-family: Arial, sans-serif;
    font-size: 14px;
}

.msq-section {
    margin-bottom: 24px;
    padding: 16px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    background: #fafafa;
}

.msq-section h3 {
    margin: 0 0 14px 0;
    padding-bottom: 6px;
    border-bottom: 2px solid #4a7ba4;
    color: #2c5173;
    font-size: 15px;
}

/* Each question row */
.msq-question {
    margin-bottom: 14px;
}

.msq-question-label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
    color: #333;
}

/*
 * Radio option group – uses flex-wrap so options sit in a row and
 * wrap gracefully on narrow screens.  Each <label> inside is an
 * inline-flex container that keeps the <input> circle and its text
 * glued together with a 5 px gap; the 16 px right margin separates
 * options horizontally.  This fully prevents the overlap that occurs
 * when input + text share a plain block or inline context.
 */
.msq-radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
    align-items: center;
}

.msq-radio-group label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    white-space: nowrap;
    font-weight: normal;
    color: #444;
}

/* Vertically centre the native radio circle */
.msq-radio-group label input[type="radio"] {
    margin: 0;
    flex-shrink: 0;
    cursor: pointer;
}

/* Sub-question (conditional) */
.msq-subquestion {
    margin-left: 24px;
    margin-top: 10px;
}

.msq-buttons {
    margin: 20px 0;
}

.msq-buttons input[type="button"] {
    margin-right: 8px;
    padding: 6px 18px;
}
</style>
</head>

<body class="body_top">

<form method="post"
      action="<?php echo $rootdir . '/forms/' . $form_name . '/save.php?mode=new'; ?>"
      name="my_form"
      id="my_form">

<div class="msq-form">

  <span class="title"><?php echo xlt('Nutrition Screening & Meal Satisfaction Questionnaire'); ?></span>
  <br><br>

  <div class="msq-buttons">
    <input type="button" class="save"
           value="    <?php echo xla('Save'); ?>    ">
    <input type="button" class="dontsave"
           value="<?php echo xla("Don't Save"); ?>">
  </div>

  <!-- ============================================================
       SECTION A – Appetite & Intake
  ============================================================ -->
  <div class="msq-section">
    <h3><?php echo xlt('Section A: Appetite & Food Intake'); ?></h3>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('1. Has your appetite changed in the past month?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="appetite_change" value="no_change">
          <?php echo xlt('No change'); ?>
        </label>
        <label>
          <input type="radio" name="appetite_change" value="decreased">
          <?php echo xlt('Decreased'); ?>
        </label>
        <label>
          <input type="radio" name="appetite_change" value="increased">
          <?php echo xlt('Increased'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('2. Has your food intake changed recently?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="food_intake_change" value="no_change">
          <?php echo xlt('No change'); ?>
        </label>
        <label>
          <input type="radio" name="food_intake_change" value="more_than_usual">
          <?php echo xlt('More than usual'); ?>
        </label>
        <label>
          <input type="radio" name="food_intake_change" value="less_than_usual">
          <?php echo xlt('Less than usual'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question msq-subquestion">
      <span class="msq-question-label">
        <?php echo xlt('2a. If intake has changed, what best describes the change?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="intake_change_type" value="normal_food_less">
          <?php echo xlt('Normal food, but less'); ?>
        </label>
        <label>
          <input type="radio" name="intake_change_type" value="small_amounts_solid">
          <?php echo xlt('Small amounts of solid food'); ?>
        </label>
        <label>
          <input type="radio" name="intake_change_type" value="only_liquids">
          <?php echo xlt('Only liquids'); ?>
        </label>
        <label>
          <input type="radio" name="intake_change_type" value="very_little_anything">
          <?php echo xlt('Very little of anything'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ============================================================
       SECTION B – Meal Satisfaction
  ============================================================ -->
  <div class="msq-section">
    <h3><?php echo xlt('Section B: Meal Satisfaction'); ?></h3>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('3. Overall, how satisfied are you with the meals you receive?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="meal_satisfaction" value="very_satisfied">
          <?php echo xlt('Very satisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_satisfaction" value="satisfied">
          <?php echo xlt('Satisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_satisfaction" value="neutral">
          <?php echo xlt('Neutral'); ?>
        </label>
        <label>
          <input type="radio" name="meal_satisfaction" value="dissatisfied">
          <?php echo xlt('Dissatisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_satisfaction" value="very_dissatisfied">
          <?php echo xlt('Very dissatisfied'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('4. How satisfied are you with the variety of food offered?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="meal_variety" value="very_satisfied">
          <?php echo xlt('Very satisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_variety" value="satisfied">
          <?php echo xlt('Satisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_variety" value="neutral">
          <?php echo xlt('Neutral'); ?>
        </label>
        <label>
          <input type="radio" name="meal_variety" value="dissatisfied">
          <?php echo xlt('Dissatisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_variety" value="very_dissatisfied">
          <?php echo xlt('Very dissatisfied'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('5. How satisfied are you with the portion sizes?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="meal_portion" value="too_large">
          <?php echo xlt('Too large'); ?>
        </label>
        <label>
          <input type="radio" name="meal_portion" value="just_right">
          <?php echo xlt('Just right'); ?>
        </label>
        <label>
          <input type="radio" name="meal_portion" value="too_small">
          <?php echo xlt('Too small'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('6. How satisfied are you with the texture/consistency of food?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="meal_texture" value="very_satisfied">
          <?php echo xlt('Very satisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_texture" value="satisfied">
          <?php echo xlt('Satisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_texture" value="neutral">
          <?php echo xlt('Neutral'); ?>
        </label>
        <label>
          <input type="radio" name="meal_texture" value="dissatisfied">
          <?php echo xlt('Dissatisfied'); ?>
        </label>
        <label>
          <input type="radio" name="meal_texture" value="very_dissatisfied">
          <?php echo xlt('Very dissatisfied'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ============================================================
       SECTION C – Eating Difficulties
  ============================================================ -->
  <div class="msq-section">
    <h3><?php echo xlt('Section C: Eating Difficulties'); ?></h3>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('7. Do you have difficulty chewing?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="difficulty_chewing" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="difficulty_chewing" value="sometimes">
          <?php echo xlt('Sometimes'); ?>
        </label>
        <label>
          <input type="radio" name="difficulty_chewing" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('8. Do you have difficulty swallowing?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="difficulty_swallowing" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="difficulty_swallowing" value="sometimes">
          <?php echo xlt('Sometimes'); ?>
        </label>
        <label>
          <input type="radio" name="difficulty_swallowing" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('9. Do you experience nausea or vomiting?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="nausea_vomiting" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="nausea_vomiting" value="sometimes">
          <?php echo xlt('Sometimes'); ?>
        </label>
        <label>
          <input type="radio" name="nausea_vomiting" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('10. Do you experience pain when eating?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="pain_eating" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="pain_eating" value="sometimes">
          <?php echo xlt('Sometimes'); ?>
        </label>
        <label>
          <input type="radio" name="pain_eating" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ============================================================
       SECTION D – Weight
  ============================================================ -->
  <div class="msq-section">
    <h3><?php echo xlt('Section D: Weight Changes'); ?></h3>

    <div class="msq-question">
      <span class="msq-question-label">
        <?php echo xlt('11. Have you lost weight without trying in the last 6 months?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="unintended_weight_loss" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="unintended_weight_loss" value="unsure">
          <?php echo xlt('Not sure'); ?>
        </label>
        <label>
          <input type="radio" name="unintended_weight_loss" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
      </div>
    </div>

    <div class="msq-question msq-subquestion">
      <span class="msq-question-label">
        <?php echo xlt('11a. If yes, approximately how much weight have you lost?'); ?>
      </span>
      <div class="msq-radio-group">
        <label>
          <input type="radio" name="weight_loss_amount" value="1_5_lbs">
          <?php echo xlt('1–5 lbs'); ?>
        </label>
        <label>
          <input type="radio" name="weight_loss_amount" value="6_10_lbs">
          <?php echo xlt('6–10 lbs'); ?>
        </label>
        <label>
          <input type="radio" name="weight_loss_amount" value="11_20_lbs">
          <?php echo xlt('11–20 lbs'); ?>
        </label>
        <label>
          <input type="radio" name="weight_loss_amount" value="over_20_lbs">
          <?php echo xlt('More than 20 lbs'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ============================================================
       Additional Notes
  ============================================================ -->
  <div class="msq-section">
    <h3><?php echo xlt('Additional Notes'); ?></h3>
    <div class="msq-question">
      <textarea name="additional_notes"
                id="additional_notes"
                rows="4"
                cols="70"
                style="width:100%;box-sizing:border-box;"
                placeholder="<?php echo xla('Enter any additional observations or comments here...'); ?>"></textarea>
    </div>
  </div>

  <div class="msq-buttons">
    <input type="button" class="save"
           value="    <?php echo xla('Save'); ?>    ">
    <input type="button" class="dontsave"
           value="<?php echo xla("Don't Save"); ?>">
  </div>

</div><!-- .msq-form -->
</form>

<script type="text/javascript"
        src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $(".save").click(function () {
        top.restoreSession();
        $('#my_form').submit();
    });
    $(".dontsave").click(function () {
        parent.closeTab(window.name, false);
    });
});
</script>

</body>
</html>
