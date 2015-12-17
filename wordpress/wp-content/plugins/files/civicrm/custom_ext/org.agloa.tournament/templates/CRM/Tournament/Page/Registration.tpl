{* Displays Registration Control Panel *}
<div id="help" class="description section-hidden-border">
{capture assign=plusImg}<img src="{$config->resourceBase}i/TreePlus.gif" alt="{ts}plus sign{/ts}" style="vertical-align: bottom; height: 20px; width: 20px;" />{/capture}
{ts 1=$plusImg}Administer your organization's tournament registration using the links on this page. {* Click %1 for descriptions of the options in each section.*}{/ts}
</div>
{strip}
<div class="crm-content-block">
<h3>Upcoming Tournament: {$billing_contact->tournament->event->title}</h3>

<h4>Welcome <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->contact->id}" target="_blank">{$billing_contact->contact->display_name}</a>.</h4>
<ul>
<li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->contact->id}" target="_blank">your contact information</a>.</li>
<li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->organization->id}" target="_blank">your organization's contact information</a> ({$billing_contact->organization->display_name}).</li>
<!--li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view/rel&reset=1&cid=1004" target="_blank">Ann Arbor participants</a>.</li-->
<li>Register participants for <a href="admin.php?page=CiviCRM&q=civicrm/event/register&reset=1&id={$billing_contact->tournament->event->id}" target="_blank">{$billing_contact->tournament->event->title}</a>.</li>
</ul>
</div>
{/strip}