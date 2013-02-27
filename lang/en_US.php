<?php

$lang = array(
	
	
	
	
	
// Exceptions

'field_is_empty' => 'The field %s is empty.',	
'field_cannot_be_empty' => 'The field "%s" cannot be empty.',	
'unknown field' => 'Unknown field "%s"',
'fields not equal' => 'The "%s" and "%s" have to be the same.',
'invalid email' => 'The email &laquo;%s&raquo; is not valid.',
'field not unique' => '%s already exists.',
'select one' => 'Select one',
	
	
	'help' => '
	
	<h3>How to use this module?</h3>
	<p>This module is aimed to replace the traditional way of manipulate forms in CMSMS "API". It allows you to focus more on your objects and code.</p>
	
	<h3>PHP Side</h3>
	<p>To create a form, use the following format.</p>
  <textarea cols="200" rows="2" style="height:40px; ">
  $form = new CMSForm(\'MyModule\', $id, \'action\', $returnid);
  $this->smarty->assign(\'form\', $form);
  </textarea>	
  <p>To go furter, you can use any of the following commands:</p>
	<textarea cols="200">
  $form = new CMSForm(\'MyModule\', $id, \'action\', $returnid);
  
  $form->setButtons(array(\'save\',\'cancel\'));
  
  $form->setWidget(\'title\', \'text\', array(\'class\' => \'field\'));
  
  $this->smarty->assign(\'form\', $form);
  </textarea>
	<h3>Credits</h3>
	<p>Jean-Christophe Cuvelier - totophe@totophe.com</p>	
	',
	
	
	
);