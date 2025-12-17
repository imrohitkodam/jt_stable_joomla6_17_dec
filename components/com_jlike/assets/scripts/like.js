jQuery(window).load(function() {
	jQuery(".contentRequestLike").each(function(){
	jQuery("#divLoading").show();
	var thisdiv = jQuery(this);
	var root_url=jQuery(this).attr("data-jlike-site-domain");
	var content_id;
	var obj = {};
	var obj = {"url":jQuery(this).attr("data-jlike-url")};
	obj["type"]=jQuery(this).attr("data-jlike-type");
	obj["subtype"]=jQuery(this).attr("data-jlike-subtype");
	obj["client"]=jQuery(this).attr("data-jlike-client");
	obj["cont_id"]=jQuery(this).attr("data-jlike-cont-id");
	obj["title"]=jQuery(this).attr("data-jlike-title");
	obj["userId"]=jQuery(this).attr("data-jlike-userId");

	jQuery.ajax({
			type: "POST",
			url: root_url + 'index.php?option=com_api&app=jlike&format=raw&resource=init',
			headers: {
				'x-auth':'session'
			},
			datatype:'json',
			data: obj,
			cache: false,
			success: function(response) {
				content_id = response.data.content_id;

				jQuery.ajax({
				type: "GET",
				url: root_url + 'index.php?option=com_api&app=jlike&resource=likes&format=raw',
				headers: {
					'x-auth':'session'
				},
				datatype:'json',
				data: {content_id:content_id},
				cache: false,
				success: function(response) {
				var res = response.data;console.log(content_id);
				thisdiv.attr("data-jlike-contentid", content_id);
				thisdiv.attr("data-jlike-is_disliked", response.data.is_disliked);
				thisdiv.attr("data-jlike-id", response.data.id);


				if (res!== null)
				{
					jQuery("#divLoading").hide();
					if (res.is_liked == true)
					{
						jQuery('#upvotebutton').prop("disabled",true);
					}

					if (res.is_disliked == false)
					{
						dislike_class += ' medislike btn-danger';
					}

					var like_class = 'btn';
					var dislike_class = 'btn';
				}
				else
				{
					jQuery("#divLoading").hide();
					res.total_likes = '';
				}

				html = '<span class="like-snippet">' +
							'<span class="like-snippet-text" id="likecount">' +
									res.total_likes +
							'</span>' +
						'</span>';

				thisdiv.append(html);

				},
				error : function(response) {
					console.log("Error : " + response);
				}
			});

			}
		});
	});

});

function updateLikes(thisid)
{
	//alert(jQuery(thisid).closest('div[data-jlike-type="likes"]').attr('data-jlike-contentid'));
	var buttonclosest = jQuery(thisid).closest('div[data-jlike-type="likes"]');
	var root_url= buttonclosest.attr("data-jlike-site-domain");
	jQuery("#divLoading").show();
	var obj = {};
	var obj = {"url":buttonclosest.attr("data-jlike-url")};
	obj["type"]=buttonclosest.attr("data-jlike-type");
	obj["subtype"]=buttonclosest.attr("data-jlike-subtype");
	obj["client"]=buttonclosest.attr("data-jlike-client");
	obj["cont_id"]=buttonclosest.attr("data-jlike-cont-id");
	obj["title"]=buttonclosest.attr("data-jlike-title");
	obj["userId"]=buttonclosest.attr("data-jlike-userId");
	obj["content_id"] = buttonclosest.attr("data-jlike-contentid");
	obj["id"] = buttonclosest.attr("data-jlike-id");
	var is_disliked = buttonclosest.attr("data-jlike-is_disliked");

	/* Send dislike false  when user clicks on like */

	if (is_disliked == 'true')
	{
		is_disliked = false;
	}
	else if(is_disliked == 'false' && obj["id"] == 'false')
	{
		is_disliked = false;
	}
	else
	{
		is_disliked = true;
	}
obj["dislike"] = is_disliked;
	jQuery.ajax({
		type: "POST",
		url: root_url + 'index.php?option=com_api&app=jlike&format=raw&resource=likes',
		headers: {
			'x-auth':'session'
		},
		datatype:'json',
		data: obj,
		cache: false,
		success: function(response) {
			buttonclosest.attr("data-jlike-id", response.id);
			buttonclosest.attr("data-jlike-is_disliked", obj["dislike"]);
			jQuery("#divLoading").hide();
			jQuery('#likecount',buttonclosest).html(response.total_likes);
		}
	});
}
