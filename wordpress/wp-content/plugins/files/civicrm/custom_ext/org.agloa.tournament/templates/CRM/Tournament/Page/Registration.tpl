{* Displays Registration Control Panel *}
<div id="help" class="description section-hidden-border">
{capture assign=plusImg}<img src="{$config->resourceBase}i/TreePlus.gif" alt="{ts}plus sign{/ts}" />{/capture}
{ts 1=$plusImg}Administer your organization's tournament registration using the links on this page. {* Click %1 for descriptions of the options in each section.*}{/ts}
</div>
{strip}
<div class="crm-content-block">
<h3>Upcoming Tournament: {$billing_contact->tournament->event.title}</h3>

<h4>Welcome <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->contact.id}" target="_blank">{$billing_contact->contact.display_name}</a>.</h4>
<ul>
<li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->contact.id}" target="_blank">your individual contact information</a>.</li>
<li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->organization.id}" target="_blank">your organization's contact information</a> ({$billing_contact->organization.display_name}).</li>
<li>Register participants for <a href="admin.php?page=CiviCRM&q=civicrm/event/register&reset=1&id={$billing_contact->tournament->event.id}" target="_blank">{$billing_contact->tournament->event.title}</a>.</li>
</ul>

{assign var="rows" value=$billing_contact->members}
{if $rows}
	 {* This section displays the rows along and includes the paging controls *}
	 <div class="crm-search-results">
    {include file='CRM/Contact/Form/Selector.tpl' context='Search'}
	 </div> 
{else}
  <div class="crm-results-block crm-results-block-empty">
      {include file="CRM/Tournament/Form/Search/EmptyMemberResults.tpl"}
  </div>
{/if}

</div>
{/strip}