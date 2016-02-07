{* Displays Registration Control Panel *}
<div id="help" class="description section-hidden-border">
	{capture assign=plusImg}<img src="{$config->resourceBase}i/TreePlus.gif" alt="{ts}plus sign{/ts}" />{/capture}
	{ts 1=$plusImg}Administer your organization's tournament registration using the links on this page.{/ts}
</div>
{strip}
<div class="crm-content-block">
	<h3>Upcoming Tournament: {$billing_contact->tournament->event.title}</h3>
	<h4>Welcome <a href={$indEditLink} target="_blank">{$billing_contact->contact.display_name}</a>.</h4>
	<ul>
		<li>Update <a href={$indEditLink}>your individual contact information</a>.</li>
		<li>Update <a href={$orgEditLink}>your organization's contact information</a> ({$billing_contact->organization.display_name}).</li>
		{* <li>Register participants for <a href="civicrm/event/register&reset=1&id={$billing_contact->tournament->event.id}">{$billing_contact->tournament->event.title}</a>.</li>*}
		<li>You have access to 'contacts' (players, coaches, etc.) in these groups:
		
		{assign var="profiles" value=$billing_contact->registrationProfiles}
		{if $profiles}
			<li>	
				<ol>
					{foreach from=$profiles item=profile}
						<li>{$profile.title}
							<ul>
								<li><a target="_blank" href="{$profileLinkBase}&gid={$profile.id}&reset=1&force=1">List/edit contacts.</a></li>
								<li><a target="_blank" href="{$profileLinkBase}/create&gid={$profile.id}&reset=1">Add new contact.</a></li>
							</ul>
						</li>
					{/foreach}
				</ol>
			</li>
		{/if}
		</li>
	</ul>

	<h5 style="display:none">(Coming soon: a list of participants from 2015 to get you started for 2016.)</h5>
	<div style="display:none">
	<p>These members (below) are currently 'related' to {$billing_contact->organization.display_name}. Click to edit.</p>
	
  {capture assign=crmURLI}{crmURL p=$pMemberAdd q=$qMemberAdd}{/capture}
  {ts 1=$crmURLI}Add a <a href='%1'>New Member</a>{/ts} for {$billing_contact->organization.display_name}.
	
	{assign var="rows" value=$billing_contact->members}
	{if $rows}
		 {* This section displays the rows along and includes the paging controls *}
		 <div class="crm-search-results">		 
	     <table>
	      <tr class="columnheader">
	      {foreach from=$columnHeaders item=header}
	        <th scope="col">
	        {*if $header.sort}
	          {assign var='key' value=$header.sort}
	          {$sort->_response.$key.link}
	        {else*}
	          {$header.name}
	        {*/if*}
	         </th>
	      {/foreach}
	      </tr>
	
	      {counter start=0 skip=1 print=false}
	      {foreach from=$rows item=row name=listings}
	      	<tr id="row-{$smarty.foreach.listings.iteration}" class="{cycle values="odd-row,even-row"}">
	      		{foreach from=$columnHeaders item=header}
	      				{assign var='key' value=$header.field_name}
	          		<td class="crm-{$key}"><a href="{$memberEditLink}&id={$row.id}">{$row.$key}</a></td>
	      		{/foreach}
	      	</tr>
	      {/foreach}
	     </table>
	    
	    {*include file='CRM/Contact/Form/Selector.tpl'*}
		 </div> 
	{else}
	  <div class="crm-results-block crm-results-block-empty">
	      {include file="CRM/Tournament/Form/Search/EmptyMemberResults.tpl"}
	  </div>
	{/if}
	
  {ts 1=$crmURLI}Add a <a href='%1'>New Member</a>{/ts} for {$billing_contact->organization.display_name}.
	
	</div>
</div>
{/strip}