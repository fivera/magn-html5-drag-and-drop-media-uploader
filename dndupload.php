<?php
/* 
Plugin Name: Magn Drag and Drop Upload
Plugin URI: 
Description: This plugin will help you to drag and drop images directly in your New Post page. Saves 90% of time while uploading images.
Version: 1.1
Author: Julian Magnone
Author URI: http://netvivs.com/

Other Resources:
http://www.thebuzzmedia.com/html5-drag-and-drop-and-file-api-tutorial/
http://imgscalr.com/
http://return-true.com/2010/01/using-ajax-in-your-wordpress-theme-admin/
http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/
http://wpsnipp.com/index.php/functions-php/add-custom-tab-to-featured-image-media-library-popup/
*/ 

require_once(dirname(__FILE__) . '/dndupload-ui.php');


function widget_dndmedia_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') ) {
		return;
	}
	
	//add_filter('template', 'ts_get_template');
	//add_filter('stylesheet', 'ts_get_stylesheet');
	
	// create custom plugin settings menu
	add_action('admin_menu', 'dndmedia_create_menu');

	function dndmedia_create_menu() {

		//create new top-level menu
		add_menu_page('dndmedia Plugin Settings', 'DnD Upload Settings', 'administrator', __FILE__, 'dndmedia_settings_page',plugins_url('/images/dndmediaicon.png', __FILE__));

		//call register settings function
		add_action( 'admin_init', 'register_dndmedia_settings' );
		add_action( 'admin_init', 'dndmedia_add_meta_boxes' );
	}
	
	function dndmedia_add_meta_boxes()
	{
		add_meta_box( 'dndmedia_metabox', 'Magn Drag and Drop Upload', 'dndmedia_show_metabox_ui', 'post', 'normal', 'high' );
	}

	function register_dndmedia_settings() {
		
		/*if ($_REQUEST['dndmedia_form_action']=='save')
		{
			//echo '<div class="updated fade" id="message"><p>'.__('Options', 'magn').' <strong>'.__('SAVED', 'magn').'</strong></p></div>';
			echo '<div class="updated fade" id="message"><p>Settings<strong>Saved</strong></p></div>';
		}*/
		
		//register our settings
		register_setting( 'dndmedia-settings-group', 'dndmedia_sendtoeditor' );
		register_setting( 'dndmedia-settings-group', 'dndmedia_attachment' );
		register_setting( 'dndmedia-settings-group', 'dndmedia_attachment_size' );
		register_setting( 'dndmedia-settings-group', 'dndmedia_dropstyle' );
	}
	
	function dndmedia_settings_page()
	{
		$dndmedia_form_action = $_POST['dndmedia_form_action'];
		dndmedia_show_ui_settings_page();
	}
	
	function dndmedia_save_settings()
	{
		echo 'Options saved';
	}

}

// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'widget_dndmedia_init');


add_action('post_edit_form_tag', 'dndmedia_post_edit_form_tag');
function dndmedia_post_edit_form_tag()
{
	echo 'Edit Form Tag';
}

add_action('edit_form_advanced', 'dndmedia_edit_form_advanced');
function dndmedia_edit_form_advanced( )
{
	//echo 'Ajax image edit tag form ';
	dndmedia_edit_form_advanced_ui();
}


//add_action('admin_head-post-new.php', 'dndmedia_admin_head');
//add_action('admin_head-post.php', 'dndmedia_admin_head');
add_action('admin_init', 'dndmedia_admin_head');
function dndmedia_admin_head()
{
	//if( defined('DOING_AJAX') ) return FALSE;
	global $pagenow, $typenow;
	if ($pagenow!='post-new.php' AND $pagenow!='post.php') return FALSE;

	
	wp_enqueue_script("jquery-ui-core");
	wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

	$js_file = plugins_url('/js/ui.progressbar.js', __FILE__);
	//if ( file_exists($js_file) )
	{
		wp_register_script('ui_progressbar_js', $js_file, array('jquery-ui-widget'), '1.0', FALSE);
		wp_enqueue_script('ui_progressbar_js');
	}
	
	
	$js_file = plugins_url('/js/jsupload/fileuploader.js', __FILE__);
	//if ( file_exists($js_file) )
	{
		wp_register_script('fileuploader_js', $js_file, array('jquery'), '1.0', FALSE);
		wp_enqueue_script('fileuploader_js');
	}

	$css_file = plugins_url('/dndupload.css', __FILE__);
	//if ( file_exists($css_file) )
	{
		wp_register_style('dndupload_css', $css_file);
		wp_enqueue_style( 'dndupload_css');
	}
	
	$js_file = plugins_url('/dndupload.js', __FILE__);
	//if ( file_exists($js_file) )
	{
		wp_register_script('dndupload_js', $js_file, array('jquery'), '1.0', FALSE);
		wp_enqueue_script('dndupload_js');
	}

}

