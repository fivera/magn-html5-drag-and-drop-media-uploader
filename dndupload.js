
var dndmedia_dropstyle;

jQuery(document).ready(function() {
	
	var res = magnCreateUploader();
	if (!res) return false;
	
	initBrowserWarning();
	initDnD();
	
	jQuery('.dndmedia-insert-link').live('click', function() {
		var title = jQuery("#post-body input[name=post_title]").val();
		var url = jQuery(this).attr('rel');
		jQuery("#edButtonPreview").click();
		magnInsertImage( url, title );
	} );
	
	jQuery('.dndmedia-rename-link').live('click', function() { 
		
		alert('Under development. This feature will allow you to rename the filename of this image.');
		
		var fullpath = jQuery(this).parent().find('img').attr('src');
		filename = fullpath.replace(/^.*[\\\/]/, '');
		fileext = /[^.]+$/.exec(filename);
		var filename_noext = filename.substr(0, filename.lastIndexOf('.')) || filename;
		
		var new_filename = prompt("Enter new file name", filename_noext);
		if (new_filename != undefined)
		{
			
			return true;
		}
		
	} );
	
	initDnDExtraTools();	
});


function magnCreateUploader() {

	var postid = jQuery('input#post_ID').val();
	
	if ( dndmedia_dropstyle != undefined)
	{
		var element = jQuery('#drop-box-jsupload-gmail')[0];
	}else{
		var element = jQuery('#drop-box-jsupload')[0];
	}
	
	if (element == undefined) return false;
	
	var uploader = new qq.FileUploader({
		//element: document.getElementById('drop-box-jsupload'),
		element: element,
		action: ajaxurl,
		debug: false,
		
		params: {'action': 'dndmedia', 'post_id': postid },
		onSubmit: function(id, fileName){
		
			// display progress bar
			jQuery("#upload-status-progressbar").fadeIn(0);
			//jQuery("#upload-status-progressbar").progressbar({value:20});
		},
		onProgress: function(id, fileName, loaded, total){
			jQuery('#dndmedia_status').text("Uploading new file. Progress "+loaded+ " bytes of " + total + " bytes." );

			// Update the progress bar
			//var currentProgress = total/loaded * 100;
			//jQuery("#upload-status-progressbar").progressbar({value: currentProgress});
		},
		onComplete: function(id, fileName, response){
			
			dndmediaProcessCompletedFileUpload(id, fileName, response);
			
			//jQuery("#upload-status-progressbar").progressbar({value:0});
			jQuery("#upload-status-progressbar").fadeOut(0);
		},
		onCancel: function(id, fileName){
			jQuery('#dndmedia_status').text("Last upload was cancelled");

			//jQuery("#upload-status-progressbar").progressbar({value:0});
			jQuery("#upload-status-progressbar").fadeOut(0);
		},
		
	}); 
	
	jQuery('.dndmedia_thumb_div').live('dblclick', function() {
		var url = jQuery(this).find('img').attr('url');
		magnInsertImage(url, "");
	} );
	

	return true;	
}

function magnInsertImage(url, title)
{
	var textToInsert = '<img alt="'+title+'" title="'+title+'" src="'+url+'" />';

	if (jQuery("textarea#content").is(":visible"))
	{
		textToInsert = "\n" + textToInsert + "\n";
		var $content = jQuery('textarea#content');
		var cursorPos = $content.prop('selectionStart');
		var v = $content.val();
		var textBefore = v.substring(0,  cursorPos );
		var textAfter  = v.substring( cursorPos, v.length );
		$content.val( textBefore + textToInsert +textAfter );

	} else {
		tinyMCE.execCommand('mceInsertContent',false, textToInsert);
	}
			
}

