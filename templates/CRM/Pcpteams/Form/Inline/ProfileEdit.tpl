{* HEADER *}
  {if $defaultImageUrl}
  <div class="avatar">
    <span> <strong> {ts} Current Profile Image {/ts} </strong> </span>
  </div>
  <div class="avatar">
    <img src="{$defaultImageUrl}" style="max-width:150px; width:auto; height:auto;">
  </div>
  <div class="clear"></div>
  <br>
  {/if}
  <div class="crm-section">
    <div class="label">{$form.image_URL.label}</div>
    <div class="content">{$form.image_URL.html}</div>
    <div class="clear"></div>
  </div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
