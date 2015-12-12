{* Displays Registration Control Panel *}
<div id="help" class="description section-hidden-border">
{capture assign=plusImg}<img src="{$config->resourceBase}i/TreePlus.gif" alt="{ts}plus sign{/ts}" style="vertical-align: bottom; height: 20px; width: 20px;" />{/capture}
{ts 1=$plusImg}Administer your organization's tournament registration using the links on this page. Click %1 for descriptions of the options in each section.{/ts}
</div>
{strip}
<div class="crm-content-block">
<h3>Register tournament participants for: {$billing_contact->tournament->event->title}</h3>

<h4>Welcome <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->contact->id}" target="_blank">{$billing_contact->contact->display_name}</a>.</h4>
<ul>
<li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->contact->id}" target="_blank">your contact information</a>.</li>
<li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view&reset=1&cid={$billing_contact->organization->id}" target="_blank">your organization's contact information</a> ({$billing_contact->organization->display_name}).</li>
<!--li>Update <a href="admin.php?page=CiviCRM&q=civicrm/contact/view/rel&reset=1&cid=1004" target="_blank">Ann Arbor participants</a>.</li-->
<li>Register participants for <a href="admin.php?page=CiviCRM&q=civicrm/event/register&reset=1&id={$billing_contact->tournament->event->id}" target="_blank">{$billing_contact->tournament->event->title}</a>.</li>
</ul>
{/strip}
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/bower_components/jquery/dist/jquery.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/bower_components/jquery-ui/jquery-ui.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/bower_components/lodash-compat/lodash.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.mousewheel.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/bower_components/select2/select2.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.tableHeader.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.textarearesizer.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.form.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.timeentry.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.blockUI.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/DataTables/media/js/jquery.dataTables.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/bower_components/jquery-validation/dist/jquery.validate.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.ui.datepicker.validation.pack.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/js/Common.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/js/crm.ajax.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.menu.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.jeditable.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/jquery.notify.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/js/jquery/jquery.crmeditable.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/js/crm.wordpress.js?r=ZRYdC">
</script>
<script type="text/javascript" src="/wp-admin/admin.php?page=CiviCRM&amp;q=civicrm/ajax/l10n-js/en_US&amp;r=ZRYdC">
</script>
<link href="/wp-content/plugins/civicrm/civicrm/bower_components/jquery-ui/themes/smoothness/jquery-ui.css?r=ZRYdC" rel="stylesheet" type="text/css"/>
<link href="/wp-content/plugins/civicrm/civicrm/bower_components/select2/select2.css?r=ZRYdC" rel="stylesheet" type="text/css"/>
<link href="/wp-content/plugins/civicrm/civicrm/css/civicrmNavigation.css?r=ZRYdC" rel="stylesheet" type="text/css"/>
<link href="/wp-content/plugins/civicrm/civicrm/packages/jquery/plugins/DataTables/media/css/jquery.dataTables.css?r=ZRYdC" rel="stylesheet" type="text/css"/>
<link href="/wp-content/plugins/civicrm/civicrm/css/civicrm.css?r=ZRYdC" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="/wp-content/plugins/civicrm/civicrm/js/noconflict.js?r=ZRYdC">
</script>

<form  action="http://agloa.dev/wp-admin/admin.php/?page=CiviCRM&amp;q=civicrm/profile/create&amp;gid=16&amp;reset=1" method="post" name="Edit" id="Edit" class="CRM_Profile_Form_Edit" >
  <div><input name="entryURL" type="hidden" value="http://agloa.dev/wp-admin/admin.php/?page=CiviCRM&amp;amp;q=civicrm/profile/create&amp;gid=16&amp;reset=1/field/add&amp;amp;page=CiviCRM&amp;amp;reset=1&amp;amp;action=add&amp;amp;gid=16" />