function dndmediaProcessCompletedFileUpload(id, fileName, response)
{
	var title = "";
	// Update stats
	jQuery('#dndmedia_status').text("");
	
	// If OK!
	if (response.url)
	{
		var markup = new String();
		
		response.name = response.url.replace(/^.*[\\\/]/, '');
		
		markup = "<div class='dndmedia_file_row'><div class=\"dndmedia_thumb_div\"><img src=\""+response.url+"\" alt=\"\" width=\"120\" class=dndmedia_thumb_img /><label>"+response.name+"</label><a href=\"javascript:void(0)\" class=\"dndmedia-link dndmedia-insert-link\" rel=\""+response.url+"\" style=\"display:visible;\" >insert</a>" +
		"<br/><a href=\"javascript:void(0)\" class=\"dndmedia-link dndmedia-rename-link\" style=\"display:none\">rename</a>  </div></div>";
		jQuery('#dndmedia_files').append( markup );
		
		var dndmedia_sendtoeditor = jQuery('#dndmedia_sendtoeditor').attr('checked');
		var dndmedia_attachment = jQuery('#dndmedia_attachment').val();
		var dndmedia_attachment_size = jQuery('#dndmedia_attachment_size').val();
		
		if (dndmedia_sendtoeditor)
		{
			var url = response.url;
			//console.log('dndmedia_attachment_size' + dndmedia_attachment_size);
			if (dndmedia_attachment_size != undefined)
			{
				// try to get attachment size
				if ( response.attachment_data.sizes && response.attachment_data.sizes[dndmedia_attachment_size] != undefined )
				{
					// get path without original file name (this is because WordPress only specify the filename without path for additional attachment sizes
					var dndmedia_filename_index = url.lastIndexOf("/");
					var dndmedia_filename_path = url.substr(0, dndmedia_filename_index);
					
					// replace the url with new determined path plus the attachment filename
					url = dndmedia_filename_path + '/' + response.attachment_data.sizes[dndmedia_attachment_size].file;
					//console.log('file ' + url);
				} else {
					//console.log('no url file ' + url);
				}
			}
		
			// send the image to editor
			try {
				title = response.attachment_data.image_meta.title;
				if (!response.attachment_data.image_meta.title) response.attachment_data.image_meta.title = '';
				
				//TODO: Check if this is image - or media type
			} catch (err) { }

			if (title == undefined) title = jQuery("#post-body input[name=post_title]").val();
			magnInsertImage(url, title);
		}
		
	}else{
		jQuery('#dndmedia_status').text("There was an error uploading this file");
	}
}
        

function initBrowserWarning() {
	var isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
	var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
	
	if(!isChrome && !isFirefox)
		jQuery("#browser-warning").fadeIn(125);
}

function initDnD() {
	// Add drag handling to target elements
	var body = document.getElementsByTagName('body')[0];
	body.addEventListener("dragenter", dndmediaOnDragEnter, false);
	//document.getElementById("body").addEventListener("dragenter", onDragEnter, false);
	
	if ( dndmedia_dropstyle != undefined)
	{
		document.getElementById("drop-box-overlay-gmail").addEventListener("dragleave", dndmediaOnDragLeave, false);
		document.getElementById("drop-box-overlay-gmail").addEventListener("dragover", dndmediaOnDragOver, false);
		document.getElementById("drop-box-overlay-gmail").addEventListener("dragenter", dndmediaOnDragEnterGmail, false);
		document.getElementById("drop-box-overlay-gmail").addEventListener("drop", dndmediaOnDrop, false);
		
	} else {
		document.getElementById("drop-box-overlay").addEventListener("dragleave", dndmediaOnDragLeave, false);
		document.getElementById("drop-box-overlay").addEventListener("dragover", dndmediaNoopHandler, false);
		// Add drop handling
		document.getElementById("drop-box-overlay").addEventListener("drop", dndmediaOnDrop, false);
	}
	
	// init the widgets
	//jQuery("#upload-status-progressbar").progressbar();
}

function dndmediaNoopHandler(evt) {
	evt.stopPropagation();
	evt.preventDefault();
}

var dndmedia_scrollto = false;
function dndmediaOnDragEnter(evt) {

	if ( dndmedia_dropstyle != undefined)
	{
		//jQuery("#wpwrap").addClass('dndmedia_hover');
		jQuery("#drop-box-overlay-gmail").show();
		if (!dndmedia_scrollto)
		{
			/*jQuery('html, body').animate({
				scrollTop: jQuery("#drop-box-overlay-gmail").offset().top
			}, 500);
			*/
			dndmedia_scrollto = true;
		}
		
		//jQuery("#drop-box-overlay-gmail-wrapper").fadeIn(125);
		
	} else {
	
		jQuery("#drop-box-overlay").fadeIn(125);
		jQuery("#drop-box-prompt").fadeIn(125);
	}
}

function dndmediaOnDragLeave(evt) {
	/*
	 * We have to double-check the 'leave' event state because this event stupidly
	 * gets fired by JavaScript when you mouse over the child of a parent element;
	 * instead of firing a subsequent enter event for the child, JavaScript first
	 * fires a LEAVE event for the parent then an ENTER event for the child even
	 * though the mouse is still technically inside the parent bounds. If we trust
	 * the dragenter/dragleave events as-delivered, it leads to "flickering" when
	 * a child element (drop prompt) is hovered over as it becomes invisible,
	 * then visible then invisible again as that continually triggers the enter/leave
	 * events back to back. Instead, we use a 10px buffer around the window frame
	 * to capture the mouse leaving the window manually instead. (using 1px didn't
	 * work as the mouse can skip out of the window before hitting 1px with high
	 * enough acceleration).
	 */
	if(evt.pageX < 10 || evt.pageY < 10 || jQuery(window).width() - evt.pageX < 10  || jQuery(window).height - evt.pageY < 10) {
		
		if ( dndmedia_dropstyle != undefined)
		{
			jQuery("#wpwrap").removeClass('dndmedia_hover');
			
		}else{
			jQuery("#drop-box-overlay").fadeOut(125);
			jQuery("#drop-box-prompt").fadeOut(125);
		}
	}
}

