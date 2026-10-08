{**
 * templates/settingsForm.tpl
 * Settings for the AI Transparency Declaration plugin.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		$('#aiTransparencyDeclarationSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="aiTransparencyDeclarationSettingsForm" method="post" action="{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT op="manage" plugin="aitransparencydeclarationplugin" category="generic" verb="save"}">
	{csrf}
	{fbvFormArea id="aiTransparencyDeclarationSettingsFormArea"}
		<p class="pkp_help">{translate key="plugins.generic.aiTransparencyDeclaration.settings.description"}</p>
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="requireDeclaration" label="plugins.generic.aiTransparencyDeclaration.settings.requireDeclaration" checked=$requireDeclaration|compare:true}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
