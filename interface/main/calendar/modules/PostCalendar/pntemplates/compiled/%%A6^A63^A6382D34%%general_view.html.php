<?php /* Smarty version 2.6.31, created on 2018-12-06 13:31:26
         compiled from D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_view.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_view.html', 38, false),array('function', 'user_info', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_view.html', 318, false),array('modifier', 'escape', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_view.html', 38, false),)), $this); ?>

<script language="JavaScript">

 function popoutcontent(othis) <?php echo '{'; ?>

    let popsrc = $(othis).parents('body').find('#DocContents iframe').attr("src");
    let wname = '_' + Math.random().toString(36).substr(2, 6);
    let opt = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=no";
    window.open(popsrc,wname, opt);

 return false;
 <?php echo '}'; ?>


 // Process click on Delete link.
 function deleteme(docid) <?php echo '{'; ?>

  dlgopen('interface/patient_file/deleter.php?document=' + docid, '_blank', 500, 450);
  return false;
 <?php echo '}'; ?>


 // Called by the deleter.php window on a successful delete.
 function imdeleted() <?php echo '{'; ?>

  top.restoreSession();
  window.location.href='<?php echo $this->_tpl_vars['REFRESH_ACTION']; ?>
';
 <?php echo '}'; ?>


 // Called to show patient notes related to this document in the "other" frame.
 function showpnotes(docid) <?php echo '{'; ?>

 <?php echo '
 if (top.tab_mode) {
     let btnClose = \''; ?>
<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Done')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
<?php echo '\';
     let url = top.webroot_url + \'/interface/patient_file/summary/pnotes.php?docid=\' + docid;
     dlgopen(url, \'pno1\', \'modal-xl\', 500, \'\', \'\', {
         buttons: [
             {text: btnClose, close: true, style: \'default btn-xs\'}
         ],
         sizeHeight: \'auto\',
         allowResize: true,
         allowDrag: true,
         dialogId: \'\',
         type: \'iframe\'
     });
     return false;
 }
 '; ?>

  var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
  parent.left_nav.forceDual();
  parent.left_nav.loadFrame('pno1', othername, 'patient_file/summary/pnotes.php?docid=' + docid);
  return false;
 <?php echo '}'; ?>


 function submitNonEmpty( e ) <?php echo '{'; ?>

	if ( e.elements['passphrase'].value.length == 0 ) <?php echo '{'; ?>

		alert( "<?php echo smarty_function_xl(array('t' => 'You must enter a pass phrase to encrypt the document'), $this);?>
" );
	<?php echo '}'; ?>
 else <?php echo '{'; ?>

		e.submit();
	<?php echo '}'; ?>

 <?php echo '}'; ?>


// For tagging it with an encounter
function tagUpdate() <?php echo '{'; ?>

	var f = document.forms['document_tag'];
	if (f.encounter_check.checked) <?php echo '{'; ?>

		if(f.visit_category_id.value==0) <?php echo '{'; ?>

			alert(" <?php echo smarty_function_xl(array('t' => 'Please select visit category'), $this);?>
" );
			return false;
		<?php echo '}'; ?>

	<?php echo '}'; ?>
 else if (f.encounter_id.value == 0 ) <?php echo '{'; ?>

		alert(" <?php echo smarty_function_xl(array('t' => 'Please select encounter'), $this);?>
");
		return false;
	<?php echo '}'; ?>

	//top.restoreSession();
	document.forms['document_tag'].submit();
<?php echo '}'; ?>


// For new or existing encounter
function set_checkbox() <?php echo '{'; ?>

	var f = document.forms['document_tag'];
	if (f.encounter_check.checked) <?php echo '{'; ?>

		f.encounter_id.disabled = true;
		f.visit_category_id.disabled = false;
		$('.hide_clear').attr('href','javascript:void(0);');
	<?php echo '}'; ?>
 else <?php echo '{'; ?>

		f.encounter_id.disabled = false;
		f.visit_category_id.disabled = true;
		f.visit_category_id.value = 0;
		$('.hide_clear').attr('href','<?php echo $this->_tpl_vars['clear_encounter_tag']; ?>
');
	<?php echo '}'; ?>

<?php echo '}'; ?>


// For tagging it with image procedure
function ImgProcedure() <?php echo '{'; ?>

	var f = document.forms['img_procedure_tag'];
	if(f.image_procedure_id.value == 0 ) <?php echo '{'; ?>

		alert("<?php echo smarty_function_xl(array('t' => 'Please select image procedure'), $this);?>
");
		return false;
	<?php echo '}'; ?>

	f.procedure_code.value = f.image_procedure_id.options[f.image_procedure_id.selectedIndex].getAttribute('data-code');
	document.forms['img_procedure_tag'].submit();
<?php echo '}'; ?>

 // Process click on Import link.
 function import_ccr(docid) <?php echo '{
  top.restoreSession();
  $.ajax({
    url: "library/ajax/ccr_import_ajax.php",
    type: "POST",
    dataType: "html",
    data:
    {
      ccr_ajax : "yes",
      document_id : docid,
    },
    success: function(data){
      alert(data);
      top.restoreSession();
      document.location.reload();
    },
    error:function(){
      alert("failure");
    }
  });
 }'; ?>

</script>

<table valign="top" width="100%">
    <tr>
        <td>
            <div style="margin-bottom: 6px;padding-bottom: 6px;border-bottom:3px solid gray;">
            <h4><?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_url_web())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

              <div class="btn-group btn-toggle">
                <button class="btn btn-xs btn-default properties"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Properties')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</button>
                <button class="btn btn-xs btn-primary active"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Contents')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</button>
              </div>
            <span style="float:right;">
            <a class="css_button" href='' onclick='return popoutcontent(this)' title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pop Out Full Screen.')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
">
                <span class="glyphicon glyphicon-fullscreen"></span></a>
            <a class="css_button" href="<?php echo $this->_tpl_vars['web_path']; ?>
" title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Original file')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
" onclick="top.restoreSession()"><span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Download')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</span></a>
            <a class="css_button" href='' onclick='return showpnotes(<?php echo $this->_tpl_vars['file']->get_id(); ?>
)'><span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Show Notes')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</span></a>
            <?php echo $this->_tpl_vars['delete_string']; ?>

            <?php if ($this->_tpl_vars['file']->get_ccr_type($this->_tpl_vars['file']->get_id()) == 'CCR' && ( $this->_tpl_vars['file']->get_mimetype($this->_tpl_vars['file']->get_id()) == "application/xml" || $this->_tpl_vars['file']->get_mimetype($this->_tpl_vars['file']->get_id()) == "text/xml" ) && $this->_tpl_vars['file']->get_imported($this->_tpl_vars['file']->get_id()) == 0): ?>
            <a class="css_button" href='javascript:' onclick='return import_ccr(<?php echo $this->_tpl_vars['file']->get_id(); ?>
)'><span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Import')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</span></a>
            <?php endif; ?>
            </span>
            </h4>
            </div>
        </td>
    </tr>
    <tr id="DocProperties" style="display:none;">
		<td valign="top">
			<?php if (! $this->_tpl_vars['hide_encryption']): ?>
			<div class="text">
                <form method="post" name="document_encrypt" action="<?php echo $this->_tpl_vars['web_path']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Encryption')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="submitNonEmpty( document.forms['document_encrypt'] );">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='download encrypted file')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
                    </div>
                </div>
                <div>
                    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Pass Phrase')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:
                    <input title="<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Supports TripleDES encryption/decryption only.')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Leaving the pass phrase blank will not encrypt the document')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
" type='text' size='20' name='passphrase' id='passphrase' value=''/>
                    <input type="hidden" name="encrypted" value="true"></input>
              	</div>
                </form>
            </div>
            <br/>
            <?php endif; ?>
			<div class="text">
                <form method="post" name="document_validate" action="<?php echo $this->_tpl_vars['VALIDATE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Sha-1 Hash')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:</b>&nbsp;
                        <i><?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_hash())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_validate'].submit();">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='validate')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
                    </div>
                </div>
                </form>
            </div>
            <br/>
            <div class="text">
                <form method="post" name="document_update" action="<?php echo $this->_tpl_vars['UPDATE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Update')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_update'].submit();">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='submit')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
                    </div>
                </div>
                <div>
                    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Rename')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:
                    <input type='text' size='20' name='docname' id='docname' value='<?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_url_web())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
'/>
              	</div>
                <div>
                    <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Date')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
:
                    <input type='text' size='10' class='datepicker' name='docdate' id='docdate'
                     value='<?php echo ((is_array($_tmp=$this->_tpl_vars['DOCDATE'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
' title='<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='yyyy-mm-dd document date')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
' />
                    <select name="issue_id"><?php echo $this->_tpl_vars['ISSUES_LIST']; ?>
</select>
                </div>
                </form>
            </div>

            <br/>

            <div class="text">
                <form method="post" name="document_move" action="<?php echo $this->_tpl_vars['MOVE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Move')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_move'].submit();">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='submit')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
                    </div>
                </div>

                <div>
                        <select name="new_category_id"><?php echo $this->_tpl_vars['tree_html_listbox']; ?>
</select>&nbsp;
                        <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Move to Patient')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 # <input type="text" name="new_patient_id" size="4" />
                        <a href="javascript:<?php echo '{}'; ?>
"
                         onclick="top.restoreSession();var URL='controller.php?patient_finder&find&form_id=<?php echo ((is_array($_tmp="document_move['new_patient_id']")) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
&form_name=<?php echo ((is_array($_tmp="document_move['new_patient_name']")) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
'; window.open(URL, 'document_move', 'toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,width=450,height=400,left=425,top=250');">
                        <img src="images/stock_search-16.png" border="0" /></a>
                        <input type="hidden" name="new_patient_name" value="" />
                </div>
                </form>
            </div>

			<br/>

			<div class="text">
			   <form method="post" name="document_tag" id="document_tag" action="<?php echo $this->_tpl_vars['TAG_ACTION']; ?>
" onsubmit="return top.restoreSession()">

				<div >
				   <div style="float:left">
					   <b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Tag to Encounter')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
				   </div>

				   <div style="float:none">
					   <a href="javascript:;" onclick="tagUpdate();">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='submit')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
				   </div>
			   </div>

				 <div>
					<select id="encounter_id"  name="encounter_id"  ><?php echo $this->_tpl_vars['ENC_LIST']; ?>
</select>&nbsp;
					<a href="<?php echo $this->_tpl_vars['clear_encounter_tag']; ?>
" class="hide_clear">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='clear')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>&nbsp;&nbsp;
					<input type="checkbox" name="encounter_check" id="encounter_check"  onclick='set_checkbox(this)'/> <label for="encounter_check"><b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Create Encounter')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b></label>&nbsp;&nbsp;
					   <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Visit Category')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 : &nbsp;<select id="visit_category_id"  name="visit_category_id"  disabled><?php echo $this->_tpl_vars['VISIT_CATEGORY_LIST']; ?>
</select>&nbsp;

			   </div>
			   </form>
		   </div>
		   <br/>
		   <div class="text">
			<form method="post" name="img_procedure_tag" id="img_procedure_tag" action="<?php echo $this->_tpl_vars['IMG_PROCEDURE_TAG_ACTION']; ?>
" onsubmit="return top.restoreSession()">
			<input type='hidden' name='procedure_code' value=''>
			<div>
				<div style="float:left">
					<b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Tag to Image Procedure')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
				</div>
				<div style="float:none">
					<a href="javascript:;" onclick="ImgProcedure();">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='submit')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
				</div>
			</div>
			<div>
				<select id="image_procedure_id"  name="image_procedure_id"><?php echo $this->_tpl_vars['IMAGE_PROCEDURE_LIST']; ?>
</select>&nbsp;
				<a href="<?php echo $this->_tpl_vars['clear_procedure_tag']; ?>
">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='clear')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
)</span></a>
			</div>
			</form>
		   </div>

            <br/>

            <form name="notes" method="post" action="<?php echo $this->_tpl_vars['NOTE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
            <div class="text">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Notes')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.notes.identifier.value='no';document.forms['notes'].submit();">(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='add')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</span>)</a>
                    	&nbsp;&nbsp;&nbsp;<b><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Email')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</b>&nbsp;
                    	<input type="text" size="25" name="provide_email" id="provide_email" />
                    	<input type="hidden" name="identifier" id="identifier" />
                        <a href="javascript:;" onclick="javascript:document.notes.identifier.value='yes';document.forms['notes'].submit();">
                        	(<span><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Send')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</span>)
                        </a>
                    </div>
                    <div>

                    </div>
                    <div style="float:none">

                    </div>
                <div>
                    <textarea cols="53" rows="8" wrap="virtual" name="note" style="width:100%"></textarea><br>
                    <input type="hidden" name="process" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['PROCESS'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
                    <input type="hidden" name="foreign_id" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />

                    <?php if ($this->_tpl_vars['notes']): ?>
                    <div style="margin-top:7px">
                        <?php $_from = $this->_tpl_vars['notes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['note_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['note_loop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['note']):
        $this->_foreach['note_loop']['iteration']++;
?>
                        <div>
                        <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Note')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 #<?php echo ((is_array($_tmp=$this->_tpl_vars['note']->get_id())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                        <?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Date:')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['note']->get_date())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                        <?php echo ((is_array($_tmp=$this->_tpl_vars['note']->get_note())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

                        <?php if ($this->_tpl_vars['note']->get_owner()): ?>
                            &nbsp;-<?php echo smarty_function_user_info(array('id' => $this->_tpl_vars['note']->get_owner()), $this);?>

                        <?php endif; ?>
                        </div>
                        <?php endforeach; endif; unset($_from); ?>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            </form>
            <h4><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Contents')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
</h4>
		</td>
	</tr>
	<tr id="DocContents" style="height:100%">
		<td>
            <?php if ($this->_tpl_vars['file']->get_mimetype() == "image/tiff" || $this->_tpl_vars['file']->get_mimetype() == "text/plain"): ?>
			<embed frameborder="0" style="height:84vh" type="<?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_mimetype())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['web_path'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
as_file=false"></embed>
			<?php elseif ($this->_tpl_vars['file']->get_mimetype() == "image/png" || $this->_tpl_vars['file']->get_mimetype() == "image/jpg" || $this->_tpl_vars['file']->get_mimetype() == "image/jpeg" || $this->_tpl_vars['file']->get_mimetype() == "image/gif" || $this->_tpl_vars['file']->get_mimetype() == "application/pdf"): ?>
			<iframe frameborder="0" style="height:84vh" type="<?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_mimetype())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['web_path'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
as_file=false"></iframe>
            <?php elseif ($this->_tpl_vars['file']->get_mimetype() == "application/dicom" || $this->_tpl_vars['file']->get_mimetype() == "application/dicom+zip"): ?>
            <iframe frameborder="0" style="height:84vh" type="<?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_mimetype())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo $this->_tpl_vars['webroot']; ?>
/library/dicom_frame.php?web_path=<?php echo ((is_array($_tmp=$this->_tpl_vars['web_path'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
as_file=false"></iframe>
            <?php elseif ($this->_tpl_vars['file']->get_ccr_type($this->_tpl_vars['file']->get_id()) != 'CCR' && $this->_tpl_vars['file']->get_ccr_type($this->_tpl_vars['file']->get_id()) != 'CCD'): ?>
            <iframe frameborder="0" style="height:84vh" type="<?php echo ((is_array($_tmp=$this->_tpl_vars['file']->get_mimetype())) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['web_path'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
as_file=true"></iframe>
			<?php endif; ?>
		</td>
	</tr>
</table>
<script language='JavaScript'>
<?php echo '
$(\'.btn-toggle\').click(function() {
    $(this).find(\'.btn\').toggleClass(\'active\');

    if ($(this).find(\'.btn-primary\').length >0) {
        $(this).find(\'.btn\').toggleClass(\'btn-primary\');
    }

    $(this).find(\'.btn\').toggleClass(\'btn-default\');
    var show_prop = ($(this).find(\'.properties.active\').length > 0 ? \'block\':\'none\');
    $("#DocProperties").css(\'display\', show_prop);
});
'; ?>

</script>