function dndmediaOnDragOver(evt)
{
	jQuery("#drop-box-overlay-gmail").addClass('dndmedia_hover');
}

function dndmediaOnDragEnterGmail(evt)
{
	jQuery("#drop-box-overlay-gmail").addClass('dndmedia_hover');
}

function dndmediaOnDrop(evt) {
	// Consume the event.
	dndmediaNoopHandler(evt);
	
	// Hide overlay
	if ( dndmedia_dropstyle != undefined)
	{
//		jQuery("#drop-box-overlay-gmail").fadeOut(0);
	}else{
		jQuery("#drop-box-overlay").fadeOut(0);
		jQuery("#drop-box-prompt").fadeOut(0);
	}
	
	// Empty status text
	jQuery("#upload-details").html("");
	
	// Get the dropped files.
	var files = evt.dataTransfer.files;
	
	// If anything is wrong with the dropped files, exit.
	if(typeof files == "undefined" || files.length == 0)
	{
		var format = "Text";
        var textData = evt.dataTransfer.getData (format);
		if (typeof textData != "undefined" )
		{	
			var url = textData;
			if ( url.indexOf("http") != -1 )
			{
				// try to import from url
				dndImportFromUrl(url);
			}
		}
		return;
	}
		
	// Update and show the upload box
	var label = (files.length == 1 ? " file" : " files");
	jQuery("#upload-count").html(files.length + label);
	jQuery("#upload-thumbnail-list").fadeIn(125);
	
	// Process each of the dropped files individually
	/*for(var i = 0, length = files.length; i < length; i++) {
		uploadFile(files[i], length);
	}*/
}

/**
 * Used to update the progress bar and check if all uploads are complete. Checking
 * progress entails getting the current value from the progress bar and adding
 * an incremental "unit" of completion to it since all uploads run async and
 * complete at different times we can't just update in-order.
 * 
 * This is only ever meant to be called from an upload 'success' handler.
 */
function updateAndCheckProgress(totalFiles, altStatusText) {
//progressbar
/*	var currentProgress = jQuery("#upload-status-progressbar").progressbar("option", "value");
	currentProgress = currentProgress + (100 / totalFiles);
	
	// Update the progress bar
	jQuery("#upload-status-progressbar").progressbar({value: currentProgress});
	
	// Check if that was the last file and hide the animation if it was
	if(currentProgress >= 99) {
		jQuery("#upload-status-text").html((altStatusText ? altStatusText : "All Uploads Complete!"));
		jQuery("#upload-animation").hide();
	}
*/
}

function generateUploadResult(label, image, altInputValue) {
	var markup = "    <li><span class='label'>" + label + "</span><input readonly type='text' value='";
	
	if(image.url)
		markup += image.url;
	else
		markup += altInputValue;
	
	markup += "' /></li><li><span class='details'>";
	
	if(image.width)
		markup += image.width + "x" + image.height;
	
	if(image.width && image.sizeInBytes)
		markup += " - ";
	
	if(image.sizeInBytes)
		markup += (image.sizeInBytes / 1000) + " KB";
	
	markup += "</span></li>";
	
	return markup;
}


function initDnDExtraTools()
{
		jQuery("#dndmedia_importurl").click( function() {
			var result = window.prompt("What is the URL you want to import?","");
			if (result!=undefined)
			{
				dndImportFromUrl(result);
			}
		} );
}

function dndImportFromUrl(url)
{
	var postid = jQuery('input#post_ID').val();
	
	jQuery.ajax({
	  url: ajaxurl,
	  type: 'POST',
	  dataType: 'json',
	  data: {'action': 'dndmedia_importurl', 'post_id': postid, 'url': url },
	  beforeSend: function() {
			jQuery("#upload-status-progressbar").fadeIn(0);
			jQuery('#dndmedia_status').text("Uploading new file, please hold on..." );
	  },
	  success: function(response) {

			var id = "1";
			var fileName = response.file;
			if (response.url)
			{
				dndmediaProcessCompletedFileUpload(id, fileName, response);			
				
				jQuery('#dndmedia_status').text("");
			}else{
				jQuery('#dndmedia_status').text("");
			}
        },
	  complete: function() {
			jQuery("#upload-status-progressbar").fadeOut(0);
	  }
	});

}