/* When the post is saved, saves our custom data */
add_action( 'save_post', 'dndmedia_save_postdata' );
function dndmedia_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  /*
  if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
      return;
*/
  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data
//$mydata = $_POST['myplugin_new_field'];

  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)

   return $mydata;
}

ini_set('safe_mode', 'Off');
ini_set('safe_mode_gid', 'Off');


add_action('wp_ajax_dndmedia', 'ajax_dndmedia_callback');
function ajax_dndmedia_callback() {
	global $wpdb; // this is how you get access to the database

	$log = array();
	
	$post_id = $_REQUEST['post_id'];
	//var_dump($_REQUEST); <-- works

	// STEP 1 : Handle the upload with jsUpload script
	$log[] = "Step 1: Uploading file";
	
	//$upload_tmp_dir = get_cfg_var('upload_tmp_dir');
	$upload_tmp_dir = dirname(__FILE__).'/temp';
	$tempname = tempnam('', '');
//var_dump($upload_tmp_dir);
//var_dump($tempname);

	// list of valid extensions, ex. array("jpeg", "xml", "bmp")
	$allowedExtensions = array();
	// max file size in bytes
	$sizeLimit = 10 * 1024 * 1024;
	$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
	$log[] = "Uploading file to $upload_tmp_dir";
	$result = $uploader->handleUpload( $upload_tmp_dir.'/' ); // with ending slash
	if (!empty($result['filename']))
	{
		$log[] = "File uploaded to temp directory";
	}else{
		$log[] = "Error uploading file. /temp directory is writtable?";
	}
	//echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
	
	
	// STEP 2 : Handle the upload with WordPress logic

	// Copy to temp
	$log[] = "Step 2: processing file in WordPress";
	//$tempname = $upload_tmp_dir.'/'.$_REQUEST['qqfile'];
	$tempname = $result['filename']; // use the output filename from Step 1
	$basename = $_REQUEST['qqfile'];
	
	// Override values before using wp_handle_upload
	$overrides['test_form'] = FALSE;
	$overrides['test_upload'] = FALSE;
	$overrides['test_type'] = TRUE; // FALSE
	$_file['name'] = $basename;
	$_file['tmp_name'] = $tempname;
	$_file['type'] = $type;
	$_file['size'] = @filesize($tempname);
	//chmod($newtempname, '0777');

	// Use modified wp_handle_upload to store the uploaded file under WordPress
	$fileinfo = dndmedia_wp_handle_upload($_file, $overrides, $time);

	// In case of error throuw it
	if ( isset($fileinfo['error']) )
	{
		return new WP_Error( 'upload_error', $fileinfo['error'] );
	}
	
	$log[] = "File uploaded to WordPress";

	$url = $fileinfo['url'];
	$type = $fileinfo['type'];
	$file = $fileinfo['file'];
	$content = '';	
	
	
	// remove tmp file
	@unlink($tempname);
	
	// STEP 3 :: Process the attachment (see: http://wordpress.stackexchange.com/questions/17870/media-handle-upload-weird-thing )
	$log[] = "Step 3: Attaching image to post";
	$attachment = array('post_mime_type' => $type,'post_title' => $basename, 'post_status' => 'inherit' );
	$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata($attach_id, $file);
	$attach_res = wp_update_attachment_metadata($attach_id, $attach_data);
	
	$log[] = "Ready to send results to client";
	
	$result = array(
			'file' => $file,
			'name' => $basename,
			'url' => $url,
			'type' => $type,
			'attachment_id' => $attach_id,
			'attachment_data' => $attach_data,
			'attachment_result' => $attach_res,
			'log' => $log
			);
			
	echo json_encode($result);
	die(); // this is required to return a proper result
}


