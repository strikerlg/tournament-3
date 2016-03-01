{* this template is used for adding/editing teams (name and description only)  *}
<div class="crm-block crm-form-block crm-group-form-block">
    <div id="help">
  {if $action eq 2}
      {capture assign=crmURL}class="no-popup" href="{crmURL p="civicrm/tournament/team/search" q="reset=1&force=1&context=smog&gid=`$group.id`"}"{/capture}
      {ts 1=$crmURL}You can edit the Name of this team here. Click <a %1>Players on this Team</a> to view, add or remove players from this team.{/ts}
  {else}
      {ts}Enter a unique name for your new team here and competition data below. Then click 'Continue' to find players to add to your new team.{/ts}
  {/if}
    </div>
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <table class="form-layout">
        <tr class="crm-group-form-block-title">
      <td class="label">{$form.title.label} {if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_group' field='title' id=$group.id}{/if}</td>
            <td>{$form.title.html|crmAddClass:huge}
                {if $group.saved_search_id}&nbsp;({ts}Smart Group{/ts}){/if}
            </td>
        </tr>

        {if $group.created_by}
        <tr class="crm-group-form-block-created">
           <td class="label">{ts}Created By{/ts}</td>
           <td>{$group.created_by}</td>
        </tr>
        {/if}

        {if $group.modified_by}
        <tr class="crm-group-form-block-modified">
           <td class="label">{ts}Modified By{/ts}</td>
           <td>{$group.modified_by}</td>
        </tr>
        {/if}
{* Probably don't need description for team
        <tr class="crm-group-form-block-description">
      <td class="label">{$form.description.label}</td>
      <td>{$form.description.html}<br />
    <span class="description">{ts}Group description is displayed when groups are listed in Profiles and Mailing List Subscribe forms.{/ts}</span>
            </td>
        </tr>
*}
  {if $form.group_type}
      <tr class="crm-group-form-block-group_type">
    <td class="label">{$form.group_type.label}</td>
    <td>{$form.group_type.html} {help id="id-group-type" file="CRM/Group/Page/Group.hlp"}</td>
      </tr>
  {/if}

{* Probably don't need these rows for team
        <tr class="crm-group-form-block-visibility">
      <td class="label">{$form.visibility.label}</td>
      <td>{$form.visibility.html|crmAddClass:huge} {help id="id-group-visibility" file="CRM/Group/Page/Group.hlp"}</td>
  </tr>

  <tr class="crm-group-form-block-isReserved">
    <td class="report-label">{$form.is_reserved.label}</td>
    <td>{$form.is_reserved.html}
      <span class="description">{ts}If reserved, only users with 'administer reserved groups' permission can disable, delete, or change settings for this group. The reserved flag does NOT affect users ability to add or remove contacts from a group.{/ts}</span>
    </td>
  </tr>
*}
  <tr>
      <td colspan=2>{include file="CRM/Custom/Form/CustomData.tpl"}</td>
  </tr>
    </table>

  {*CRM-14190*}
  {*include file="CRM/Group/Form/ParentGroups.tpl"*}

    {if $form.organization_id}
  <h3>{ts}Associated Organization{/ts} {help id="id-group-organization" file="CRM/Group/Page/Group.hlp"}</h3>
          <table class="form-layout-compressed">
        <tr class="crm-group-form-block-organization">
            <td class="label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$form.organization_id.label}</td>
      <td>{$form.organization_id.html|crmAddClass:huge}
      </td>
        </tr>
    </table>
    {/if}

    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    {if $action neq 1}
  <div class="action-link">
      <a {$crmURL}>&raquo; {ts}Players on this Team{/ts}</a>
      {if $group.saved_search_id}
          <br />
    {if $group.mapping_id}
        <a class="no-popup" href="{crmURL p="civicrm/contact/search/builder" q="reset=1&force=1&ssID=`$group.saved_search_id`"}">&raquo; {ts}Edit Smart Group Criteria{/ts}</a>
    {elseif $group.search_custom_id}
                    <a class="no-popup" href="{crmURL p="civicrm/contact/search/custom" q="reset=1&force=1&ssID=`$group.saved_search_id`"}">&raquo; {ts}Edit Smart Group Criteria{/ts}</a>
    {else}
        <a class="no-popup" href="{crmURL p="civicrm/contact/search/advanced" q="reset=1&force=1&ssID=`$group.saved_search_id`"}">&raquo; {ts}Edit Smart Group Criteria{/ts}</a>
    {/if}

      {/if}
  </div>
    {/if}
</fieldset>

{literal}
<script type="text/javascript">
{/literal}{if $freezeMailignList}{literal}
cj('input[type=checkbox][name="group_type[{/literal}{$freezeMailignList}{literal}]"]').prop('disabled',true);
{/literal}{/if}{literal}
{/literal}{if $hideMailignList}{literal}
cj('input[type=checkbox][name="group_type[{/literal}{$hideMailignList}{literal}]"]').hide();
cj('label[for="group_type[{/literal}{$hideMailignList}{literal}]"]').hide();
{/literal}{/if}{literal}
</script>
{/literal}
</div>
