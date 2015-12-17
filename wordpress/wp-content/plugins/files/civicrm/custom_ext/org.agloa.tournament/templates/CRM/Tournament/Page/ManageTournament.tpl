{capture assign=newEventURL}{crmURL p='civicrm/tournament/add' q="action=add&reset=1"}{/capture}
{capture assign=icalFile}{crmURL p='civicrm/tournament/ical' q="reset=1" fe=1}{/capture}
{capture assign=icalFeed}{crmURL p='civicrm/tournament/ical' q="reset=1&list=1" fe=1}{/capture}
{capture assign=rssFeed}{crmURL p='civicrm/tournament/ical' q="reset=1&list=1&rss=1" fe=1}{/capture}
{capture assign=htmlFeed}{crmURL p='civicrm/tournament/ical' q="reset=1&list=1&html=1" fe=1}{/capture}
<div class="float-right">
  <a href="{$htmlFeed}" target="_blank" title="{ts}HTML listing of current and future public tournaments.{/ts}">
    <img src="{$config->resourceBase}i/applications-internet.png"
         alt="{ts}HTML listing of current and future public tournaments.{/ts}" />
  </a>&nbsp;&nbsp;
  <a href="{$rssFeed}" target="_blank" title="{ts}Get RSS 2.0 feed for current and future public tournaments.{/ts}">
    <img src="{$config->resourceBase}i/feed-icon.png"
         alt="{ts}Get RSS 2.0 feed for current and future public tournaments.{/ts}" />
  </a>&nbsp;&nbsp;
  <a href="{$icalFile}" title="{ts}Download iCalendar file for current and future public tournaments.{/ts}">
    <img src="{$config->resourceBase}i/office-calendar.png"
         alt="{ts}Download iCalendar file for current and future public tournaments.{/ts}" />
  </a>&nbsp;&nbsp;
  <a href="{$icalFeed}" target="_blank" title="{ts}Get iCalendar feed for current and future public tournaments.{/ts}">
    <img src="{$config->resourceBase}i/ical_feed.gif"
         alt="{ts}Get iCalendar feed for current and future public tournaments.{/ts}" />
  </a>&nbsp;&nbsp;&nbsp;{help id='icalendar'}
</div>
{include file="CRM/Event/Form/SearchEvent.tpl"}

<div class="action-link">
  <a accesskey="N" href="{$newEventURL}" id="newManageEvent" class="button crm-popup">
    <span><div class="icon ui-icon-circle-plus"></div>{ts}Add Event{/ts}</span>
  </a>
  <div class="clear"></div>
