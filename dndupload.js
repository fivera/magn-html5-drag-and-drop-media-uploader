

jQuery(document).ready(function() {
	
	magnCreateUploader();
	initBrowserWarning();
	initDnD();
	
	jQuery('.dndmedia-insert-link').live('click', function() {
		var title = "Image";// jQuery(this).attr('rel');
		var url = jQuery(this).attr('rel');
		tinyMCE.execCommand('mceInsertContent',false,'<br><img alt="'+title+'" src="'+url+'" />');
		//alert('inserting ok');
	} );
	
});

function magnCreateUploader() {

	var postid = jQuery('input#post_ID').val();
	
	// set progress bar
	//jQuery("#upload-status-progressbar").progressbar({value: 0});
	
	var uploader = new qq.FileUploader({
		element: document.getElementById('drop-box-jsupload'),
		action: ajaxurl,
		debug: true,
		
		params: {'action': 'dndmedia', 'post_id': postid },
		onSubmit: function(id, fileName){
		
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
}

function dndmediaProcessCompletedFileUpload(id, fileName, response)
{
	// Update stats
	jQuery('#dndmedia_status').text("");
	
	// If OK!
	if (response.url)
	{
		var markup = new String();
		markup = "<div class='dndmedia_file_row'><div class=\"dndmedia_thumb_div\"><img src=\""+response.url+"\" alt=\"\" width=\"120\" class=dndmedia_thumb_img /><label>New File</label><a href=\"#\" class=\"dndmedia-insert-link\" rel=\""+response.url+"\" style=\"display:none;\" >insert</a></div></div>";
		jQuery('#dndmedia_files').append( markup );
		
		var dndmedia_sendtoeditor = jQuery('#dndmedia_sendtoeditor').attr('checked');
		var dndmedia_attachment = jQuery('#dndmedia_attachment').val();
		var dndmedia_attachment_size = jQuery('#dndmedia_attachment_size').val();
		
		if (dndmedia_sendtoeditor)
		{
			var url = response.url;
			//alert('dndmedia_attachment_size' + dndmedia_attachment_size);
			if (dndmedia_attachment_size != undefined)
			{
				// try to get attachment size
				if ( response.attachment_data.sizes[dndmedia_attachment_size] != undefined )
				{
					// get path without original file name (this is because WordPress only specify the filename without path for additional attachment sizes
					var dndmedia_filename_index = url.lastIndexOf("/");
					var dndmedia_filename_path = url.substr(0, dndmedia_filename_index);
					
					// replace the url with new determined path plus the attachment filename
					url = dndmedia_filename_path + '/' + response.attachment_data.sizes[dndmedia_attachment_size].file;
					//alert('file ' + url);
				} else {
					//alert('no url file ' + url);
				}
			}
		
			// send the image to editor
			tinyMCE.execCommand('mceInsertContent',false,'<img alt="'+response.attachment_data.image_meta.title+'" src="'+url+'" />');
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
	
	document.getElementById("drop-box-overlay").addEventListener("dragleave", dndmediaOnDragLeave, false);
	document.getElementById("drop-box-overlay").addEventListener("dragover", dndmediaNoopHandler, false);
	
	// Add drop handling
	document.getElementById("drop-box-overlay").addEventListener("drop", dndmediaOnDrop, false);
	
	// init the widgets
	//jQuery("#upload-status-progressbar").progressbar();
}

function dndmediaNoopHandler(evt) {
	evt.stopPropagation();
	evt.preventDefault();
}

function dndmediaOnDragEnter(evt) {
	jQuery("#drop-box-overlay").fadeIn(125);
	jQuery("#drop-box-prompt").fadeIn(125);
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
		jQuery("#drop-box-overlay").fadeOut(125);
		jQuery("#drop-box-prompt").fadeOut(125);
	}
}

function dndmediaOnDrop(evt) {
	// Consume the event.
	dndmediaNoopHandler(evt);
	
	// Hide overlay
	jQuery("#drop-box-overlay").fadeOut(0);
	jQuery("#drop-box-prompt").fadeOut(0);
	
	// Empty status text
	jQuery("#upload-details").html("");
	
	// Reset progress bar incase we are dropping MORE files on an existing result page
	//progressbar
	//jQuery("#upload-status-progressbar").progressbar({value:0});
	
	// Show progressbar
	jQuery("#upload-status-progressbar").fadeIn(0);
	
	// Get the dropped files.
	var files = evt.dataTransfer.files;
	
	// If anything is wrong with the dropped files, exit.
	if(typeof files == "undefined" || files.length == 0)
		return;
	
	// Update and show the upload box
	var label = (files.length == 1 ? " file" : " files");
	jQuery("#upload-count").html(files.length + label);
	jQuery("#upload-thumbnail-list").fadeIn(125);
	
	// Process each of the dropped files individually
	for(var i = 0, length = files.length; i < length; i++) {
		uploadFile(files[i], length);
	}
}

// new jsupload file 
function uploadFile(file, totalFiles)
{
	
	
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