add_action('wp_ajax_dndmedia_importurl', 'ajax_dndmedia_importurl_callback');
function ajax_dndmedia_importurl_callback() {

	$url = $_REQUEST['url'];
	$result = array();
	
	if (!empty($url))
	{
		// get URL from external resource
		$context = @stream_context_create(array('http' => array('header'=>'Connection: close'))); 
		$content = @file_get_contents($url);
		
		
		if (!empty($content))
		{
			// content is not empty then process it
			$result = array('file'=> $file, 'url' => $url );
			
			$upload_tmp_dir = dirname(__FILE__).'/temp';
			$tempname = $upload_tmp_dir.'/'.date('Ymdhi').'.tmp';
			file_put_contents( $tempname , $content);
			
			//$basename = "newname.png";
			
			// Curious about what this does? See my comment here: http://stackoverflow.com/questions/2273280/how-to-get-the-last-path-in-the-url/7340428#7340428
			$url_path = parse_url($url, PHP_URL_PATH);
			$parts = explode('/', $url_path);
			$basename = end($parts);
			
			// Override values before using wp_handle_upload
			$overrides['test_form'] = FALSE;
			$overrides['test_upload'] = FALSE;
			$overrides['test_type'] = TRUE; // FALSE
			$_file['name'] = $basename;
			$_file['tmp_name'] = $tempname;
			$_file['type'] = $type;
			$_file['size'] = @filesize($tempname);
			//chmod($newtempname, '0777');

			// Use modified wp_handle_upload to store the uploaded file under WordPress
			$fileinfo = dndmedia_wp_handle_upload($_file, $overrides);

			if ( !isset($fileinfo['error']) )
			{
				$log[] = "File imported from URL and uploaded to WordPress";

				$url = $fileinfo['url'];
				$type = $fileinfo['type'];
				$file = $fileinfo['file'];
				
				// remove tmp file
				@unlink($tempname);
				
				// STEP 3 :: Process the attachment (see: http://wordpress.stackexchange.com/questions/17870/media-handle-upload-weird-thing )
				$log[] = "Step 3: Attaching imported image into post";
				$attachment = array('post_mime_type' => $type,'post_title' => $basename, 'post_status' => 'inherit' );
				$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attach_data = wp_generate_attachment_metadata($attach_id, $file);
				$attach_res = wp_update_attachment_metadata($attach_id, $attach_data);
				
				$log[] = "Ready to send results to client";
				
				$result = array(
						'file' => $file,
						'name' => $basename,
						'url' => $url,
						'type' => $type,
						'attachment_id' => $attach_id,
						'attachment_data' => $attach_data,
						'attachment_result' => $attach_res,
						'log' => $log,
						'op' => 'importurl',
						);
						
			
			} else {
				//return new WP_Error( 'upload_error', $fileinfo['error'] );
				$result['error'][] = "Upload error " . $fileinfo['error'] ;
				
			}
			
		} else {
			
			$result['error'][] = "Empty content error. Cannot import external image";
		}
		
		
	} else {
		$result['error'][] = "Empty URL";
	}
	
	echo json_encode($result);
	die();
}


