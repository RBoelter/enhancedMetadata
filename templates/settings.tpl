<script>
	$(function () {ldelim}
		$('#enhancedMetadataSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        {rdelim});

	document.querySelectorAll('.checkNum').forEach(function (el) {ldelim}
		el.addEventListener("input", elem => el.value = (isNaN(el.value)) ? el.value.replace(elem.data, '') : el.value);
        {rdelim})
</script>
<form
		class="pkp_form"
		id="enhancedMetadataSettings"
		method="POST"
		enctype="multipart/form-data"
		action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
    {csrf}
    {fbvFormArea}
        {fbvFormSection title="plugins.generic.enhanced.head"}
            {fbvElement type="text" id="emTitle"  value=$emTitle label="plugins.generic.enhanced.head.desc"}
        {/fbvFormSection}
	    {fbvFormSection title="plugins.generic.enhanced.head"}
	        {fbvElement type="textarea" id="emFile"  value=$emFile label="plugins.generic.enhanced.head.desc"}
	    {/fbvFormSection}
    {/fbvFormArea}

    {fbvFormButtons submitText="common.save"}
</form>