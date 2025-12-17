var jlike = {};

var jlikeTodos = {};

jQuery(document).ready(function(){
	jlikeTodos.toDos();
});

jlike = {
	init:function (obj)
	{
		var res;

		jQuery.ajax({
			url: root_url + "index.php?option=com_api&app=jlike&resource=init&key=ed086fefc3b111c666378912f44d71ca0a70a8b6&format=raw",
			type: "POST",
			async:false,
			data: obj,
			success:function(result){
				res = result;
			},
			error:function(){
			}
		});

		return res;
	}
}

jlikeTodos = {
	toDos:function ()
	{
		var obj = {};
		jQuery('div[data-jlike-type="todos"]').each(function(){
			var this_container = jQuery(this);
			var obj={"url":jQuery(this_container).attr("data-jlike-url")};
			obj["type"]=jQuery(this_container).attr("data-jlike-type");
			obj["subtype"]=jQuery(this_container).attr("data-jlike-subtype");
			obj["client"]=jQuery(this_container).attr("data-jlike-client");
			obj["cont_id"]=jQuery(this_container).attr("data-jlike-cont-id");
			obj["title"]=jQuery(this_container).attr("data-jlike-title");

			var result = jlike.init(obj);

			jQuery(this_container).attr("data-content-id", result['data']['content_Id']);

			var todos = jlikeTodos.getTodos(this_container);

			jlikeTodos.renderToDos(todos);
		});

	},
	getTodos:function (this_container)
	{
		var res;

		var obj={"content_id":jQuery(this_container).attr("data-content-id")};
		obj["type"]=jQuery(this_container).attr("data-jlike-type");
		obj["subtype"]=jQuery(this_container).attr("data-jlike-subtype");
		obj["client"]=jQuery(this_container).attr("data-jlike-client");
		obj["limit"]=jQuery(this_container).attr("data-jlike-limit");
		obj["limitstart"]=jQuery(this_container).attr("data-jlike-limitstart");

		jQuery.ajax({
			url: root_url + "index.php?option=com_api&app=jlike&resource=todos&key=ed086fefc3b111c666378912f44d71ca0a70a8b6&format=raw",
			type: "POST",
			async:false,
			data: obj,
			success:function(result){
				res = result;
			},
			error:function(){
			}
		});

		return res;
	},
	renderToDos:function (todos){
		//~ console.log(todos);
	},
	saveToDo:function (todos){
		//~ console.log(1)
	}
}
