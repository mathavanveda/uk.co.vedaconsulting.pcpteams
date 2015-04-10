<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
	
	<!-- ( HEADER ) -->
	 <br>
	 <h2>{ts}Thanks for choosing to Support us for '{$eventDetails.title}'{/ts}</h2>
	 <br>
	 
	 <div>
	 	<p>
	 		Do you already have a place in this event?
	 	</p>
                     {assign var=eventId   value=$eventDetails.id}
                    <a class='crm-button' href='{crmURL p="civicrm/event/register" q="reset=1&id=$eventId"}'>{ts}No I need to register {/ts}</a>
	 </div>
	<!-- ( FIELD (AUTOMATIC LAYOUT) ) -->

	{foreach from=$elementNames item=elementName}
	  <div class="crm-section">
	    <div class="label">{$form.$elementName.label}</div>
	    <div class="content">{$form.$elementName.html}</div>
	    <div class="clear"></div>
	  </div>
	{/foreach}
	
	<br>
	<br>
	<br>
	<!-- ( FOOTER ) -->
	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="bottom"}
	</div>
</div>
