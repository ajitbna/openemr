<?php /* Smarty version 2.6.31, created on 2018-12-11 12:51:42
         compiled from D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_upload.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_upload.html', 16, false),array('modifier', 'escape', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_upload.html', 16, false),)), $this); ?>

<form method=post enctype="multipart/form-data" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" onsubmit="return top.restoreSession()">
<input type="hidden" name="MAX_FILE_SIZE" value="64000000" />

<?php if (( ! ( $this->_tpl_vars['patient_id'] > 0 ) )): ?>
  <div class="text" style="color:red;">
    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp="IMPORTANT: This upload tool is only for uploading documents on patients that are not yet entered into the system. To upload files for patients whom already have been entered into the system, please use the upload tool linked within the Patient Summary screen.")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

    <br/>
    <br/>
  </div>
<?php endif; ?>

<div class="text">
    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp="NOTE: Uploading files with duplicate names will cause the files to be automatically renamed (for example, file.jpg will become file.1.jpg). Filenames are considered unique per patient, not per category.")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

    <br/>
    <br/>
</div>
<div class="text bold">
    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Upload Document')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 <?php if ($this->_tpl_vars['category_name']): ?> <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='to category')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 '<?php echo ((is_array($_tmp=$this->_tpl_vars['category_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'<?php endif; ?>
</div>
<div class="text">
    <p><span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Source File Path')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</span> <input type="file" name="file[]" id="source-name" multiple="true"/>&nbsp;(<font size="1"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp="Multiple files can be uploaded at one time by selecting them using CTRL+Click or SHIFT+Click.")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</font>)</p>
    <p><span title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Leave Blank To Keep Original Filename')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Optional Destination Name')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</span> <input type="text" name="destination" title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Leave Blank To Keep Original Filename')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
" id="destination-name" /></p>
    <?php if (! $this->_tpl_vars['hide_encryption']): ?>
	</br>
	<p><span title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Check the box if this is an encrypted file')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp="Is The File Encrypted?")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</span> <input type="checkbox" name="encrypted" title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Check the box if this is an encrypted file')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
" id="encrypted" /></p>
	<p><span title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pass phrase to decrypt document')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pass Phrase')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</span> <input type="text" name="passphrase" title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pass phrase to decrypt document')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
" id="passphrase" /></p>
	<p><i><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Supports AES-256-CBC encryption/decryption only.')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</i></p>
    <?php endif; ?>
    <p><input type="submit" value="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Upload')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
" /></p>
</div>

<input type="hidden" name="patient_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['patient_id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
<input type="hidden" name="category_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['category_id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
<input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
</form>

<br><br>

<!-- Drag and drop uploader -->
<div id="autouploader">
<form method="post" enctype="multipart/form-data" action="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/ajax/upload.php?patient_id=<?php echo ((is_array($_tmp=$this->_tpl_vars['patient_id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
&parent_id=<?php echo ((is_array($_tmp=$this->_tpl_vars['category_id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="dropzone">
<input type="hidden" name="MAX_FILE_SIZE" value="64000000" >
</form>
</div>

<!-- Section for document template download -->
<form method='post' action='interface/patient_file/download_template.php' onsubmit='return top.restoreSession()'>
<input type='hidden' name='patient_id' value='<?php echo ((is_array($_tmp=$this->_tpl_vars['patient_id'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
' />
<p class='text bold'>
 <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Download document template for this patient and visit')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

</p>
<p class='text'>
 <select name='form_filename'><?php echo $this->_tpl_vars['TEMPLATES_LIST']; ?>
</select> &nbsp;
 <input type='submit' value='<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Fetch')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
' />
</p>
</form>
<!-- End document template download section -->

<?php if (! empty ( $this->_tpl_vars['file'] )): ?>
	<div class="text bold">
		<br/>
		<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Upload Report')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>

	</div>
	<?php $_from = $this->_tpl_vars['file']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>
		<div class="text">
			<?php if ($this->_tpl_vars['error']): ?><i><?php echo ((is_array($_tmp=$this->_tpl_vars['error'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i><br/><?php endif; ?>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='ID')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Patient')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_foreign_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='URL')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_url())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Size')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_size())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Date')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_date())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Hash')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_hash())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='MimeType')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_mimetype())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br>
			<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Revision')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
: <?php echo ((is_array($_tmp=$this->_tpl_vars['file']->revision)) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
<br><br>
		</div>
	<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
<h3><?php echo $this->_tpl_vars['error']; ?>
</h3>