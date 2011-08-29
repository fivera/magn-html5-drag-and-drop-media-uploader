<?php


function dndmedia_show_ui_settings_page()
{

	$categories = get_categories();

	?>
		<div class="wrap">
			<h2>Magn Ajax Image</h2>
		
			<h3>Settings</h3>
		
			<form name="dndmedia_options" method="POST" action="options.php">
				<?php //wp_nonce_field('update-options'); ?>
				<?php settings_fields( 'dndmedia-settings-group' ); ?>
				<input type="hidden" name="dndmedia_form_action" value="save">
				
				<div>
					<label for="wpsync_spreadsheet_key">Spreadsheet KEY</label>
					<input type="text" name="wpsync_spreadsheet_key" value="<?php echo get_option('wpsync_spreadsheet_key'); ?>" style="width: 400px;" />
				</div>
				
				<div>
					<label for="dndmedia_use_metabox">Use Metabox instead of full page drop facility</label>
					<input type="checkbox" name="dndmedia_use_metabox" value="1" <?php echo (get_option('dndmedia_use_metabox') ? "checked" : "") ?> />
				</div>
				

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
		</div><!-- end wrap-->
	<?
} // end wpsync_show_ui_settings_page



function dndmedia_edit_form_advanced_ui()
{
?>

	<div id="drop-box-overlay"> 
		<h1>Drop files anywhere here to upload:
			<div id="drop-box-jsupload" >
			 Drop files here...
			</div>
		</h1> 
	</div> 

	<div id="upload-box"> 
		<div> 
			<p id="upload-status-text"> 
				Drag and Drop images...
			</p> 
			<p id="upload-details"> 
				Uploads are resized and mirrored for you automatically.
			</p> 
		</div> 
		<div id="upload-status-progressbar"></div> 
	</div> 
	
	<div id="upload-box2"> 
		<div> 
			<p id="upload-status-text"> 
				Drag and Drop images or files here
			</p> 
			<p id="upload-details"> 
				Uploads are resized and mirrored for you automatically.
			</p> 
		</div> 
		<div id="upload-status-progressbar"></div> 
	</div> 
	
	
	
	
	
<?php 
}


function dndmedia_show_metabox_ui()
{
?>
	<div id="dndmedia_meta_box">
		<h2>Start dropping your images</h2>
		
		<div id="dndmedia_status">
			Initial status: waiting...
		</div>
		
		<div id="dndmedia_files">
		</div>
		
		
		<div style="clear:both;"></div>
	
	
		<div>
			<h4>DnD Media Options</h4>
			<div><input type="checkbox" id="dndmedia_sendtoeditor" name="dndmedia_sendtoeditor" value="1" checked="checked"> Auto publish into editor after successful upload</div>
			<div><input type="checkbox" id="dndmedia_attachment" name="dndmedia_attachment" value="1" checked="checked" disabled="disabled"> Auto create attachment(recommended)</div>
			<div>Default attachment size: 
				<select name="dndmedia_attachment_size" id="dndmedia_attachment_size">
					<option value=""></option>
					<?php
						$dndmedia_sizes = get_intermediate_image_sizes();
						var_dump($dndmedia_sizes);
						//$dndmedia_sizes = $_wp_additional_image_sizes;
						foreach ( $dndmedia_sizes as $size_name => $size_attrs ) {
							// Get the image source, width, height, and whether it's intermediate.
							// $image = wp_get_attachment_image_src( get_the_ID(), $size );
							// Add the link to the array if there's an image and if $is_intermediate (4th array value) is true or full size.
							//if ( !empty( $image ) && ( true == $image[3] || 'full' == $size ) ) $links[] = "<a class='image-size-link' href='{$image[0]}'>{$image[1]} &times; {$image[2]}</a>";
							//$size_attrs_str = $size_name.' - w:'. $size_attrs['width'] . ', h:' . $size_attrs['height'] . ', crop:' . $size_attrs['crop'];
							$size_attrs_str = $size_attrs;
							$size_name = $size_attrs;
							echo '<option value="'.$size_name.'">'.$size_attrs_str.'</option>';
						}
					?>
					<option value="">full</option>
				</select>
			</div>
		
			
		</div>
	</div>
<?php
}