<input name="gid" type="hidden" value="16" />
<input name="_qf_default" type="hidden" value="Edit:next" />
</div>  

    <div class="crm-profile-name-Billing_Contacts_16">


    <div id="crm-container" class="crm-container crm-public" lang="en" xml:lang="en">
  
      <div id="editrow-email-Primary" class="crm-section editrow_email-Primary-section form-item"><div class="label"><label for="email-Primary">  Email
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input maxlength="254" size="20" name="email-Primary" type="text" id="email-Primary" class="medium crm-form-text required" /></div><div class="clear"></div></div><div id="editrow-last_name" class="crm-section editrow_last_name-section form-item"><div class="label"><label for="last_name">  Last Name
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input maxlength="64" size="30" name="last_name" type="text" id="last_name" class="big crm-form-text required" /></div><div class="clear"></div></div><div id="editrow-first_name" class="crm-section editrow_first_name-section form-item"><div class="label"><label for="first_name">  First Name
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input maxlength="64" size="30" name="first_name" type="text" id="first_name" class="big crm-form-text required" /></div><div class="clear"></div></div><div id="editrow-middle_name" class="crm-section editrow_middle_name-section form-item"><div class="label"><label for="middle_name">Middle Name</label></div><div class="edit-value content"><input maxlength="64" size="20" name="middle_name" type="text" id="middle_name" class="medium crm-form-text" /></div><div class="clear"></div></div><div id="editrow-prefix_id" class="crm-section editrow_prefix_id-section form-item"><div class="label"><label for="prefix_id">Individual Prefix</label></div><div class="edit-value content"><select class="six crm-select2 crm-form-select" placeholder="" data-option-edit-path="civicrm/admin/options/individual_prefix" data-api-entity="contact" data-api-field="prefix_id" name="prefix_id" id="prefix_id">
	<option value=""></option>
	<option value="1">Mrs.</option>
	<option value="2">Ms.</option>
	<option value="3">Mr.</option>
	<option value="4">Dr.</option>
	<option value="5">Br.</option>
</select> <a href="http://agloa.dev/wp-admin/admin.php/?page=CiviCRM&amp;q=civicrm/admin/options/individual_prefix&amp;reset=1" class="crm-option-edit-link medium-popup crm-hover-button" target="_blank" title="Edit Options" data-option-edit-path="civicrm/admin/options/individual_prefix"><span class="icon ui-icon-wrench"></span></a></div><div class="clear"></div></div><div id="editrow-suffix_id" class="crm-section editrow_suffix_id-section form-item"><div class="label"><label for="suffix_id">Individual Suffix</label></div><div class="edit-value content"><select class="six crm-select2 crm-form-select" placeholder="" data-option-edit-path="civicrm/admin/options/individual_suffix" data-api-entity="contact" data-api-field="suffix_id" name="suffix_id" id="suffix_id">
	<option value=""></option>
	<option value="1">Jr.</option>
	<option value="2">Sr.</option>
	<option value="3">II</option>
	<option value="4">III</option>
	<option value="5">IV</option>
	<option value="6">V</option>
	<option value="7">VI</option>
	<option value="8">VII</option>
