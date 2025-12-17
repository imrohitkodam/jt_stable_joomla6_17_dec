/**
 * global: root_url
 */
var jlike = {};

jQuery(document).ready(function (){
	jlike.comments('public');
	jQuery("#collaborators").click(function() {
		jlike.comments('collaborator');
	});

	jQuery("#reviewers").click(function() {
		jlike.comments('reviewer');
	});

	/*jlike.comments('collaborator');
	jQuery('.save').click(function() {
		var type       = jQuery(this).closest( ".jquery-comments" ).attr("data-jlike-subtype");
		var id         = jQuery(this).closest( ".jquery-comments" ).attr("data-content-id");
		var client     = jQuery(this).closest( ".jquery-comments" ).attr("data-jlike-client");
		var a = jQuery(this).closest( ".jquery-comments" );

		jQuery(a).comments({
			postCommentOnEnter : true,
				enableReplying		: true,
				enableEditing : true,

			postComment: function(commentJSON, success, error) {
					jlike.postComment(commentJSON, success, error, id, type, client);
				}
		});
	});*/
});

jlike = {
	comments:function (type)
	{
		var obj = {};
		jQuery('div[ data-jlike-subtype='+type+']').each(function(){
			var this_container = jQuery(this);
			var obj={"url":jQuery(this_container).attr("data-jlike-url")};
			obj["type"]=jQuery(this_container).attr("data-jlike-type");
			obj["subtype"]=jQuery(this_container).attr("data-jlike-subtype");
			obj["client"]=jQuery(this_container).attr("data-jlike-client");
			obj["cont_id"]=jQuery(this_container).attr("data-jlike-cont-id");
			obj["title"]=jQuery(this_container).attr("data-jlike-title");
			obj["userId"]=jQuery(this_container).attr("data-jlike-userId");

			var result = jlike.init(obj);

			jQuery(this_container).attr("data-content-id", result.data.content_Id);

			jQuery(this_container).comments({

				postCommentOnEnter : true,
				enableReplying		: false,
				enableEditing : true,
				enableUpvoting: false,
				enableDeleting: true,
				//enableDeletingCommentWithReplies:true,
				//currentUserIsAdmin: true,



				fieldMappings: {
					id: 'id',
					//parent: 'parent_id',
					content: 'annotation',
					created: 'annotation_date',
					//modified:'annotation_date',
				},
				timeFormatter: function(time) {
					return moment(time).fromNow();
					//return moment(time).format('HH:mm:ss');

				},

				profilePictureURL: 'https://app.viima.com/static/media/user_profiles/user-icon.png',

				getComments: function(success, error) {
					jlike.getComments(success, error, result.data.content_Id, obj["subtype"], obj["client"], obj["userId"]);
				},
				postComment: function(commentJSON, success, error) {
					jlike.postComment(commentJSON, success, error, result.data.content_Id, obj["subtype"], obj["client"]);
					jlike.comments(type);
				},
				deleteComment: function(commentJSON, success, error) {
					jlike.deleteComment(commentJSON, success, error, result.data.content_Id,  obj["subtype"], obj["client"]);
				},
				putComment: function(commentJSON, success, error) {
					jlike.putComment(commentJSON, success, error, result.data.content_Id,  obj["subtype"], obj["client"]);
				}
			});
		});
	},
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
	},
	getComments:function (success, error, content_Id, subtype, client, userId)
	{
		jQuery.ajax({
			url: root_url + "index.php?option=com_api&app=jlike&resource=annotations&key=ed086fefc3b111c666378912f44d71ca0a70a8b6&format=raw",
			type: "GET",
			data : {content_id : content_Id, subtype, client},
			success:function(result) {
				if (!result.data)
				{
					success([]);
				}
				else
				{
					var dataresultarray = result.data.results;

					for (var index = 0; index < dataresultarray.length; ++index) {
							if(dataresultarray[index].user.id == userId)
							{
								dataresultarray[index].created_by_current_user=true;
							}
							else{
								dataresultarray[index].fullname = dataresultarray[index].user.name;
							}
							if(dataresultarray[index].parent_id)
							{
								dataresultarray[index].parent=dataresultarray[index].parent;
							}
							else{
								dataresultarray[index].parent=null;
							}
					}
					//console.log('outfor'+dataresultarray);
					//alert(a[0].id);
					//var dataresultarray = dataresultarray;
					success(dataresultarray);
				}


			}
		});

	},
	postComment: function(commentJSON, success, error, content_Id, subtype, client) {
		commentJSON.content_id = content_Id;
		commentJSON.subtype = subtype;
		commentJSON.client = client;
		jQuery.ajax({
			type: 'POST',
			url: root_url + "index.php?option=com_api&app=jlike&resource=annotations&key=ed086fefc3b111c666378912f44d71ca0a70a8b6&format=raw",
			data: commentJSON,
			success: function(comment) {
				success(comment.data.results);
			},
			error: error
		});
	},
	putComment: function(commentJSON, success, error, content_Id, subtype, client) {
		commentJSON.content_id = content_Id;
		//commentJSON.parent_id = content_Id;
		commentJSON.subtype = subtype;
		commentJSON.client = client;
		jQuery.ajax({
			type: 'POST',
			url: root_url + "index.php?option=com_api&app=jlike&resource=annotations&key=ed086fefc3b111c666378912f44d71ca0a70a8b6&format=raw&annotation_id="+commentJSON.id,
			data: commentJSON,
			success: function(comment) {
				var dataresultarray = comment.data.results;
				success(dataresultarray);
			},
			error: error
		});
	},
	deleteComment: function(commentJSON, success, error, content_Id, subtype, client) {
		commentJSON.content_id = content_Id;
		//commentJSON.parent_id = content_Id;
		commentJSON.subtype = subtype;
		commentJSON.client = client;
		jQuery.ajax({
			type: 'delete',
			url: root_url + "index.php?option=com_api&app=jlike&resource=annotations&key=ed086fefc3b111c666378912f44d71ca0a70a8b6&format=raw&id="+commentJSON.id,
			data: commentJSON,
			success: function(comment) {
				success(comment);
			},
			error: error
		});
	}


}
