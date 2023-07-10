{* {extends file='page.tpl'}

{block name="page_content"}
  <h1>{l s='Newsletter subscription' d='Modules.Emailsubscription.Shop'}</h1>

  <p class="alert {if $variables.nw_error}alert-danger{else}alert-success{/if}">
    {$variables.msg}
  </p>

  {if $variables.conditions}
    <p>{$variables.conditions}</p>
  {/if}

{/block} *}
