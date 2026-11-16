<?php
/**
 * Nutrition Screening & Assessment Form - New
 *
 * Radio layout: each option is a <label> with display:inline-flex so the
 * radio circle and its text never overlap (fixes Employment Status and
 * other radio groups stacking on top of their labels).
 *
 * LICENSE: GNU GPL v2 or later.
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

formHeader("Form: lt_nutrition_screening_assessment");
$form_name = "lt_nutrition_screening_assessment";
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
/* ---------------------------------------------------------------
   Nutrition Screening & Assessment – radio button layout fix.

   Overlap happens when bare <input type="radio"> and adjacent text
   share a block/inline context with no display model (common in
   older OpenEMR table-cell forms).  Fix: wrap every option in a
   <label> using inline-flex with a gap, and lay the group out with
   flex-wrap so options sit side-by-side and wrap cleanly.
--------------------------------------------------------------- */
.nsa-form {
    max-width: 860px;
    margin: 0 auto;
    font-family: Arial, sans-serif;
    font-size: 14px;
}
.nsa-section {
    margin-bottom: 24px;
    padding: 16px;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    background: #fafafa;
}
.nsa-section h3 {
    margin: 0 0 14px 0;
    padding-bottom: 6px;
    border-bottom: 2px solid #4a7ba4;
    color: #2c5173;
    font-size: 15px;
}
.nsa-question { margin-bottom: 14px; }
.nsa-question-label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
    color: #333;
}
.nsa-radio-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
    align-items: center;
}
.nsa-radio-group label {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    white-space: nowrap;
    font-weight: normal;
    color: #444;
}
.nsa-radio-group label input[type="radio"] {
    margin: 0;
    flex-shrink: 0;
    cursor: pointer;
}
.nsa-subquestion { margin-left: 24px; margin-top: 10px; }
.nsa-text-input {
    width: 100%;
    max-width: 480px;
    box-sizing: border-box;
    padding: 4px 8px;
}
.nsa-buttons { margin: 20px 0; }
.nsa-buttons input[type="button"] { margin-right: 8px; padding: 6px 18px; }
</style>
</head>
<body class="body_top">

<form method="post"
      action="<?php echo $rootdir . '/forms/' . $form_name . '/save.php?mode=new'; ?>"
      name="my_form" id="my_form">

