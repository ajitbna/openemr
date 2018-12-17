<?php /* Smarty version 2.6.31, created on 2018-12-05 15:34:42
         compiled from phpgacl/pager.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'phpgacl/pager.tpl', 7, false),)), $this); ?>
<table width="100%" cellspacing="0" cellpadding="2" border="0">
  <tr valign="middle">
    <td align="left">
<?php if ($this->_tpl_vars['paging_data']['atfirstpage']): ?>
      |&lt; &lt;&lt;
<?php else: ?>
      <a href="<?php echo $this->_tpl_vars['link']; ?>
page=1">|&lt;</a> <a href="<?php echo $this->_tpl_vars['link']; ?>
page=<?php echo ((is_array($_tmp=$this->_tpl_vars['paging_data']['prevpage'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">&lt;&lt;</a>
<?php endif; ?>
    </td>
    <td align="right">
<?php if ($this->_tpl_vars['paging_data']['atlastpage']): ?>
      &gt;&gt; &gt;|
<?php else: ?>
      <a href="<?php echo $this->_tpl_vars['link']; ?>
page=<?php echo ((is_array($_tmp=$this->_tpl_vars['paging_data']['nextpage'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">&gt;&gt;</a> <a href="<?php echo $this->_tpl_vars['link']; ?>
page=<?php echo ((is_array($_tmp=$this->_tpl_vars['paging_data']['lastpageno'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">&gt;|</a>
<?php endif; ?>
    </td>
  </tr>
</table>