</div>
{if $rows}
<div id="event_status_id" class="crm-block crm-manage-events">
  {strip}
  {include file="CRM/common/pager.tpl" location="top"}
  {include file="CRM/common/pagerAToZ.tpl"}
  {* handle enable/disable actions*}
  {include file="CRM/common/enableDisableApi.tpl"}
  {include file="CRM/common/jsortable.tpl"}
    <table id="options" class="display">
      <thead>
      <tr>
        <th>{ts}Event{/ts}</th>
        <th>{ts}City{/ts}</th>
        <th>{ts}State/Province{/ts}</th>
        <th>{ts}Type{/ts}</th>
        <th>{ts}Public?{/ts}</th>
        <th>{ts}Starts{/ts}</th>
        <th>{ts}Ends{/ts}</th>
        {if call_user_func(array('CRM_Campaign_BAO_Campaign','isCampaignEnable'))}
          <th>{ts}Campaign{/ts}</th>
        {/if}
        <th>{ts}Active?{/ts}</th>
        <th></th>
        <th class="hiddenElement"></th>
        <th class="hiddenElement"></th>
      </tr>
      </thead>
      {foreach from=$rows key=keys item=row}
        {if $keys neq 'tab'}
          <tr id="event-{$row.id}" class="crm-entity {if NOT $row.is_active} disabled{/if}">
          <td class="crm-event_{$row.id}">
            <a href="{crmURL p='civicrm/tournament/info' q="id=`$row.id`&reset=1"}"
               title="{ts}View event info page{/ts}" class="bold">{$row.title}</a>&nbsp;&nbsp;({ts}ID:{/ts} {$row.id})<br/>
               <span><b>{$row.repeat}</b></span>
          </td>
          <td class="crm-event-city">{$row.city}</td>
          <td class="crm-event-state_province">{$row.state_province}</td>
          <td class="crm-event-event_type">{$row.event_type}</td>
          <td class="crm-event-is_public">{if $row.is_public eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
          <td class="crm-event-start_date">{$row.start_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
          <td class="crm-event-end_date">{$row.end_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
          {if call_user_func(array('CRM_Campaign_BAO_Campaign','isCampaignEnable'))}
            <td class="crm-event-campaign">{$row.campaign}</td>
          {/if}
          <td class="crm-event_status" id="row_{$row.id}_status">
            {if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}
          </td>
          <td class="crm-event-actions right nowrap">
            <div class="crm-configure-actions">
              <span id="event-configure-{$row.id}" class="btn-slide crm-hover-button">{ts}Configure{/ts}
                <ul class="panel" id="panel_info_{$row.id}">
                  {foreach from=$rows.tab key=k item=v}
                    {assign var="fld" value=$v.field}
                    {if NOT $row.$fld}{assign var="status" value="disabled"}{else}{assign var="status" value="enabled"}{/if}
                      {if $k eq 'reminder'}
                        <li><a title="{$v.title}" class="action-item crm-hover-button {$status}"
                           href="{crmURL p="`$v.url`" q="reset=1&action=browse&setTab=1&id=`$row.id`"}">{$v.title}</a>
                      {else}
                        <li><a title="{$v.title}" class="action-item crm-hover-button {$status}"
                           href="{crmURL p="`$v.url`" q="reset=1&action=update&id=`$row.id`"}">{$v.title}</a></li>
                      {/if}
                  {/foreach}
                </ul>
              </span>
            </div>

            <div class=crm-event-participants>
              <span id="event-participants-{$row.id}" class="btn-slide crm-hover-button">{ts}Participants{/ts}
                <ul class="panel" id="panel_participants_{$row.id}">
                  {if $findParticipants.statusCounted}
                    <li>
                      <a title="Counted" class="action-item crm-hover-button" href="{crmURL p='civicrm/tournament/search'
                      q="reset=1&force=1&status=true&event=`$row.id`"}">{$findParticipants.statusCounted}
                      </a>
                    </li>
                  {/if}
                  {if $findParticipants.statusNotCounted}
                    <li>
                      <a title="Not Counted" class="action-item crm-hover-button"
                           href="{crmURL p='civicrm/tournament/search'
                           q="reset=1&force=1&status=false&event=`$row.id`"}">{$findParticipants.statusNotCounted}
                      </a>
                    </li>
                  {/if}
                  {if $row.participant_listing_id}
                    <li>
                      <a title="Public Participant Listing" class="action-item crm-hover-button"
                         href="{crmURL p='civicrm/tournament/participant' q="reset=1&id=`$row.id`"
                         fe='true'}">{ts}Public Participant Listing{/ts}
                      </a>
                    </li>
                  {/if}
                </ul>
              </span>
            </div>

            <div class="crm-event-links">
              <span id="event-links-{$row.id}" class="btn-slide crm-hover-button">{ts}Event Links{/ts}
                <ul class="panel" id="panel_links_{$row.id}">
                  <li>
                    <a title="Register Participant" class="action-item crm-hover-button" href="{crmURL p='civicrm/participant/add'
                    q="reset=1&action=add&context=standalone&eid=`$row.id`"}">{ts}Register Participant{/ts}</a>
                  </li>
                  <li>
                    <a title="Event Info" class="action-item crm-hover-button" href="{crmURL p='civicrm/tournament/info'
                    q="reset=1&id=`$row.id`" fe='true'}" target="_blank">{ts}Event Info{/ts}
                    </a>
                  </li>
                  {if $row.is_online_registration}
                    <li>
                      <a title="Online Registration (Test-drive)" class="action-item crm-hover-button"
                         href="{crmURL p='civicrm/tournament/register'
                         q="reset=1&action=preview&id=`$row.id`"}">{ts}Registration (Test-drive){/ts}
                      </a>
                    </li>
                    <li>
                      <a title="Online Registration (Live)" class="action-item crm-hover-button" href="{crmURL p='civicrm/tournament/register'
                      q="reset=1&id=`$row.id`" fe='true'}" target="_blank">{ts}Registration (Live){/ts}
                      </a>
                    </li>
                  {/if}
                </ul>
              </span>
            </div>
            <div class="crm-event-more">
              {$row.action|replace:'xx':$row.id}
            </div>
          </td>
          <td class="crm-event-start_date hiddenElement">{$row.start_date|crmDate}</td>
          <td class="crm-event-end_date hiddenElement">{$row.end_date|crmDate}</td>
        </tr>
        {/if}
      {/foreach}
    </table>
  {include file="CRM/common/pager.tpl" location="bottom"}
  {/strip}
  {if $isSearch eq 0}
    <div class="status messages no-popup">{ts}Don't see your event listed? Try "Search All or by Date Range" above.{/ts}</div>
  {/if}
</div>
{else}
  {if $isSearch eq 1}
  <div class="status messages">
    <div class="icon inform-icon"></div>
    {capture assign=browseURL}{crmURL p='civicrm/tournament/manage' q="reset=1"}{/capture}
    {ts}No available Tournaments match your search criteria. Suggestions:{/ts}
    <div class="spacer"></div>
    <ul>
      <li>{ts}Check your spelling.{/ts}</li>
      <li>{ts}Try "Search All or by Date Range".{/ts}</li>
      <li>{ts}Try a different spelling or use fewer letters.{/ts}</li>
      <li>{ts}Make sure you have enough privileges in the access control system.{/ts}</li>
    </ul>
    {ts 1=$browseURL}Or you can <a href='%1'>browse all available Current Tournaments</a>.{/ts}
  </div>
    {else}
  <div class="messages status no-popup">
    <div class="icon inform-icon"></div>
    {ts 1=$newEventURL}There are no tournaments scheduled for the date range. You can <a href='%1'>add one</a>.{/ts}
  </div>
  {/if}
{/if}