var jlikeRatingService = {
	postUrl: 'index.php?option=com_jlike&format=json&task=rating.save',

	// Ajax call to submit rating form
	post: function(formData, cb) {
		 jQuery.ajax({
			type: "POST",
			dataType : "json",
			data : formData,
			url: jlikeRootPath + this.postUrl,
			success: function(data) {
				cb(data);
			},
			error: function(data){
				cb(data);
			}
		});
	},

	// Ajax call to get list of rating as per the content id
	initRatings: function(cb) {

		var orderCol = jQuery('#sortTable').val();
		jQuery('#filter_order').val(orderCol);
		var limit = jQuery('#limit').val();
		var start = jQuery('#start').val();
		var filter_order = jQuery('#filter_order').val();
		var filter_order_Dir = jQuery('#filter_order_Dir').val();
		var url = jlikeRootPath + "index.php?option=com_jlike&format=json&task=ratings.getRatings";

		if (typeof limit != 'undefined')
		{
			url += "&limit="+limit;
		}

		var formdata = jQuery('#subForm').serialize();
		jQuery.ajax({
			type: "POST",
			dataType : "json",
			data : formdata,
			url: url+"&start="+start,
			success: function(data) {
				cb(data);
			},
			error: function(data)
			{
				cb(data);
			}
		});
	},

	// Ajax call to get rating record
	displayRatingData: function(cb) {
		 jQuery.ajax({
			type: "POST",
			dataType : "json",
			data: jQuery('#jlikeRating').serialize(),
			url: jlikeRootPath + 'index.php?option=com_jlike&format=json&task=rating.getLoggedInUserRating',
			success: function(data) {
				cb(data);
			},
			error: function(data){
				cb(data);
			}
		});
	}
};


