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
  <h3>Smarty side</h3>
  <code><pre>
  {if isset($form)}
    {if $form-&gt;hasErrors()}&lt;div style=&quot;color: red;&quot;&gt;{$form-&gt;showErrors()}&lt;/div&gt;{/if}
	{$form-&gt;getHeaders()}

	{$form-&gt;showWidgets(\&#x27;&lt;div class=&quot;pageoverflow&quot;&gt;
		&lt;div class=&quot;pagetext&quot;&gt;%LABEL%:&lt;/div&gt;
		&lt;div class=&quot;pageinput&quot;&gt;%INPUT% &lt;em&gt;%TIPS%&lt;/em&gt;&lt;/div&gt;
		&lt;div class=&quot;pageinput&quot; style=&quot;color: red;&quot;&gt;%ERRORS%&lt;/div&gt;
	&lt;/div&gt;\&#x27;)}

	{$form-&gt;renderFieldsets(\&#x27;&lt;div class=&quot;pageoverflow&quot;&gt;
		&lt;div class=&quot;pagetext&quot;&gt;%LABEL%:&lt;/div&gt;
		&lt;div class=&quot;pageinput&quot;&gt;%INPUT% &lt;em&gt;%TIPS%&lt;/em&gt;&lt;/div&gt;
		&lt;div class=&quot;pageinput&quot; style=&quot;color: red;&quot;&gt;%ERRORS%&lt;/div&gt;
	&lt;/div&gt;\&#x27;)}

	&lt;p&gt;
		{$form-&gt;getButtons()}
	&lt;/p&gt;
	{$form-&gt;getFooters()}
{/if}
  </pre></code>

  <h3>Changelog</h3>
<pre>
1.10.16
-------
- Errors messages can be translated using I18n
- Validators are now in separate classes
- Validators can now directly passed from the widget constructor (ex: \'validators\' => array(\'my_validator\' => new CMSFValidatorNotEmpty(array(\'foo\' => \'bar\')))
</pre>
	<h3>Credits</h3>
	<p>Jean-Christophe Cuvelier - totophe@totophe.com</p>	
	',
	
	
	
);