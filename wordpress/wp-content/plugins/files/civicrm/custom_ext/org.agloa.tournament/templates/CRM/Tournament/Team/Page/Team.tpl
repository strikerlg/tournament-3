{* Actions: 1=add, 2=edit, browse=16, delete=8 *}
{if $action ne 1 and $action ne 2 and $action ne 8 and $groupPermission eq 1}
<div class="crm-submit-buttons">
    <a accesskey="N" href="{crmURL p='civicrm/tournament/team/add' q='reset=1'}" class="newGroup button"><span><div class="icon ui-icon-circle-plus"></div>{ts}Add Team{/ts}</span></a><br/>
</div>
{/if} {* action ne add or edit *}
<div class="crm-block crm-content-block">
{if $action eq 16}
<div id="help">
    {ts}Use Teams to organize players (e.g. these players compete as 'The A Team').{/ts} {help id="manage_teams"}
</div>
{/if}
{if $action ne 2 AND $action ne 8}
{include file="CRM/Tournament/Team/Form/Search.tpl"}
{/if}

{if $action eq 1 or $action eq 2}
   {include file="CRM/Tournament/Team/Form/Edit.tpl"}
{elseif $action eq 8}
   {include file="CRM/Tournament/Team/Form/Delete.tpl"}
{/if}

{if $action ne 1 and $action ne 2 and $action ne 8 and $groupPermission eq 1}
<div class="crm-submit-buttons">
        <a accesskey="N" href="{crmURL p='civicrm/tournament/team/add' q='reset=1'}" class="newGroup button"><span><div class="icon ui-icon-circle-plus"></div>{ts}Add Team{/ts}</span></a><br/>
</div>
{/if} {* action ne add or edit *}
</div>
