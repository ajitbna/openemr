<?php
/**
 * Nutrition Screening & Assessment Form - View/Edit
 *
 * Same radio CSS pattern as new.php (inline-flex labels, zeroed margins).
 *
 * LICENSE: GNU GPL v2 or later.
 * @package OpenEMR
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

$form_name = "lt_nutrition_screening_assessment";
$table     = "form_lt_nutrition_screening_assessment";

formHeader("Form: $form_name");

$id  = $_GET['id'];
$row = sqlQuery("SELECT * FROM `$table` WHERE id = ?", array($id));

function nsa_checked($stored, $value)
{
    if ($stored === $value) {
        echo ' checked';
    }
}

/**
 * Render a radio group with the anti-overlap layout.
 *
 * @param string $name    Field name
 * @param array  $options value => label
 * @param string $stored  Current DB value
 */
function nsa_radios($name, $options, $stored)
{
    echo '<div class="nsa-radio-group">' . "\n";
    foreach ($options as $val => $label) {
        echo '  <label>';
        echo '<input type="radio" name="' . attr($name) . '" value="' . attr($val) . '"';
        nsa_checked($stored, $val);
        echo '> ' . $label;
        echo "</label>\n";
    }
    echo "</div>\n";
}
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
.nsa-form { max-width: 860px; margin: 0 auto; font-family: Arial, sans-serif; font-size: 14px; }
.nsa-section { margin-bottom: 24px; padding: 16px; border: 1px solid #d0d0d0; border-radius: 4px; background: #fafafa; }
.nsa-section h3 { margin: 0 0 14px 0; padding-bottom: 6px; border-bottom: 2px solid #4a7ba4; color: #2c5173; font-size: 15px; }
.nsa-question { margin-bottom: 14px; }
.nsa-question-label { display: block; font-weight: bold; margin-bottom: 6px; color: #333; }
.nsa-radio-group { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: center; }
.nsa-radio-group label { display: inline-flex; align-items: center; gap: 5px; cursor: pointer; white-space: nowrap; font-weight: normal; color: #444; }
.nsa-radio-group label input[type="radio"] { margin: 0; flex-shrink: 0; cursor: pointer; }
.nsa-subquestion { margin-left: 24px; margin-top: 10px; }
.nsa-text-input { width: 100%; max-width: 480px; box-sizing: border-box; padding: 4px 8px; }
.nsa-buttons { margin: 20px 0; }
.nsa-buttons input[type="button"] { margin-right: 8px; padding: 6px 18px; }
</style>
</head>
<body class="body_top">

<form method="post"
      action="<?php echo $rootdir . '/forms/' . $form_name . '/save.php?mode=update&id=' . attr($id); ?>"
      name="my_form" id="my_form">

<div class="nsa-form">

  <span class="title"><?php echo xlt('Nutrition Screening & Assessment Form'); ?></span>
  <br><br>

  <div class="nsa-buttons">
    <input type="button" class="save" value="    <?php echo xla('Save'); ?>    ">
    <input type="button" class="dontsave" value="<?php echo xla("Don't Save"); ?>">
  </div>

  <div class="nsa-section">
    <h3><?php echo xlt('Social & Living Situation'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Employment Status'); ?></span>
      <?php nsa_radios('employment_status', array(
          'employed'        => xlt('Employed'),
          'unemployed'      => xlt('Unemployed'),
          'retired'         => xlt('Retired'),
          'self_employed'   => xlt('Self-Employed'),
          'not_applicable'  => xlt('Not Applicable'),
      ), $row['employment_status']); ?>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Living Arrangement'); ?></span>
      <?php nsa_radios('living_arrangement', array(
          'alone'           => xlt('Lives alone'),
          'with_family'     => xlt('With family/spouse'),
          'assisted_living' => xlt('Assisted living / facility'),
          'other'           => xlt('Other'),
      ), $row['living_arrangement']); ?>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Does anyone help prepare or serve meals?'); ?></span>
      <?php nsa_radios('meal_assistance', array(
          'yes'       => xlt('Yes'),
          'no'        => xlt('No'),
          'sometimes' => xlt('Sometimes'),
      ), $row['meal_assistance']); ?>
    </div>
  </div>

  <div class="nsa-section">
    <h3><?php echo xlt('Appetite & Food Intake'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Current appetite'); ?></span>
      <?php nsa_radios('appetite', array(
          'good' => xlt('Good'),
          'fair' => xlt('Fair'),
          'poor' => xlt('Poor'),
      ), $row['appetite']); ?>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Has food intake changed recently?'); ?></span>
      <?php nsa_radios('food_intake', array(
          'no_change' => xlt('No change'),
          'increased' => xlt('Increased'),
          'decreased' => xlt('Decreased'),
      ), $row['food_intake']); ?>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Do you skip meals?'); ?></span>
      <?php nsa_radios('skip_meals', array(
          'never'     => xlt('Never'),
          'sometimes' => xlt('Sometimes'),
          'often'     => xlt('Often'),
      ), $row['skip_meals']); ?>
    </div>
  </div>

  <div class="nsa-section">
    <h3><?php echo xlt('Weight Changes'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Unintended weight change in the last 6 months?'); ?></span>
      <?php nsa_radios('unintended_weight_change', array(
          'no'     => xlt('No'),
          'unsure' => xlt('Not sure'),
          'yes'    => xlt('Yes'),
      ), $row['unintended_weight_change']); ?>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('If yes, direction of change'); ?></span>
      <?php nsa_radios('weight_change_direction', array(
          'loss' => xlt('Weight loss'),
          'gain' => xlt('Weight gain'),
      ), $row['weight_change_direction']); ?>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('Approximate amount'); ?></span>
      <?php nsa_radios('weight_change_amount', array(
          '1_5_lbs'     => xlt('1–5 lbs'),
          '6_10_lbs'    => xlt('6–10 lbs'),
          '11_20_lbs'   => xlt('11–20 lbs'),
          'over_20_lbs' => xlt('More than 20 lbs'),
      ), $row['weight_change_amount']); ?>
    </div>
  </div>

  <div class="nsa-section">
    <h3><?php echo xlt('Clinical Nutrition Risk Factors'); ?></h3>

    <?php
    $yn_sometimes = array(
        'no'        => xlt('No'),
        'sometimes' => xlt('Sometimes'),
        'yes'       => xlt('Yes'),
    );
    $clinical = array(
        'difficulty_chewing'    => xlt('Difficulty chewing'),
        'difficulty_swallowing' => xlt('Difficulty swallowing'),
        'nausea_vomiting'       => xlt('Nausea or vomiting'),
        'diarrhea_constipation' => xlt('Diarrhea or constipation'),
    );
    foreach ($clinical as $fname => $qlabel) {
        echo '<div class="nsa-question">';
        echo '<span class="nsa-question-label">' . $qlabel . '</span>';
        nsa_radios($fname, $yn_sometimes, $row[$fname]);
        echo '</div>';
    }
    ?>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('On a special / therapeutic diet?'); ?></span>
      <?php nsa_radios('special_diet', array(
          'no'  => xlt('No'),
          'yes' => xlt('Yes'),
      ), $row['special_diet']); ?>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('If yes, diet type'); ?></span>
      <input class="nsa-text-input" type="text" name="special_diet_type"
             value="<?php echo attr($row['special_diet_type']); ?>">
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Known food allergies or intolerances?'); ?></span>
      <?php nsa_radios('food_allergies', array(
          'no'  => xlt('No'),
          'yes' => xlt('Yes'),
      ), $row['food_allergies']); ?>
    </div>

    <div class="nsa-question nsa-subquestion">
      <span class="nsa-question-label"><?php echo xlt('If yes, list allergies'); ?></span>
      <textarea name="food_allergies_detail" rows="2" cols="60"
                class="nsa-text-input"><?php echo text($row['food_allergies_detail']); ?></textarea>
    </div>
  </div>

  <div class="nsa-section">
    <h3><?php echo xlt('Functional Status & Risk'); ?></h3>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Needs assistance with eating'); ?></span>
      <?php nsa_radios('needs_assistance_eating', array(
          'independent' => xlt('Independent'),
          'some_help'   => xlt('Some help'),
          'dependent'   => xlt('Dependent'),
      ), $row['needs_assistance_eating']); ?>
    </div>

    <div class="nsa-question">
      <span class="nsa-question-label"><?php echo xlt('Overall nutrition risk (clinician assessment)'); ?></span>
      <?php nsa_radios('nutrition_risk', array(
          'low'      => xlt('Low risk'),
          'moderate' => xlt('Moderate risk'),
          'high'     => xlt('High risk'),
      ), $row['nutrition_risk']); ?>
    </div>
  </div>

  <div class="nsa-section">
    <h3><?php echo xlt('Additional Notes'); ?></h3>
    <div class="nsa-question">
      <textarea name="additional_notes" rows="4" cols="70"
                style="width:100%;box-sizing:border-box;"><?php echo text($row['additional_notes']); ?></textarea>
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
