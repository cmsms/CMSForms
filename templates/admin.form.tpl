{if isset($form)}
{if $form->hasErrors()}<div style="color: red;">{$form->showErrors()}</div>{/if}
	{$form->getHeaders()}
	
	{$form->showWidgets('<div class="pageoverflow">
		<div class="pagetext">%LABEL%:</div>
		<div class="pageinput">%INPUT% <em>%TIPS%</em></div>
		<div class="pageinput" style="color: red;">%ERRORS%</div>
	</div>')}
	
	{$form->renderFieldsets('<div class="pageoverflow">
		<div class="pagetext">%LABEL%:</div>
		<div class="pageinput">%INPUT% <em>%TIPS%</em></div>
		<div class="pageinput" style="color: red;">%ERRORS%</div>
	</div>')}
	
	<p>
		{$form->getButtons()}
	</p>
	{$form->getFooters()}
{/if}