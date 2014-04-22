{* DEPRECATED *}
{if isset($form)}
{if $form->hasErrors()}<div style="color: red;">{$form->showErrors()}</div>{/if}
	{$form->getHeaders()}
	
	{$form->showWidgets()}
	
	{$form->renderFieldsets()}
	
	<p>
		{$form->getButtons()}
	</p>
	{$form->getFooters()}
{/if}