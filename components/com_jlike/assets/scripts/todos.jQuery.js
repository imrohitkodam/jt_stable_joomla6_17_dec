/**
 * global: site_root
 * global: tdl
 * global: _
 */
(function( $ ) {

$.fn.jltodos = function(options){
	var defaults = {action:'',
		tempRender:[
		"<li data-jlike-todoid='<%= id %>'>",
			"<div class='clearfix'>",
				"<span class='col-xs-12 col-sm-8'>",
					"<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>",
					"&nbsp;<%= sender_msg %>",
				"</span>",
				"<span class='col-xs-12 col-sm-4'><%= formatDate(created) %></span>",
			"</div>",
		"</li>"],
		no_data_msg:"No record found."
	};

	var templates = {};

	// Merge options into defaults and also override default options if already exist in default
	 $.extend(defaults, options);

	templates.todo = "";

	if (defaults.tempRender != "")
	{
		templates.todo = (defaults.tempRender).join("");
	}

	if (defaults.action == "createTodo")
	{
		createTodo(defaults.obj);
	}
	else if (defaults.action == "deleteTodo")
	{
		deleteTodo(defaults.id);
	}
	else if (defaults.action == "renderTodos")
	{
		var element = $(this);
		renderTodos(defaults.obj, element);
	}
	else if (defaults.action == "init")
	{
		var element = $(this);
		init(defaults.obj, element);
	}
	else
	{
		return this.each(function(){
			var element = $(this);
			var dataSuccess = [];
			var obj= {};

			obj["url"]      = element.attr("data-jlike-url");
			obj["status"]   = element.attr("data-jlike-status");
			obj["type"]     = element.attr("data-jlike-type");
			obj["subtype"]  = element.attr("data-jlike-subtype");
			obj["client"]   = element.attr("data-jlike-client");
			obj["cont_id"]  = element.attr("data-jlike-cont-id");
			obj["title"]    = element.attr("data-jlike-title");
			var ordering    = element.attr("data-jlike-ordering");
			var direction   = element.attr("data-jlike-direction");

			var limit       = parseInt(element.attr("data-jlike-limit"), 10);
			var limitstart  = parseInt(element.attr("data-jlike-limitstart"), 10);

			init(obj, element);
		});
	}

	function init(obj, element)
	{
		jQuery.ajax({
			url: site_root + "index.php?option=com_api&app=jlike&resource=init&format=raw",
			headers: {
				'x-auth':'session'
			},
			type: "POST",
			data: obj,
			async:false,
			success:function(result){
				if (result.success == true)
				{
					element.attr("data-jlike-contentid", result.data.content_id);
					obj['content_id'] = result.data.content_id;
					//renderTodos(obj, element);
				}
			},
			error:function(){
				console.log("Error");
			}
		});
	}

	function createTodo(obj){
		jQuery.ajax({
			url: site_root + 'index.php?option=com_api&app=jlike&resource=todos&format=raw',
			headers: {
				'x-auth':'session'
			},
			data:obj,
			type: 'POST',
			success: function(data) {
				if(data.success == true){
					//jQuery('input[name="'+name+'"]').each(function(){
						//jQuery(this).attr("data-jlike-id", data.id);
					//});
				}
			},
			error: function(err) {
				console.log(err);
			}
		});
	}

	 function deleteTodo(id){
		jQuery.ajax({
			url: site_root + 'index.php?option=com_api&app=jlike&resource=todos&format=raw&id=' + id,
			headers: {
				'x-auth':'session'
			},
			type: 'DELETE',
			success: function(data) {
				tdl.renderAllTasks();
			},
		  error: function(err) {
			console.log(err);
			}
		});
	}

	function renderTodos(obj, addhtmlto)
	{
		jQuery.ajax({
			url: site_root + 'index.php?option=com_api&app=jlike&resource=todos&format=raw',
			headers: {
				'x-auth':'session'
			},
			type: 'GET',
			data:obj,
			async:false,
			beforeSend: function ()
			{
				//~ jQuery('#renderTodos').button('loading');
			},
			success: function(result)
			{
				var markup = null;

				if (result.success == true)
				{
					var rows = result['data']['result'];

					markup = "";

					if (rows != undefined)
					{
						var compiled = _.template(templates.todo);
						rows.forEach(function(item, idx, array){
							markup += compiled(item);
						});
					}
				}

				if (markup == null)
				{
					jQuery(element).html(defaults.no_data_msg);
				}
				else
				{
					jQuery(element).html(markup);
				}
			},
			error: function(err) {
				console.log(err);
			}
		});
	}
}
})( jQuery );

// @Hack/ Remove this hack
function formatDate(date)
{
	var t = date.split(/[- :]/);

	// t => ["2016", "06", "02", "14", "21", "15"]
	// Changed date for dd/mm/yyyy
	var formatedDate =  t[2] + '/' + t[1] + '/' + t[0];

	return formatedDate;
}