// Copied from WP and modified
function dndmedia_wp_handle_upload( &$file, $overrides = false, $time = null ) {
	// The default error handler.
	if ( ! function_exists( 'wp_handle_upload_error' ) ) {
		function wp_handle_upload_error( &$file, $message ) {
			return array( 'error'=>$message );
		}
	}

	$file = apply_filters( 'wp_handle_upload_prefilter', $file );

	// You may define your own function and pass the name in $overrides['upload_error_handler']
	$upload_error_handler = 'wp_handle_upload_error';

	// You may have had one or more 'wp_handle_upload_prefilter' functions error out the file.  Handle that gracefully.
	if ( isset( $file['error'] ) && !is_numeric( $file['error'] ) && $file['error'] )
		return $upload_error_handler( $file, $file['error'] );

	// You may define your own function and pass the name in $overrides['unique_filename_callback']
	$unique_filename_callback = null;

	// $_POST['action'] must be set and its value must equal $overrides['action'] or this:
	$action = 'wp_handle_upload';

	// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
	$upload_error_strings = array( false,
		__( "The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>." ),
		__( "The uploaded file exceeds the <em>MAX_FILE_SIZE</em> directive that was specified in the HTML form." ),
		__( "The uploaded file was only partially uploaded." ),
		__( "No file was uploaded." ),
		'',
		__( "Missing a temporary folder." ),
		__( "Failed to write file to disk." ),
		__( "File upload stopped by extension." ));

	// All tests are on by default. Most can be turned off by $override[{test_name}] = false;
	$test_form = true;
	$test_size = true;
	$test_upload = true;

	// If you override this, you must provide $ext and $type!!!!
	$test_type = true;
	$mimes = false;

	// Install user overrides. Did we mention that this voids your warranty?
	if ( is_array( $overrides ) )
		extract( $overrides, EXTR_OVERWRITE );

	// A correct form post will pass this test.
	if ( $test_form && (!isset( $_POST['action'] ) || ($_POST['action'] != $action ) ) )
		return call_user_func($upload_error_handler, $file, __( 'Invalid form submission.' ));

	// A successful upload will pass this test. It makes no sense to override this one.
	if ( $file['error'] > 0 )
		return call_user_func($upload_error_handler, $file, $upload_error_strings[$file['error']] );

	// A non-empty file will pass this test.
	if ( $test_size && !($file['size'] > 0 ) ) {
		if ( is_multisite() )
			$error_msg = __( 'File is empty. Please upload something more substantial.' );
		else
			$error_msg = __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.' );
		return call_user_func($upload_error_handler, $file, $error_msg);
	}

	// A properly uploaded file will pass this test. There should be no reason to override this one.
	if ( $test_upload && ! @ is_uploaded_file( $file['tmp_name'] ) )
		return call_user_func($upload_error_handler, $file, __( 'Specified file failed upload test.' ));

	// A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
	if ( $test_type ) {
		$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );

		extract( $wp_filetype );

		// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
		if ( $proper_filename )
			$file['name'] = $proper_filename;

		if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
			return call_user_func($upload_error_handler, $file, __( 'Sorry, this file type is not permitted for security reasons.' ));

		if ( !$ext )
			$ext = ltrim(strrchr($file['name'], '.'), '.');

		if ( !$type )
			$type = $file['type'];
	} else {
		$type = '';
	}

	// A writable uploads dir will pass this test. Again, there's no point overriding this one.
	if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
		return call_user_func($upload_error_handler, $file, $uploads['error'] );

	$filename = wp_unique_filename( $uploads['path'], $file['name'], $unique_filename_callback );

	// Move the file to the uploads dir
	$new_file = $uploads['path'] . "/$filename";

//added by jm	
$res = copy($file['tmp_name'], $new_file);
if (!$res)
{
return $upload_error_handler( $file, sprintf( __('The uploaded file could not be moved to %s.' ), $uploads['path'] ) );
}

/*	if ( false === @ move_uploaded_file( $file['tmp_name'], $new_file ) )
		return $upload_error_handler( $file, sprintf( __('The uploaded file could not be moved to %s.' ), $uploads['path'] ) );
*/
	// Set correct file permissions
	$stat = stat( dirname( $new_file ));
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );

	// Compute the URL
	$url = $uploads['url'] . "/$filename";

	if ( is_multisite() )
		delete_transient( 'dirsize_cache' );

	return apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ), 'upload' );
}

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        

		
		//jm
/*        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
*/
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
		
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
			$filename = $uploadDirectory . $filename . '.' . $ext;
			//echo $uploadDirectory . $filename . '.' . $ext;
            return array('success'=>true, 'filename'=>$filename);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}

//* end qqq