<div class="nsa-form">

  <span class="title"><?php echo xlt('Nutrition Screening & Assessment Form'); ?></span>
  <br><br>

  <div class="nsa-buttons">
    <input type="button" class="save" value="    <?php echo xla('Save'); ?>    ">
    <input type="button" class="dontsave" value="<?php echo xla("Don't Save"); ?>">
  </div>

  <!-- ========== Social / Living ========== -->
  <div class="nsa-section">
    <h3><?php echo xlt('Social & Living Situation'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Employment Status'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="employment_status" value="employed">
          <?php echo xlt('Employed'); ?>
        </label>
        <label>
          <input type="radio" name="employment_status" value="unemployed">
          <?php echo xlt('Unemployed'); ?>
        </label>
        <label>
          <input type="radio" name="employment_status" value="retired">
          <?php echo xlt('Retired'); ?>
        </label>
        <label>
          <input type="radio" name="employment_status" value="self_employed">
          <?php echo xlt('Self-Employed'); ?>
        </label>
        <label>
          <input type="radio" name="employment_status" value="not_applicable">
          <?php echo xlt('Not Applicable'); ?>
        </label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Living Arrangement'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="living_arrangement" value="alone">
          <?php echo xlt('Lives alone'); ?>
        </label>
        <label>
          <input type="radio" name="living_arrangement" value="with_family">
          <?php echo xlt('With family/spouse'); ?>
        </label>
        <label>
          <input type="radio" name="living_arrangement" value="assisted_living">
          <?php echo xlt('Assisted living / facility'); ?>
        </label>
        <label>
          <input type="radio" name="living_arrangement" value="other">
          <?php echo xlt('Other'); ?>
        </label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Does anyone help prepare or serve meals?'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="meal_assistance" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
        <label>
          <input type="radio" name="meal_assistance" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="meal_assistance" value="sometimes">
          <?php echo xlt('Sometimes'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ========== Appetite & Intake ========== -->
  <div class="nsa-section">
    <h3><?php echo xlt('Appetite & Food Intake'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Current appetite'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="appetite" value="good">
          <?php echo xlt('Good'); ?>
        </label>
        <label>
          <input type="radio" name="appetite" value="fair">
          <?php echo xlt('Fair'); ?>
        </label>
        <label>
          <input type="radio" name="appetite" value="poor">
          <?php echo xlt('Poor'); ?>
        </label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Has food intake changed recently?'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="food_intake" value="no_change">
          <?php echo xlt('No change'); ?>
        </label>
        <label>
          <input type="radio" name="food_intake" value="increased">
          <?php echo xlt('Increased'); ?>
        </label>
        <label>
          <input type="radio" name="food_intake" value="decreased">
          <?php echo xlt('Decreased'); ?>
        </label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Do you skip meals?'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="skip_meals" value="never">
          <?php echo xlt('Never'); ?>
        </label>
        <label>
          <input type="radio" name="skip_meals" value="sometimes">
          <?php echo xlt('Sometimes'); ?>
        </label>
        <label>
          <input type="radio" name="skip_meals" value="often">
          <?php echo xlt('Often'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ========== Weight ========== -->
  <div class="nsa-section">
    <h3><?php echo xlt('Weight Changes'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Unintended weight change in the last 6 months?'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="unintended_weight_change" value="no">
          <?php echo xlt('No'); ?>
        </label>
        <label>
          <input type="radio" name="unintended_weight_change" value="unsure">
          <?php echo xlt('Not sure'); ?>
        </label>
        <label>
          <input type="radio" name="unintended_weight_change" value="yes">
          <?php echo xlt('Yes'); ?>
        </label>
      </div>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('If yes, direction of change'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="weight_change_direction" value="loss">
          <?php echo xlt('Weight loss'); ?>
        </label>
        <label>
          <input type="radio" name="weight_change_direction" value="gain">
          <?php echo xlt('Weight gain'); ?>
        </label>
      </div>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('Approximate amount'); ?></span>
      <div class="nsa-radio-group">
        <label>
          <input type="radio" name="weight_change_amount" value="1_5_lbs">
          <?php echo xlt('1–5 lbs'); ?>
        </label>
        <label>
          <input type="radio" name="weight_change_amount" value="6_10_lbs">
          <?php echo xlt('6–10 lbs'); ?>
        </label>
        <label>
          <input type="radio" name="weight_change_amount" value="11_20_lbs">
          <?php echo xlt('11–20 lbs'); ?>
        </label>
        <label>
          <input type="radio" name="weight_change_amount" value="over_20_lbs">
          <?php echo xlt('More than 20 lbs'); ?>
        </label>
      </div>
    </div>
  </div>

  <!-- ========== Clinical Risk ========== -->
  <div class="nsa-section">
    <h3><?php echo xlt('Clinical Nutrition Risk Factors'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Difficulty chewing'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="difficulty_chewing" value="no"> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="difficulty_chewing" value="sometimes"> <?php echo xlt('Sometimes'); ?></label>
        <label><input type="radio" name="difficulty_chewing" value="yes"> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Difficulty swallowing'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="difficulty_swallowing" value="no"> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="difficulty_swallowing" value="sometimes"> <?php echo xlt('Sometimes'); ?></label>
        <label><input type="radio" name="difficulty_swallowing" value="yes"> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Nausea or vomiting'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="nausea_vomiting" value="no"> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="nausea_vomiting" value="sometimes"> <?php echo xlt('Sometimes'); ?></label>
        <label><input type="radio" name="nausea_vomiting" value="yes"> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Diarrhea or constipation'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="diarrhea_constipation" value="no"> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="diarrhea_constipation" value="sometimes"> <?php echo xlt('Sometimes'); ?></label>
        <label><input type="radio" name="diarrhea_constipation" value="yes"> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('On a special / therapeutic diet?'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="special_diet" value="no"> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="special_diet" value="yes"> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('If yes, diet type'); ?></span>
      <input class="nsa-text-input" type="text" name="special_diet_type"
             placeholder="<?php echo xla('e.g. diabetic, low sodium, pureed…'); ?>">
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Known food allergies or intolerances?'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="food_allergies" value="no"> <?php echo xlt('No'); ?></label>
        <label><input type="radio" name="food_allergies" value="yes"> <?php echo xlt('Yes'); ?></label>
      </div>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('If yes, list allergies'); ?></span>
      <textarea name="food_allergies_detail" rows="2" cols="60"
                class="nsa-text-input"
                placeholder="<?php echo xla('List food allergies or intolerances…'); ?>"></textarea>
    </div>
  </div>

  <!-- ========== Functional / Risk ========== -->
  <div class="nsa-section">
    <h3><?php echo xlt('Functional Status & Risk'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Needs assistance with eating'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="needs_assistance_eating" value="independent"> <?php echo xlt('Independent'); ?></label>
        <label><input type="radio" name="needs_assistance_eating" value="some_help"> <?php echo xlt('Some help'); ?></label>
        <label><input type="radio" name="needs_assistance_eating" value="dependent"> <?php echo xlt('Dependent'); ?></label>
      </div>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Overall nutrition risk (clinician assessment)'); ?></span>
      <div class="nsa-radio-group">
        <label><input type="radio" name="nutrition_risk" value="low"> <?php echo xlt('Low risk'); ?></label>
        <label><input type="radio" name="nutrition_risk" value="moderate"> <?php echo xlt('Moderate risk'); ?></label>
        <label><input type="radio" name="nutrition_risk" value="high"> <?php echo xlt('High risk'); ?></label>
      </div>
    </div>
  </div>

  <!-- ========== Notes ========== -->
  <div class="nsa-section">
    <h3><?php echo xlt('Additional Notes'); ?></h3>
    <div class="nsa-question">
      <textarea name="additional_notes" rows="4" cols="70"
                style="width:100%;box-sizing:border-box;"
                placeholder="<?php echo xla('Enter any additional observations or comments…'); ?>"></textarea>
    </div>
  </div>

  <div class="nsa-buttons">
    <input type="button" class="save" value="    <?php echo xla('Save'); ?>    ">
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
