(function( $ ) {
$.fn.likes = function(options){

	var defaults = {
		tempRender:[
		"<button id='likes'>",
		"<i class='fa fa-thumbs-up'></i>",
		"<span class='like-snippet'>",
			"<span class='like-snippet-text likecount='<%= id %>'></span>",
			"&nbsp;<%= total_likes %>",
		"</button></span>"]
	};

	var templates = {};

	var tdl = {
		initEvents: function() {
			jQuery('#likes').on('click', function(){
				var buttonclosest = jQuery(this).closest('div[data-jlike-type="likes"]');

				var is_disliked = buttonclosest.attr("data-jlike-is_disliked");
				var id = buttonclosest.attr("data-jlike-id");

				if (is_disliked == 'true')
				{
					is_disliked = false;
				}
				else if(is_disliked == 'false' && id == 'false')
				{
					is_disliked = false;
				}
				else
				{
					is_disliked = true;
				}

				jQuery.ajax({
					type: "POST",
					url: root_url + 'index.php?option=com_api&app=jlike&format=raw&resource=likes',
					headers: {
						'x-auth':'session'
					},
					datatype:'json',
					data: {
						id:buttonclosest.attr("data-jlike-id"),
						type:buttonclosest.attr("data-jlike-type"),
						sutype:buttonclosest.attr("data-jlike-sutype"),
						client:buttonclosest.attr("data-jlike-client"),
						content_id:buttonclosest.attr("data-jlike-contentid"),
						dislike:is_disliked
					},
					cache: false,
					success: function(response) {
						tdl.getLikes();
					}
				});
			});
		},

		initLikes: function() {
			jQuery.ajaxq ("iLikes", {
				url: root_url + "index.php?option=com_api&app=jlike&resource=init&format=raw",
				headers: {
					'x-auth':'session'
				},
				type: "POST",
				/*async: false,*/
				data: {
					url : url,
					type : type,
					subtype : subtype,
					client : client,
					title : title,
					cont_id : cont_id,
				},
				success:function(result){
					element.attr("data-jlike-contentid", result.data.content_id);
					tdl.getLikes();
				},
				error:function(){
				}
			});
		},

		getLikes: function() {
			jQuery.ajax({
				type: "GET",
				url: root_url + 'index.php?option=com_api&app=jlike&resource=likes&format=raw',
				headers: {
					'x-auth':'session'
				},
				datatype:'json',
				data: {content_id:element.attr("data-jlike-contentid")},
				cache: false,
				success: function(response) {
				var res = response.data;

				var markup = "";

				element.attr("data-jlike-id", response.data.id);
				element.attr("data-jlike-is_disliked", response.data.is_disliked);

				var compiled = _.template(templates.like);

				markup += compiled(res);

				jQuery(element).html(markup);

					tdl.initEvents();

				},
				error : function(response) {
					console.log("Error : " + response);
				}
			});
		}
	};

	templates.like = (defaults.tempRender).join("");

	var element = $(this);

	var url			= element.attr("data-jlike-url");
	var type		= element.attr("data-jlike-type");
	var subtype		= element.attr("data-jlike-subtype");
	var client		= element.attr("data-jlike-client");
	var cont_id		= element.attr("data-jlike-cont-id");
	var title		= element.attr("data-jlike-title");
	var userid		= element.attr("data-jlike-userid");

	var userProfile = '';

	if (!url) {
		console.log('no URL present for ' + element.attr('id'));
	}

	tdl.initLikes();
}
})( jQuery );
