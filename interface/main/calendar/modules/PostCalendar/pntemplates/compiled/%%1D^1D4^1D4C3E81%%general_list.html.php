<?php /* Smarty version 2.6.31, created on 2018-12-10 08:27:31
         compiled from D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'headerShow', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_list.html', 13, false),array('function', 'xl', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_list.html', 54, false),array('function', 'datetimepickerSupport', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_list.html', 171, false),array('modifier', 'escape', 'D:/xampp/htdocs/openemr-5.0.1/templates/documents/general_list.html', 69, false),)), $this); ?>
<html>
<head>

<?php echo smarty_function_headerShow(array(), $this);?>

<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/bootstrap-3-3-4/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['css_header']; ?>
" type="text/css">
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone-4-3-0/dist/dropzone.css">
<link href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-ui-1-12-1/themes/ui-lightness/jquery-ui.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
<?php echo '
<style type="text/css">
.warn_diagnostic {
    margin: 10 auto 10 auto;
    color: rgb(255, 0, 0);
    font-size: 1.5em;
}
.ui-autocomplete {
    position: absolute;
    top: 0;
    left: 0;
    min-width:200px;
    cursor: default;
}
.ui-menu-item{
     min-width:200px;
}
.fixed-height{
min-width:200px;
padding: 1px;
max-height: 35%;
overflow: auto;
}
</style>
'; ?>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-ui-1-12-1/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
/library/js/DocumentTreeMenu.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/dropzone-4-3-0/dist/dropzone.js"></script>
<script type="text/javascript" src="library/dialog.js?v=<?php echo $this->_tpl_vars['GLOBALS']['v_js_includes']; ?>
"></script>
<script type="text/javascript" src="library/textformat.js?v=<?php echo $this->_tpl_vars['GLOBALS']['v_js_includes']; ?>
"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['GLOBALS']['assets_static_relative']; ?>
/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<script type="text/javascript">
    // dropzone javascript asset translation(s)
    Dropzone.prototype.defaultOptions.dictDefaultMessage = "<?php echo smarty_function_xl(array('t' => 'Drop files here to upload'), $this);?>
";
</script>

</head>
<!--<body bgcolor="<?php echo $this->_tpl_vars['STYLE']['BGCOLOR2']; ?>
">-->
<!-- ViSolve - Call expandAll function on loading of the page if global value 'expand_document' is set -->
<?php if ($this->_tpl_vars['GLOBALS']['expand_document_tree']): ?>
  <body class="body_top" onload="javascript:objTreeMenu_1.expandAll();return false;">
<?php else: ?>
  <body class="body_top">
<?php endif; ?>

<div id="documents_list">
    <div class="ui-widget"style="float:right;">
        <button id='pid' class="pBtn" type="button" style="float:right;">0</button>
         <input id="selectPatient" type="text" placeholder="<?php echo ((is_array($_tmp=$this->_tpl_vars['place_hld'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>">
    </div>
    <a id="list_collapse" href="#" onclick="javascript:objTreeMenu_1.collapseAll();return false;">&nbsp;(<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Collapse all')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
    )</a>
    <div class="title"><?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Documents')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
    </div>
    <?php echo $this->_tpl_vars['tree_html']; ?>

</div>
<div id="documents_actions">
		<?php if ($this->_tpl_vars['message']): ?>
			<div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i></div><br>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['messages']): ?>
            <div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo ((is_array($_tmp=$this->_tpl_vars['messages'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i></div><br>
		<?php endif; ?>
		<?php echo $this->_tpl_vars['activity']; ?>

</div>
<script type="text/javascript">
var curpid = "<?php echo ((is_array($_tmp=$this->_tpl_vars['cur_pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
";
var newVersion="<?php echo ((is_array($_tmp=$this->_tpl_vars['is_new'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
";
var demoPid = "<?php echo ((is_array($_tmp=$this->_tpl_vars['demo_pid'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
";
var inUseMsg = "<?php echo ((is_array($_tmp=$this->_tpl_vars['used_msg'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
";
<?php echo '
if(curpid == demoPid && !newVersion){
    $(".ui-widget").hide();
}
else{
    $("#pid").text(curpid);
}
$(function() {
    $( "#selectPatient" ).autocomplete({
    	source: "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/library/ajax/document_helpers.php",
    	focus: function(event, sel) {
            event.preventDefault();
        },
        select: function(event, sel) {
            event.preventDefault();
            if (sel.item.value == \'00\' && ! sel.item.label.match(\''; ?>
<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Reset')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
<?php echo '\')){
            	alert(inUseMsg);
            	return false;
            }
            $(this).val(sel.item.label);
            location.href = "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/controller.php?document&list&patient_id="+sel.item.value+"&patient_name=" + sel.item.label;
            $("#pid").text(sel.item.value);
        },
        minLength: 3
    }).autocomplete("widget").addClass("fixed-height");
 });
$(".pBtn").click(function(event) {
    var $input = $("#selectPatient");
        $input.val(\'*\');
        $input.autocomplete(\'search\'," ");
        $input.val(\'\');
});
$("#list_collapse").detach().appendTo("#objTreeMenu_1_node_1 nobr");

// functions to view and pop out documents as needed.
//
$(function () {
    $("img[id^=\'icon_objTreeMenu_\']").tooltip({
        items: $("img[src*=\'file3.png\']"),
        content: \''; ?>
<?php echo smarty_function_xl(array('t' => ((is_array($_tmp="Double Click on this icon to pop up document in a new viewer.")) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
<?php echo '\'
    });

    $("img[id^=\'icon_objTreeMenu_\']").on(\'dblclick\', function (e) {
        let popsrc = $(this).next("a").attr(\'href\') || \'\';
        let diview = $(this).next("a").text();
        let dflg = false;
        if (!popsrc.includes(\'&view&\')) {
            return false;
        } else if (diview.toLowerCase().includes(\'.dcm\') || diview.toLowerCase().includes(\'.zip\')) {
            popsrc = "'; ?>
<?php echo $this->_tpl_vars['GLOBALS']['webroot']; ?>
<?php echo '/library/dicom_frame.php?web_path=" + popsrc;
            dflg = true;
        }
        popsrc = popsrc.replace(\'&view&\', \'&retrieve&\') + \'as_file=false\';
        let poContentModal = function () {
            let wname = \'_\' + Math.random().toString(36).substr(2, 6);
            let opt = "menubar=no,location=no,resizable=yes,scrollbars=yes,status=no";
            window.open(popsrc, wname, opt);
        };

        let btnText = \''; ?>
<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Full Screen')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
<?php echo '\';
        let btnClose = \''; ?>
<?php echo smarty_function_xl(array('t' => ((is_array($_tmp='Close')) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html'))), $this);?>
<?php echo '\';
        let size = \'modal-xl\';
        let sizeHeight = \'full\';
        if (dflg) {
            size = \'modal-md\';
        }
        dlgopen(popsrc, \'popdoc\', size, 600, \'\', \'\', {
            buttons: [
                {text: btnText, close: true, style: \'primary btn-xs\', click: poContentModal},
                {text: btnClose, close: true, style: \'default btn-xs\'}
            ],
            sizeHeight: sizeHeight,
            allowResize: true,
            allowDrag: true,
            dialogId: \'\',
            type: \'iframe\'
        });
        return false;
    });
});

$(document).ready(function(){'; ?>

    <?php echo smarty_function_datetimepickerSupport(array(), $this);?>

<?php echo '});'; ?>


</script>
</body>
</html>