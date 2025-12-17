/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 **/
var hierarchyAdmin =
{
	hierarchy: {
		/** Initialize hierarchy js **/
		initHierarchyJs: function() {
			jQuery(document).ready(function() {
				if (client){
					jQuery('#jform_context').prop('readonly', true);
				}
				if (clientID){
					jQuery('#jform_context_id').prop('readonly', true);
				}
			});
			Joomla.submitbutton = function (task) {
				if (task == 'hierarchy.apply' && document.formvalidator.isValid(document.id('hierarchy-form'))) {
					Joomla.submitform(task, document.getElementById('hierarchy-form'));
				}
				else {
					alert(Joomla.JText._('JGLOBAL_VALIDATION_FORM_FAILED'));
					return false;
				}
				parent.location.reload();
			}
		},

		getAutoSuggestUsers: function() {
			/** Invite user field tokenfield **/
			inviteTaskUrl = UriRoot + 'index.php?option=com_hierarchy&task=hierarchy.getAutoSuggestUsers&user_id=' + userID;

			jQuery('#jform_reports_to').tokenize({
				placeholder: Joomla.JText._('COM_HIERARCHY_USERNAMES_DESC'),
				newElements: false,
				searchMinLength: 1,
				datas: inviteTaskUrl
			});
		}
	},

	hierarchys: {
		/** Initialize hierarchys js **/
		initHierarchysJs: function() {
			Joomla.submitbutton = function (task) {
			if (task =='hierarchys.remove') {
				var result = confirm(Joomla.JText._('COM_HIERARCHY_HIERARCHY_DELETE_CONF'));
				if (result != true) {
					return false;
				}
				Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},
		showUserNames: function() {
			var maxRow = jQuery(".reports_to").length;
			for(var i = 0; i < maxRow ; i++) {
				jQuery("#popover_"+i).popover({ trigger: "hover" });
			}
		}
	}
}

var hierarchySite =
{
	hierarchys: {
		showUserNames: function() {
			var maxRow = jQuery(".reports_to").length;
			for(var i = 0; i < maxRow ; i++) {
				jQuery("#popover_"+i).popover({ trigger: "hover" });
			}
		},

		drillUpDrillDownList: function(userID) {
			reportsUrl = UriRoot + 'index.php?option=com_hierarchy&task=hierarchys.getReportsTo&user_id=' + userID;
			jQuery.ajax({
				type:'POST',
				url:reportsUrl,
				data:{},
				dataType: 'json',
				success:function(data)
				{
					jQuery.each(data, function (dataKey, dataVal) {
						var reportingToNames = new Array();
						jQuery.each(dataVal.also, function(index, value) {
							reportingToNames.push(value.reportsToName);
						});

						/*@TODO add this for reference
						jQuery('#row_'+userID).after('<tr id="row_'+dataVal.user_id+'"><td><i class="fa fa-chevron-right" aria-hidden="true"></i> <img src="' + gravatar + '" class="img-rounded" alt="" width="30" height="30"><a href="#" >' + dataVal.name + ' <i class="fa fa-angle-down" onclick="hierarchySite.hierarchys.drillUpDrillDownList(' + dataVal.user_id + ')"; aria-hidden="true"></i></td><td>' + dataVal.context + '</td><td>' + dataVal.context_id + '</td><td><span id="popover" data-content="' +reportingToNames +'">'+ reportingToNames +'</span></td><td>' + dataVal.user_id + '</td></tr>');*/

						jQuery('#row_'+ userID).after('<tr><td><i class="fa fa-chevron-right" aria-hidden="true"></i> <img src="' + gravatar + '" class="img-rounded" alt="" width="30" height="30"> ' + dataVal.name + '</td><td>' + dataVal.context + '</td><td>' + dataVal.context_id + '</td><td><span id="popover" data-content="' +reportingToNames +'">'+ reportingToNames +'</span></td><td>' + dataVal.user_id + '</td></tr>');

						jQuery('#click_off_'+userID).prop('onclick',null).off('click');
						jQuery("#popover").popover({ trigger: "hover" });
					});
				}
			});
		},

		displayHierarchyChart: function ()
		{
			hierarchy_chart_config = {
				chart: {
					container: "#hierarchy_chart",
					connectors: {
						type: 'step'
					},
					node:{
						collapsable:true
					}
				},
				nodeStructure: {
					text: { name: userName },
					image: gravatar,
					children: childrenArrayObject
				}
			};

			var my_chart = new Treant(hierarchy_chart_config);
		}
	}
}
