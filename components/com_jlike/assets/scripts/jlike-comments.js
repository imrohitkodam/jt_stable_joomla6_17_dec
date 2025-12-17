/**
 * global: root_url
 */
(function( $ ) {

    $.fn.jLikeComments = function(options) {

		return this.each( function() {

			var el = $(this);

			/*var jlike_url = el.data('jlike-url');
			var jlike_type = el.data('jlike-type');
			var jlike_subtype = el.data('jlike-subtype');
			var jlike_client = el.data('jlike-client');
			var jlike_title = el.data('jlike-title');*/

			// Initialize the widget
			jQuery.ajax({
				url: root_url + "index.php?option=com_api&app=jlike&resource=init&key=a5325de0476ee06880bde277c7aae8cb&format=raw",
				type: "POST",
				data : {
					url : el.data('jlike-url'),
					type : el.data('jlike-type'),
					subtype : el.data('jlike-subtype'),
					client : el.data('jlike-client'),
				},
				success:function(result) {
					if (result.success == true)
					{
						el.data('jlike-contentid', result.data.results);
						loadComments(el);
					}
				}
			});

			function debug(el) { console.log(el); }

			function loadComments(el) {
				el.comments({
					postCommentOnEnter : true,
					enableReplying		: false,
					enableEditing : true,
					enableUpvoting: false,
					enableDeleting: true,
					//enableDeletingCommentWithReplies:true,
					//currentUserIsAdmin: true,

					fieldMappings: {
						id: 'annotation_id',
						//parent: 'parent_id',
						content: 'annotation',
						//modified:'annotation_date',
						fullname: 'user_name',
					},
					timeFormatter: function(time) {
						return new Date(time).timeAgoInWords();
					},

					profilePictureURL: 'https://app.viima.com/static/media/user_profiles/user-icon.png',

					getComments: function(success, error) {
						jQuery.ajax({
							url: root_url + "index.php?option=com_api&app=jlike&resource=annotations&key=a5325de0476ee06880bde277c7aae8cb&format=raw",
							type: "GET",
							data : {
								content_id : el.data('jlike-contentid'),
								subtype : el.data('jlike-subtype'),
								client : el.data('jlike-client')
							},
							success:function(result) {
								for (var i = 0; i < result.data.results.length; i++) {
									var t = result.data.results[i].annotation_date.split(/[- :]/);

									result.data.results[i].user_name = result.data.results[i].user.name;
									result.data.results[i].user_id = result.data.results[i].user.id;
									result.data.results[i].user_avatar = result.data.results[i].user.avatar;
									result.data.results[i].created =  new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

								}

								success(result.data.results);
							}
						});
					},

					postComment: function(data, success, error) {
						data.content_id = el.data('jlike-contentid');
						data.subtype = el.data('jlike-subtype');
						data.client = el.data('jlike-client');
						if (parseInt(data.annotation_id) != data.annotation_id)
						{
							data.annotation_id = null;
						}

						jQuery.ajax({
							url: root_url + "index.php?option=com_api&app=jlike&resource=annotations&key=a5325de0476ee06880bde277c7aae8cb&format=raw",
							type: "POST",
							data : data,
							success:function(result) {
								var t = result.data.results.annotation_date.split(/[- :]/);

								result.data.results.user_name = result.data.results.user.name;
								result.data.results.user_id = result.data.results.user.id;
								result.data.results.user_avatar = result.data.results.user.avatar;
								result.data.results.created =  new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);

								success(result.data.results);
							}
						});
					},

					deleteComment: function(commentJSON, success, error) {
					},

					putComment: function(commentJSON, success, error) {
					}

				});
			}

		});
	}

})( jQuery );
