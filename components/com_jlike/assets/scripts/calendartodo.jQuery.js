(function( $ ) {
$.fn.calendartodo = function(options){
	var defaults = {action:'', comments:'', userProfile:''};

	// options = $.extend(defaults, options); original code

	// Merge options into defaults and also override default options if already exist in default
	$.extend(defaults, options);

	if (defaults.action == "getTodos")
	{
		getTodos(defaults.obj);
	}

	function getTodos(obj)
	{
		jQuery.ajax({
			url: jlike_site_root + 'index.php?option=com_jlike&task=todocalendar.getTodo',
			type: 'GET',
			data:obj,
			dataType: 'json',
			beforeSend: function ()
			{
			},
			success: function(result)
			{
				defaults.callback.call(this, result);
			},
			error: function(err) {
				defaults.callback.call(this, err);
			}
		});
	}

	return true;
}
})( jQuery );
