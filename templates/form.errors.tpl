{if isset($errors)}
    <ul class="errors">
        {foreach from=$errors item=error_list key=priority}
            <li>
                {if $show_priority}
                    <em class="form_error_priority">{$priority}</em>
                {/if}
                <ul>
                    {foreach from=$error_list item=error}
                        <li>{$error}</li>
                    {/foreach}
                </ul>
            </li>
        {/foreach}
    </ul>
{/if}