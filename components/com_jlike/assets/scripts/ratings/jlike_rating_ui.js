var jlikeRatingUI = {

	// Function for Rating form submission
	saveRatingForm: function(formId) {

		if (!jlikeRatingUI.checkJlikeRating())
		{
			jQuery(".message").html('<span class="alert alert-danger col-md-12">'+Joomla.Text._('COM_JLIKE_RATING_INVALID_FIELD_RATING')+'</span>');

			return false;
		}

		// Not needed as it shows error messages above the content not above the rating form
		// Also it doesn't vanish even if form is valid
		/*if (!document.formvalidator.isValid('#'+formId))
		{
			return false;
		}*/

		jQuery("#saveRating").attr("disabled", "disabled");

		var formData = jQuery('#'+formId).serialize();
		var cb = function(resp){
			if(resp.success)
			{
				jQuery(".ratingForm").hide();
				jlikeRatingUI.showRatingData();
				jQuery(".message").html('');
			}
			else
			{
				jQuery(".message").html('<span class="alert alert-danger col-md-12">'+resp.message+'</span>');
				jQuery("#saveRating").removeAttr("disabled");
			}
		};

		jlikeRatingService.post(formData, cb);
	},

	// Function to check validation for rating field
	checkJlikeRating: function(){

		var radios = document.getElementsByName("rating");

		for (var i = 0, len = radios.length; i < len; i++)
		{
			 if (radios[i].checked)
			 {
				 return true;
			 }
		}

		return false;
	},

	getRatings: function() {
		jQuery('#start').val("0");
		jQuery("#ratingList").empty();
		var getRatingsCb = function (data) {
			if(data.success)
			{
				jlikeRatingUI.displayRating(data);
				jlikeRatingUI.showMoreButton(data);
			}
		};
		jlikeRatingService.initRatings(getRatingsCb);
	},

	// Function to load more activities as per the limit
	loadMoreRating: function(){
		var loadMoreRatingCb = function(data){
			jlikeRatingUI.displayRating(data);
			jlikeRatingUI.showMoreButton(data);
		};
		jlikeRatingService.initRatings(loadMoreRatingCb);
	},

	// Function to display rating listusing handelbar
	displayRating: function(data){
		var start = jQuery('#start').val();
		jQuery.each( data.data.result, function( key, value ) {
			var source   = document.getElementById("entry-template").innerHTML;
			var template = Handlebars.compile(source);
			var context = {info: value};
			var html    = template(context);
			start++;
			jQuery('#start').val(start);
			jQuery("#ratingList").append(html);
		});

		data.data.start = start;
	},

	// Function to hide and show load more button
	showMoreButton: function(data){
		if (Number(data.data.start) >= Number(data.data.total))
		{
			jQuery('#load-more-rating-button').hide();
		}
		else
		{
			jQuery('#load-more-rating-button').show();
		}
	},

	// Function to display rating submiited by that user
	showRatingData: function(){
		var showRatingDataCb = function(resp) {
			if (resp.success)
			{
				var source   = document.getElementById("entry-template").innerHTML;
				var template = Handlebars.compile(source);
				var context = {info: resp.data};
				var html    = template(context);
				jQuery("#ratingDetailview").append(html);
			}
		};
		jlikeRatingService.displayRatingData(showRatingDataCb);
	}
};

// Handlebarbar helper to display stars as per the rating value
Handlebars.registerHelper('times', function(ratingData, block) {
	var accum = '';

    for(var i = ratingData.rating_scale; i > 0; i--)
    {
		var obj = {
			key:i,
			id:ratingData.id,
			checked: ''
		};

		if(i == ratingData.rating)
		{
			obj.checked = 'checked="checked"';
		}

		accum += block.fn(obj);
	}

	return accum;
});

jQuery(document).ready(function() {
		jlikeRatingUI.showRatingData();
		jlikeRatingUI.getRatings();
});

