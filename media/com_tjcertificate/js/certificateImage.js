var certificateImage = {

    printCertificate: function(elementId) {
        var printContent = document.getElementById(elementId).innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;

        certificateImage.enableDownloadShareBtns();
    },

    enableDownloadShareBtns: function()
    {
		jQuery("#download-popover").popover({
            trigger: 'focus',
            html: true,
            content: jQuery('#download-popover-content').html()
        });

        jQuery("#sharing-popover").popover({
            trigger: 'focus',
            html: true,
            content: jQuery('#sharing-popover-content').html()
        });

        jQuery("#copyurl").popover();
    },

    uploadImage: function(image) {
        var result = false;
        var certificateId = jQuery("#certificateId").val();
        jQuery.ajax({
            url: certRootUrl + "index.php?option=com_tjcertificate&task=certificate.uploadCertificate",
            type: 'POST',
            data: {
                image: image,
                certificateId: certificateId
            },
            success: function(data) {
                result = data;

                var certificateId = jQuery("#certificateId").val();
                var imagePath     = certRootUrl + 'media/com_tjcertificate/certificates/';
                var img           = document.createElement('img');
                jQuery('#certificateContent').hide();
                img.src = imagePath + certificateId + ".png?ver=" + tjCertVersion;
                jQuery("#previewImage").append(img);
				setTimeout(function(){

                    if (screen.width < 1200)
                    {
                        viewport = document.querySelector("meta[name=viewport]");
                        viewport.setAttribute("content", "width=device-width");
                    }

					jQuery.LoadingOverlay("hide");
                }, 1000);
            }
        });

        return result;
    },

    generateImage: function(element) {
		// jQuery('#certificateContent').width(element.offsetWidth).height(element.offsetHeight);

        if (screen.width < 1200)
        {
            viewport = document.querySelector("meta[name=viewport]");
            viewport.setAttribute("content", "width=1200px");
        }

		jQuery.LoadingOverlay("show", {
			image : "media/com_tjcertificate/images/loader/loader.gif"
		});

        html2canvas(element, {
            // scale: (2),
            scrollX: 0,
            scrollY: -window.scrollY,
            allowTaint: true,
            useCORS: true
        }).then(function(canvas) {
			certificateImage.enableDownloadShareBtns();
            certificateImage.uploadImage(canvas.toDataURL('image/png'));
        });
    },

     copyUrl: function(element) {
        element = '#' + element;
        var inputDump = document.createElement('input'),
        hrefText = jQuery(element).attr('data-alt-url');
        jQuery(element).popover("show");
        document.body.appendChild(inputDump);
        inputDump.value = hrefText;
        inputDump.select();
        document.execCommand('copy');
        document.body.removeChild(inputDump);

        setTimeout(function() {
            jQuery(element).popover("hide");
        }, 1000);
    }
}
