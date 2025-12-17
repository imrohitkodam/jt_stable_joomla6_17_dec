/**
 * global: root_url
 * global: jlikeConfig
 */
window.jlikeConfig = {};
(function( $ ) {
$.fn.annotations = function(options){

	window.jlikeConfig = {};
	var element = $(this);

	jlikeConfig = $.extend({
		auth_token: element.attr("data-jlike-key"),
		enableEditing : true,
		enableReplying: false,
		enableUpvoting: false,
		enableDeleting: true,
		headers:{'x-auth':'session'},
		postCommentOnEnter : false,
		requestFrom: element.attr("data-jlike-requestFrom"),
		readOnly: true,
		roundProfilePictures: true,
		displayErrClass:'public-error',
		userLoggedIn: false,
		loginText: 'Please login to add comment',
		loginURL:null,
	}, options);

	userLogin(element);

	var url			= element.attr("data-jlike-url");
	var type		= element.attr("data-jlike-type");
	var subtype		= element.attr("data-jlike-subtype");
	var client		= element.attr("data-jlike-client");
	var cont_id		= element.attr("data-jlike-cont-id");
	var title		= element.attr("data-jlike-title");
	var context		= element.attr("data-jlike-context");
	var ordering    = element.attr("data-jlike-ordering");
	var direction   = element.attr("data-jlike-direction");
	var limit       = parseInt(element.attr("data-jlike-limit"), 10);
	var limitstart  = parseInt(element.attr("data-jlike-limitstart"), 10);
	var root_url   = element.attr("attr.data-jlike-domain");


	if (jlikeConfig.hasOwnProperty('cont_id')){
		if (jlikeConfig.cont_id && !cont_id) {
			element.attr("data-jlike-cont-id", options.cont_id);
			cont_id = jlikeConfig.cont_id;
		}
	}

	if (jlikeConfig.hasOwnProperty('url')){
		if (jlikeConfig.url && !url) {
			element.attr("data-jlike-url", jlikeConfig.url);
			url = jlikeConfig.url;
		}
	}

	if (jlikeConfig.hasOwnProperty('client')){
		if (jlikeConfig.client && !client) {
			element.attr("data-jlike-client", jlikeConfig.client);
			client = jlikeConfig.client;
		}
	}

	if (!client || !url || !cont_id) {
		return false;
	}
	
	// This is done for osian
	if (jlikeConfig.hasOwnProperty('root_url')){
		if (jlikeConfig.root_url && !root_url) {
			element.attr("attr.data-jlike-domain", jlikeConfig.root_url);
			root_url = jlikeConfig.root_url;
		}
	}

	if (jlikeConfig.requestFrom === "api"){
		jlikeConfig.type      = 'api';
		jlikeConfig.initApi   = 'index.php?option=com_api&app=jlike&resource=init&format=raw&key='+jlikeConfig.auth_token;
		jlikeConfig.getApi    = 'index.php?option=com_api&app=jlike&resource=annotations&format=raw&key='+jlikeConfig.auth_token;
		jlikeConfig.saveApi	  = 'index.php?option=com_api&app=jlike&resource=annotations&format=raw&key='+jlikeConfig.auth_token;
		jlikeConfig.updateApi = 'index.php?option=com_api&app=jlike&resource=annotations&format=raw&key='+jlikeConfig.auth_token;
		jlikeConfig.deleteApi = 'index.php?option=com_api&app=jlike&resource=annotations&format=raw&key='+jlikeConfig.auth_token;
	}
	else{
		jlikeConfig.type      = 'task';
		jlikeConfig.initApi   = 'index.php?option=com_jlike&task=annotationform.getInitData&tmpl=component';
		jlikeConfig.getApi    = 'index.php?option=com_jlike&task=annotations.getData&tmpl=component';
		jlikeConfig.saveApi	  = 'index.php?option=com_jlike&task=annotationform.save&tmpl=component';
		jlikeConfig.updateApi = 'index.php?option=com_jlike&task=annotationform.save&tmpl=component';
		jlikeConfig.deleteApi = 'index.php?option=com_jlike&task=annotationform.delete&tmpl=component';
	}

	var userProfile = '';

	jQuery.ajaxq ("jAnnotations", {
		url: root_url + jlikeConfig.initApi,
		headers: jlikeConfig.headers,
		type: "POST",
		/*async: false,*/
		data: {
			url : url,
			async:false,
			type : type,
			subtype : subtype,
			client : client,
			title : title,
			cont_id : cont_id,
		},
		dataType: "json",
		success:function(result){
			element.attr("data-jlike-contentid", result.data.content_id);

			if (typeof result.data.usersInfo !== undefined) {
				userProfile = result.data.usersInfo.avatar;

				var userslist = type=='annotations' ? JSON.stringify(result.data.userslist): '';
				element.attr("data-jlike-mentionsUserslist", userslist);

				loadComments(element);
			}
		},
		error:function(){
		}
	});

	function userLogin(element) {
		if (jlikeConfig.userLoggedIn === false){
			jQuery(element).before('<div class="jlike-comments-logintext">'+jlikeConfig.loginText+'<a href='+jlikeConfig.loginURL+'> here</a></div>');
		}
	}

	function loadComments(element) {

		jQuery(element).comments({
			enableReplying: jlikeConfig.enableReplying,
			enableEditing : jlikeConfig.enableEditing,
			enableUpvoting: jlikeConfig.enableUpvoting,
			enableDeleting: jlikeConfig.enableDeleting,
			readOnly: jlikeConfig.readOnly,
			postCommentOnEnter : jlikeConfig.postCommentOnEnter,		
			roundProfilePictures: jlikeConfig.roundProfilePictures,
			sendText: "Comment",
			
			fieldMappings: {
				id: 'annotation_id',
				parent: 'parent_id',
				content: 'annotation',
				content_html: 'annotation_html',
				created: 'annotation_date',
				fullname: 'user_name',
				createdByCurrentUser: 'is_mine',
			},
			timeFormatter: function(time) {
				return moment(time).fromNow();
				//return moment(time).format('HH:mm:ss');
			},

			profilePictureURL: userProfile,

			getComments: function(success, error) {
				jQuery.ajax({
					url: root_url + jlikeConfig.getApi,
					headers: jlikeConfig.headers,
					type: "GET",
					data : {
						content_id : element.attr("data-jlike-contentid"),
						subtype : element.attr("data-jlike-subtype"),
						client : element.attr("data-jlike-client"),
						limitstart : parseInt(element.attr("data-jlike-limitstart"), 10),
						limit : parseInt(element.attr("data-jlike-limit"), 10),
						context : element.attr("data-jlike-context"),
						ordering : element.attr("data-jlike-ordering"),
						direction : element.attr("data-jlike-direction")
						//ordering:ordering,
						//direction:direction
					},
					dataType: "json",
					success:function(result) {

						if(result.data.success === false)
						{
							jQuery('.no-data').show();
							jQuery('.spinner').remove();
							jQuery('.'+jlikeConfig.displayErrClass).show();
							setTimeout(function(){ jQuery('.'+jlikeConfig.displayErrClass).fadeOut();}, 3000);
							jQuery('.'+jlikeConfig.displayErrClass).html(result.data.result);
							return false;
						}

						for (var i = 0; i < result.data.result.length; i++) {
							var t = result.data.result[i].annotation_date.split(/[- :]/);

							if (typeof result.data.result !== undefined || typeof result.data.result.user !== undefined)
							{
								result.data.result[i].user_name = result.data.result[i].user.name;
								result.data.result[i].user_id = result.data.result[i].user.id;
								result.data.result[i].profile_picture_url = result.data.result[i].user.avatar;
								result.data.result[i].profile_url = result.data.result[i].user.profile_link;
								result.data.result[i].created =  new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
							}
						}

						success(result.data.result);

						// Pagination
						if (result.data.total > limit)
						{
							if ( jQuery( ".data-container .loadMore", element).length )
							{
								jQuery(".loadMore").click(function() {
									limitstart = limitstart + limit;
									element.attr('data-jlike-limitstart', limitstart);
									loadComments(element);
								});
							}
							else
							{
								var r= jQuery('<div class="text-center"><button class="loadMore loadMore btn btn-primary pt-2 pb-2 pl-3 pr-3"><img class="cmt_loader" src="'+root_url + 'components/com_jlike/assets/images/ajax-loading.gif">more comments</button></div>');

								jQuery(".data-container", element).append(r);
								jQuery('.cmt_loader').hide();
								jQuery(".loadMore").click(function() {
									jQuery('.cmt_loader').show();
									limitstart = limitstart + limit;
									element.attr('data-jlike-limitstart', limitstart);

									jQuery.ajax({
										url: root_url + jlikeConfig.getApi,
										headers: jlikeConfig.headers,
										type: "GET",
										data : {
											content_id : element.attr("data-jlike-contentid"),
											subtype : element.attr("data-jlike-subtype"),
											client : element.attr("data-jlike-client"),
											limitstart : parseInt(element.attr("data-jlike-limitstart"), 10),
											limit : parseInt(element.attr("data-jlike-limit"), 10),
											context : element.attr("data-jlike-context"),
											ordering : element.attr("data-jlike-ordering"),
											direction : element.attr("data-jlike-direction")
											//ordering:ordering,
											// direction:direction
										},
										success:function(result) {
											jQuery('.cmt_loader').hide();
											for (var i = 0; i < result.data.result.length; i++) {
												var t = result.data.result[i].annotation_date.split(/[- :]/);

												result.data.result[i].user_name = result.data.result[i].user.name;
												result.data.result[i].user_id = result.data.result[i].user.id;
												result.data.result[i].profile_picture_url = result.data.result[i].user.avatar;
												result.data.result[i].profile_url = result.data.result[i].user.profile_link;
												result.data.result[i].created =  new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
											}
											success(result.data.result);

											if (result.data.total < limitstart+10)
											{
												jQuery( ".data-container .loadMore", element).hide();
												element.attr('data-jlike-limitstart', 0);
											}
										}
									});
								});
							}
						}
					}
				});
			},
			postComment: function(commentJSON, success, error, div) {
				commentJSON.content_id = div.attr('data-jlike-contentid');
				commentJSON.subtype = div.attr('data-jlike-subtype');
				commentJSON.client = div.attr('data-jlike-client');
				commentJSON.context = div.attr('data-jlike-context');

				if (parseInt(commentJSON.annotation_id) != commentJSON.annotation_id) {
					commentJSON.annotation_id = null;
				}

				var commentText = commentJSON.annotation;
				commentText = commentText.replace(/\&nbsp;/g, '');
				commentText = jQuery.trim(commentText);

				if(typeof commentText === undefined  || commentText == '') {
					jQuery('.'+jlikeConfig.displayErrClass).show();
					setTimeout(function(){ jQuery('.'+jlikeConfig.displayErrClass).fadeOut();}, 3000);
					jQuery('.'+jlikeConfig.displayErrClass).html('Please enter the comment !');
					return false;
				}

				jQuery.ajax({
					type: 'POST',
					url: root_url + jlikeConfig.saveApi,
					headers: jlikeConfig.headers,
					data: commentJSON,
					dataType: "json",
					success: function(result) {

						if(result.data.success === false) {
							jQuery('.'+jlikeConfig.displayErrClass).show();
							setTimeout(function(){ jQuery('.'+jlikeConfig.displayErrClass).fadeOut();}, 3000);
							jQuery('.'+jlikeConfig.displayErrClass).html(result.data.result);
							return false;
						}

						var t = result.data.result.annotation_date.split(/[- :]/);

						if (typeof result.data.result !== undefined || typeof result.data.result.user === undefined) {
							result.data.result.user_name = result.data.result.user.name;
							result.data.result.user_id = result.data.result.user.id;
							result.data.result.profile_picture_url = result.data.result.user.avatar;
							result.data.result.profile_url = result.data.result.user.profile_link;
							result.data.result.created =  new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
							result.data.result.created_by_current_user = true;
							success(result.data.result);
						}
						// loadComments(div);
					},
					error: error
				});
			},
			deleteComment: function(commentJSON, success, error, div) {
				var methodType = 'post';

				if(jlikeConfig.type === "api") {
					methodType = 'delete';
				}

				commentJSON.content_id = div.attr('data-jlike-contentid');
				commentJSON.subtype = div.attr('data-jlike-subtype');
				commentJSON.client = div.attr('data-jlike-client');
				commentJSON.id = commentJSON.annotation_id;
				
				jQuery.ajax({
					type: methodType,
					url: root_url + jlikeConfig.deleteApi+"&id="+commentJSON.annotation_id,
					headers: jlikeConfig.headers, 
					data: commentJSON,
					dataType: "json",
					success: function(comment) {
						success(comment);
						// loadComments(div);
					},
					error: error
				});
			},
			putComment: function(commentJSON, success, error, div) {
				commentJSON.content_id = div.attr('data-jlike-contentid');
				commentJSON.subtype = div.attr('data-jlike-subtype');
				commentJSON.client = div.attr('data-jlike-client');
				commentJSON.context = div.attr('data-jlike-context');

				if (parseInt(commentJSON.annotation_id) != commentJSON.annotation_id) {
					return false;
				}

				var commentText = commentJSON.annotation;
				commentText = commentText.replace(/\&nbsp;/g, '');
				commentText = jQuery.trim(commentText);

				if(typeof commentText === undefined  || commentText == '') {
					jQuery('.'+jlikeConfig.displayErrClass).show();
					setTimeout(function(){ jQuery('.'+jlikeConfig.displayErrClass).fadeOut();}, 3000);
					jQuery('.'+jlikeConfig.displayErrClass).html('Please enter the comment !');
					return false;
				}

				jQuery.ajax({
					type: 'POST',
					url: root_url + jlikeConfig.updateApi,
					headers: jlikeConfig.headers,
					data: commentJSON,
					dataType: "json",
					success: function(comment) {
						if(comment.data.success === false)
						{
							jQuery('.'+jlikeConfig.displayErrClass).show();
							setTimeout(function(){ jQuery('.'+jlikeConfig.displayErrClass).fadeOut();}, 3000);
							jQuery('.'+jlikeConfig.displayErrClass).html(result.data.result);
							return false;
						}
						// loadComments(div);
						success(comment.data.result);
					},
					error: error
				});
			}
		});

		// init mention
		var instance = "#"+element.attr("id")+" .jlike-mention";
		var userslistObj = JSON.parse(element.attr("data-jlike-mentionsUserslist"));

		if (typeof userslistObj !== undefined  && userslistObj != '') {
			init_mention(instance, userslistObj);
		}
	}

	return true;
};
})( jQuery );
