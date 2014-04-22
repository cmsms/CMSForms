<fieldset{if $fieldset->getClass() ne ''} class="{$fieldset->getClass()}"{/if}>
    <legend>{$fieldset->getLegend()}</legend>
    {form_rows form=$fieldset}
</fieldset>