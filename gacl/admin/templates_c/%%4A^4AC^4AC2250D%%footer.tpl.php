<?php /* Smarty version 2.6.31, created on 2018-12-05 15:34:09
         compiled from phpgacl/footer.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'phpgacl/footer.tpl', 4, false),)), $this); ?>
		</div></div>

		<div id="bot-br"><div id="bot-bl"><div id="bot-tr"><div id="bot-tl">
			<a href="http://phpgacl.sourceforge.net">phpGACL</a> v<?php echo ((is_array($_tmp=$this->_tpl_vars['phpgacl_version'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 (Schema v<?php echo ((is_array($_tmp=$this->_tpl_vars['phpgacl_schema_version'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
) - Generic Access Control Lists
			<br />
			Copyright &copy; 2005 <a href="about.php">Mike Benoit</a>
		</div></div></div></div>

	<!-- End Body -->
  </body>
</html>