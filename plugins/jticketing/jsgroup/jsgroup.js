var jsGroup = {
	enrolledUsers: 0,
	groups: 0,
	init: function (){
		const thisenrolledUsers = this.enrolledUsers;
		const thisgroups = this.groups;

		jQuery(document).ready(function()
		{
			jQuery("#grpCategoriesField").hide();
			jQuery('#jform_params_jsgroup_groupCategory-lbl').hide();
			jQuery("#jform_params_jsgroup_eventgroup").click(function()
			{
				if (jQuery("#jform_params_jsgroup_eventgroup1").is(":checked"))
				{
					jQuery("#grpCategoriesField").show();
					jQuery('#jform_params_jsgroup_groupCategory-lbl').show();
					jQuery('#jform_params_jsgroup_onAfterEnrollJsGroups_chzn').hide();
					jQuery('#jform_params_jsgroup_onAfterEnrollJsGroups-lbl').hide();
				}
				else
				{
					jQuery("#grpCategoriesField").hide();
					jQuery('#jform_params_jsgroup_groupCategory-lbl').hide();
					jQuery('#jform_params_jsgroup_onAfterEnrollJsGroups_chzn').show();
					jQuery('#jform_params_jsgroup_onAfterEnrollJsGroups-lbl').show();
				}
			});

			if (thisenrolledUsers >= 1 && thisgroups)
			{
				jQuery("#jform_params_jsgroup_eventgroup").hide();
				jQuery("#jform_params_jsgroup_eventgroup-lbl").hide();
				jQuery("#jform_params_jsgroup_onAfterEnrollJsGroups").attr("disabled", true);
			}
		});
	}
}