</select> <a href="http://agloa.dev/wp-admin/admin.php/?page=CiviCRM&amp;q=civicrm/admin/options/individual_suffix&amp;reset=1" class="crm-option-edit-link medium-popup crm-hover-button" target="_blank" title="Edit Options" data-option-edit-path="civicrm/admin/options/individual_suffix"><span class="icon ui-icon-wrench"></span></a></div><div class="clear"></div></div><div id="editrow-phone-Primary-1" class="crm-section editrow_phone-Primary-1-section form-item"><div class="label"><label for="phone-Primary-1">  Phone and Extension
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input name="phone-Primary-1" type="text" id="phone-Primary-1" class="crm-form-text required" />&nbsp;<input name="phone_ext-Primary-1" type="text" id="phone_ext-Primary-1" class="crm-form-text" /></div><div class="clear"></div></div><div id="editrow-phone-2-3" class="crm-section editrow_phone-2-3-section form-item"><div class="label"><label for="phone-2-3">Fax</label></div><div class="edit-value content"><input maxlength="32" size="20" name="phone-2-3" type="text" id="phone-2-3" class="medium crm-form-text" /></div><div class="clear"></div></div><div id="editrow-street_address-Primary" class="crm-section editrow_street_address-Primary-section form-item"><div class="label"><label for="street_address-Primary">  Address
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input maxlength="96" size="45" name="street_address-Primary" type="text" id="street_address-Primary" class="huge crm-form-text required" /></div><div class="clear"></div></div><div id="editrow-supplemental_address_1-Primary" class="crm-section editrow_supplemental_address_1-Primary-section form-item"><div class="label"></div><div class="edit-value content"><input maxlength="96" size="45" name="supplemental_address_1-Primary" type="text" id="supplemental_address_1-Primary" class="huge crm-form-text" /></div><div class="clear"></div></div><div id="editrow-supplemental_address_2-Primary" class="crm-section editrow_supplemental_address_2-Primary-section form-item"><div class="label"></div><div class="edit-value content"><input maxlength="96" size="45" name="supplemental_address_2-Primary" type="text" id="supplemental_address_2-Primary" class="huge crm-form-text" /></div><div class="clear"></div></div><div id="editrow-city-Primary" class="crm-section editrow_city-Primary-section form-item"><div class="label"><label for="city-Primary">  City
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input maxlength="64" size="30" name="city-Primary" type="text" id="city-Primary" class="big crm-form-text required" /></div><div class="clear"></div></div><div id="editrow-country-Primary" class="crm-section editrow_country-Primary-section form-item"><div class="label"><label for="country-Primary">  Country
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><select class="crm-select2 crm-chain-select-control crm-form-select required" placeholder="- select -" name="country-Primary" data-target="state_province-Primary" id="country-Primary">
	<option value="">- select -</option>
	<option value="1228" selected="selected">United States</option>
	<option value="1101">India</option>
	<option value="1208">Taiwan</option>
</select></div><div class="clear"></div></div><div id="editrow-state_province-Primary" class="crm-section editrow_state_province-Primary-section form-item"><div class="label"><label for="state_province-Primary">  State
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><select data-callback="civicrm/ajax/jqState" data-empty-prompt="Choose country first" data-none-prompt="- N/A -" class="crm-select2 crm-chain-select-target crm-form-select required" data-select-prompt="- select -" data-name="state_province-Primary" name="state_province-Primary" id="state_province-Primary">
	<option value="">- N/A -</option>
</select></div><div class="clear"></div></div><div id="editrow-postal_code-Primary" class="crm-section editrow_postal_code-Primary-section form-item"><div class="label"><label for="postal_code-Primary">  Postal Code
     <span class="crm-marker" title="This field is required.">*</span>

</label></div><div class="edit-value content"><input maxlength="12" size="12" name="postal_code-Primary" type="text" id="postal_code-Primary" class="twelve crm-form-text required" /></div><div class="clear"></div></div><div id="editrow-postal_code_suffix-Primary" class="crm-section editrow_postal_code_suffix-Primary-section form-item"><div class="label"><label for="postal_code_suffix-Primary">Postal Code Suffix</label></div><div class="edit-value content"><input maxlength="12" size="12" name="postal_code_suffix-Primary" type="text" id="postal_code_suffix-Primary" class="twelve crm-form-text" /></div><div class="clear"></div></div><div class="crm-submit-buttons" style=''>                                                                                              
        
        <span class="crm-button crm-button-type-next crm-button_qf_Edit_next crm-icon-button">
          <span class="crm-button-icon ui-icon-check"> </span>          <input class="crm-form-submit default validate" accesskey="S" crm-icon="check" name="_qf_Edit_next" value="Save" type="submit" id="_qf_Edit_next" />
        </span>
      <a class="button cancel" href="http://agloa.dev/wp-admin/admin.php/?page=CiviCRM&q=civicrm/profile&reset=1&gid=16">Cancel</a></div>

</div> 
</div> 
</form>

{* Include Javascript to hide and display the appropriate blocks as directed by the php code *}
{include file="CRM/common/showHide.tpl"}
</div>