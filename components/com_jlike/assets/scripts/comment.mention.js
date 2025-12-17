function mention(commentId)
{
	this.commentId = commentId;
}

/**
 * The the value
 */
mention.prototype.getValue = function(){
	var commentValue = jQuery('#'+this.commentId).mentionsInput('getValue');

	// For new line replace end div with \r\n
	commentValue = commentValue.replace(/<\/div>/g,"\r\n");
	commentValue = commentValue.replace(/<div>/g,"");
	commentValue = commentValue.replace(/<br>/g,'\n');

	// Strip tag because html not allowed in commnet
	commentValue = strip_tags(commentValue);

	return commentValue;
}

/**
 * The the Raw Value
 */
mention.prototype.getRawValue = function(){
	return jQuery('#'+this.commentId).mentionsInput('getRawValue');
}


// Init the users to mention
init_mention = function(className, userslist)
{
	jQuery(className).atwho({
	  at: "@",
	  data: userslist,
	  displayTpl: "<li><img src='${avatar}' height='26' width='26'> <span>${name} <small>${email}</small></span></li>",
	  insertTpl: '<a href="${profile_link}"><span class="mentionid">${atwho-at}[</span><span class="mentionName" >${name}</span><span class="mentionid">](${id})</span></a>'
    });
}
