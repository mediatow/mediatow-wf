<?php 
function cff_menu() {
    add_menu_page(
        '',
        'Facebook Feed',
        'manage_options',
        'cff-top',
        'cff_settings_page'
    );
    add_submenu_page(
        'cff-top',
        'Settings',
        'Settings',
        'manage_options',
        'cff-top',
        'cff_settings_page'
    );
}
add_action('admin_menu', 'cff_menu');
//Add styling page
function cff_styling_menu() {
    add_submenu_page(
        'cff-top',
        'Layout & Style',
        'Layout & Style',
        'manage_options',
        'cff-style',
        'cff_style_page'
    );
}
add_action('admin_menu', 'cff_styling_menu');
//Add license page
function cff_license_menu() {
    add_submenu_page('cff-top', 'License', 'License', 'manage_options', 'cff-license', 'cff_license_page');
}
add_action('admin_menu', 'cff_license_menu');
//Create License Page
function cff_license_page() {
    $license = get_option( 'cff_license_key' );
    $status  = get_option( 'cff_license_status' );
    ?>
    <div class="wrap">
        
        <h2><b><?php _e('Custom Facebook Feed License'); ?></b></h2>
        <form method="post" action="options.php">
            
            <?php settings_fields('cff_license'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr valign="top">   
                        <th scope="row" valign="top">
                            <?php _e('Enter your License Key'); ?>
                        </th>
                        <td>
                            <input id="cff_license_key" name="cff_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
                            <?php if( false !== $license ) { ?>
                                <?php if( $status !== false && $status == 'valid' ) { ?>
                                    <?php wp_nonce_field( 'cff_nonce', 'cff_nonce' ); ?>
                                    <input type="submit" class="button-secondary" name="cff_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
                                    <span style="color:green;"><?php _e('Active'); ?></span>
                                <?php } else {
                                    wp_nonce_field( 'cff_nonce', 'cff_nonce' ); ?>
                                    <input type="submit" class="button-secondary" name="cff_license_activate" value="<?php _e('Activate License'); ?>"/>
                                    <span style="color:red;"><?php _e('Inactive'); ?></span>
                                <?php } ?>
                            <?php } ?>
                            <br />
                            <i style="color: #666; font-size: 11px;">The license key you received when you purchased the plugin.</i>
                            <br /><a href="http://smashballoon.com/custom-facebook-feed/support" target="_blank">Renew my license</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        
        </form>
    <?php
}
function cff_register_option() {
    // creates our settings in the options table
    register_setting('cff_license', 'cff_license_key', 'cff_sanitize_license' );
}
add_action('admin_init', 'cff_register_option');
function cff_sanitize_license( $new ) {
    $old = get_option( 'cff_license_key' );
    if( $old && $old != $new ) {
        delete_option( 'cff_license_status' ); // new license has been entered, so must reactivate
    }
    return $new;
}
function cff_activate_license() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_activate'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce', 'cff_nonce' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license = trim( get_option( 'cff_license_key' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'activate_license', 
            'license'   => $license, 
            'item_name' => urlencode( WPW_SL_ITEM_NAME ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response = wp_remote_get( add_query_arg( $api_params, WPW_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response ) )
            return false;
        // decode the license data
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        // $license_data->license will be either "active" or "inactive"
        update_option( 'cff_license_status', $license_data->license );
    }
}
add_action('admin_init', 'cff_activate_license');
function cff_deactivate_license() {
    // listen for our activate button to be clicked
    if( isset( $_POST['cff_license_deactivate'] ) ) {
        // run a quick security check 
        if( ! check_admin_referer( 'cff_nonce', 'cff_nonce' ) )   
            return; // get out if we didn't click the Activate button
        // retrieve the license from the database
        $license = trim( get_option( 'cff_license_key' ) );
            
        // data to send in our API request
        $api_params = array( 
            'edd_action'=> 'deactivate_license', 
            'license'   => $license, 
            'item_name' => urlencode( WPW_SL_ITEM_NAME ) // the name of our product in EDD
        );
        
        // Call the custom API.
        $response = wp_remote_get( add_query_arg( $api_params, WPW_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
        // make sure the response came back okay
        if ( is_wp_error( $response ) )
            return false;
        // decode the license data
        $license_data = json_decode( wp_remote_retrieve_body( $response ) );
        
        // $license_data->license will be either "deactivated" or "failed"
        if( $license_data->license == 'deactivated' )
            delete_option( 'cff_license_status' );
    }
}
add_action('admin_init', 'cff_deactivate_license'); 
//Create Settings page
function cff_settings_page() {
    //Declare variables for fields
    $hidden_field_name  = 'cff_submit_hidden';
    $access_token       = 'cff_access_token';
    $page_id            = 'cff_page_id';
    $num_show           = 'cff_num_show';
    $cff_show_others    = 'cff_show_others';

    // Read in existing option value from database
    $access_token_val = get_option( $access_token );
    $page_id_val = get_option( $page_id );
    $num_show_val = get_option( $num_show );
    $cff_show_others_val = get_option( $cff_show_others );
    // See if the user has posted us some information. If they did, this hidden field will be set to 'Y'.
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $access_token_val = $_POST[ $access_token ];
        $page_id_val = $_POST[ $page_id ];
        $num_show_val = $_POST[ $num_show ];
        $cff_show_others_val = $_POST[ $cff_show_others ];
        // Save the posted value in the database
        update_option( $access_token, $access_token_val );
        update_option( $page_id, $page_id_val );
        update_option( $num_show, $num_show_val );
        update_option( $cff_show_others, $cff_show_others_val );
        // Put an settings updated message on the screen 
    ?>
    <div class="updated"><p><strong><?php _e('Settings saved.', 'custom-facebook-feed' ); ?></strong></p></div>
    <?php } ?> 
 
    <div class="wrap">
        <h2><b><?php _e('Custom Facebook Feed Settings'); ?></b></h2>
        <form name="form1" method="post" action="">
            <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
            <br />
            <hr />
            <h3><?php _e('Configuration'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e('Access Token'); ?></th>
                        <td>
                            <input name="cff_access_token" type="text" value="<?php esc_attr_e( $access_token_val ); ?>" size="60" />
                            <a href="http://smashballoon.com/custom-facebook-feed/access-token/" target="_blank">How to get an Access Token</a>
                            <br /><i style="color: #666; font-size: 11px;">Eg. 1234567890123|ABC2fvp5h9tJe4-5-AbC123</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Facebook Page ID'); ?></th>
                        <td>
                            <input name="cff_page_id" type="text" value="<?php esc_attr_e( $page_id_val ); ?>" size="60" />
                            <a href="http://smashballoon.com/custom-facebook-feed/faq/" target="_blank">What's my Page ID?</a>
                            <br /><i style="color: #666; font-size: 11px;">Eg. 1234567890123 or smashballoon</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Number of posts to display'); ?></th>
                        <td>
                            <input name="cff_num_show" type="text" value="<?php esc_attr_e( $num_show_val ); ?>" size="4" />
                            <i style="color: #666; font-size: 11px;">Eg. 5</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Show posts by others on my page'); ?></th>
                        <td>
                            <input name="cff_show_others" type="checkbox" id="cff_show_others" <?php if($cff_show_others_val == true) echo "checked"; ?> />
                            <i style="color: #666; font-size: 11px;">By default only posts by the page owner will be shown. Check this box to also show posts by others.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
        <hr />
        <h3>Displaying your Feed</h3>
        <p>Copy and paste this shortcode directly into the page, post or widget where you'd like the feed to show up:</p>
        <input type="text" value="[custom-facebook-feed]" size="22" readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="edd-sysinfo" title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac)." />
        <p>You can override the settings above directly in the shortcode like so:</p>
        <p>[custom-facebook-feed <b><span style='color: purple;'>id=Put_Your_Facebook_Page_ID_Here</span> <span style='color: green;'>num=3</span> <span style='color: blue;'>layout=thumb</span></b>]</p>
        <p><a href="http://smashballoon.com/custom-facebook-feed/docs/shortcodes/" target="_blank">Click here</a> for a full list of shortcode options</p>
        <hr />
        <br />
        <a href="http://smashballoon.com/custom-facebook-feed/support/" target="_blank">Plugin Support</a><i style="color: #666; font-size: 11px; margin-left: 5px;">(If any of the items below are listed as <span style='color: red;'>No</span> then please include this in your support request)</i></span>
        <h4><?php _e('<u>System Info:</u>'); ?></h4>
        <p>PHP Version:          <b><?php echo PHP_VERSION . "\n"; ?></b></p>
        <p>Web Server Info:      <b><?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?></b></p>
        <p>PHP allow_url_fopen:  <b><?php echo ini_get( 'allow_url_fopen' ) ? "<span style='color: green;'>Yes</span>" : "<span style='color: red;'>No</span>"; ?></b></p>
        <p>PHP cURL:             <b><?php echo is_callable('curl_init') ? "<span style='color: green;'>Yes</span>" : "<span style='color: red;'>No</span>" ?></b></p>
        <p>JSON:                 <b><?php echo function_exists("json_decode") ? "<span style='color: green;'>Yes</span>" : "<span style='color: red;'>No</span>" ?></b></p>
        
        
<?php 
} //End Settings_Page 
//Create Style page
function cff_style_page() {
    //Declare variables for fields
    $style_hidden_field_name    = 'cff_style_submit_hidden';
    $defaults = array(
        //Post types
        'cff_show_links_type'       => true,
        'cff_show_event_type'       => true,
        'cff_show_video_type'       => true,
        'cff_show_photos_type'      => true,
        'cff_show_status_type'      => true,
        //Layout
        'cff_preset_layout'         => 'thumb',
        //Include
        'cff_show_text'             => true,
        'cff_show_desc'             => true,
        'cff_show_shared_links'     => true,
        'cff_show_date'             => true,
        'cff_show_media'            => true,
        'cff_show_event_title'      => true,
        'cff_show_event_details'    => true,
        'cff_show_meta'             => true,
        'cff_show_link'             => true,
        'cff_show_like_box'         => true,
        //Typography
        'cff_title_format'          => 'p',
        'cff_title_size'            => 'inherit',
        'cff_title_weight'          => 'inherit',
        'cff_title_color'           => '',
        'cff_body_size'             => 'inherit',
        'cff_body_weight'           => 'inherit',
        'cff_body_color'            => '',
        'cff_event_title_format'    => 'p',
        'cff_event_title_size'      => 'inherit',
        'cff_event_title_weight'    => 'inherit',
        'cff_event_title_color'     => '',
        'cff_event_details_size'    => 'inherit',
        'cff_event_details_weight'  => 'inherit',
        'cff_event_details_color'   => '',
        'cff_event_date_formatting' => '1',
        'cff_event_date_custom'     => '',

        //Date
        'cff_date_position'         => 'below',
        'cff_date_size'             => 'inherit',
        'cff_date_weight'           => 'inherit',
        'cff_date_color'            => '',
        'cff_date_formatting'       => '1',
        'cff_date_custom'           => '',
        'cff_date_before'           => '',
        'cff_date_after'            => '',
        //Link to Facebook
        'cff_link_size'             => 'inherit',
        'cff_link_weight'           => 'inherit',
        'cff_link_color'            => '',
        'cff_facebook_link_text'    => 'View on Facebook',
        'cff_view_link_text'        => 'View Link',
        //Meta
        'cff_icon_style'            => 'light',
        'cff_meta_text_color'       => '',
        'cff_meta_bg_color'         => '',
        'cff_nocomments_text'       => 'No comments yet',
        'cff_hide_comments'         => '',
        //Misc
        'cff_feed_width'            => '',
        'cff_feed_height'           => '',
        'cff_feed_padding'          => '',
        'cff_like_box_position'     => 'bottom',
        'cff_bg_color'              => '',
        'cff_likebox_bg_color'      => '',
        'cff_video_height'          => '',
        'cff_show_author'           => false,
        //New
        'cff_custom_css'            => '',
        'cff_title_link'            => false,
        'cff_event_title_link'      => false,
        'cff_video_action'          => 'file',
        'cff_sep_color'             => '',
        'cff_sep_size'              => '1'
    );
    //Save layout option in an array
    add_option( 'cff_style_settings', $options );
    $options = wp_parse_args(get_option('cff_style_settings'), $defaults);
    //Set the page variables
    //Post types
    $cff_show_links_type = $options[ 'cff_show_links_type' ];
    $cff_show_event_type = $options[ 'cff_show_event_type' ];
    $cff_show_video_type = $options[ 'cff_show_video_type' ];
    $cff_show_photos_type = $options[ 'cff_show_photos_type' ];
    $cff_show_status_type = $options[ 'cff_show_status_type' ];
    //Layout
    $cff_preset_layout = $options[ 'cff_preset_layout' ];
    $cff_media_size = $options[ 'cff_media_size' ];
    //Include
    $cff_show_text = $options[ 'cff_show_text' ];
    $cff_show_desc = $options[ 'cff_show_desc' ];
    $cff_show_shared_links = $options[ 'cff_show_shared_links' ];
    $cff_show_date = $options[ 'cff_show_date' ];
    $cff_show_media = $options[ 'cff_show_media' ];
    $cff_show_event_title = $options[ 'cff_show_event_title' ];
    $cff_show_event_details = $options[ 'cff_show_event_details' ];
    $cff_show_meta = $options[ 'cff_show_meta' ];
    $cff_show_link = $options[ 'cff_show_link' ];
    $cff_show_like_box = $options[ 'cff_show_like_box' ];
    //Typography
    $cff_title_format = $options[ 'cff_title_format' ];
    $cff_title_size = $options[ 'cff_title_size' ];
    $cff_title_weight = $options[ 'cff_title_weight' ];
    $cff_title_color = $options[ 'cff_title_color' ];
    $cff_body_size = $options[ 'cff_body_size' ];
    $cff_body_weight = $options[ 'cff_body_weight' ];
    $cff_body_color = $options[ 'cff_body_color' ];
    $cff_event_title_format = $options[ 'cff_event_title_format' ];
    $cff_event_title_size = $options[ 'cff_event_title_size' ];
    $cff_event_title_weight = $options[ 'cff_event_title_weight' ];
    $cff_event_title_color = $options[ 'cff_event_title_color' ];
    $cff_event_details_size = $options[ 'cff_event_details_size' ];
    $cff_event_details_weight = $options[ 'cff_event_details_weight' ];
    $cff_event_details_color = $options[ 'cff_event_details_color' ];
    $cff_event_date_formatting = $options[ 'cff_event_date_formatting' ];
    $cff_event_date_custom = $options[ 'cff_event_date_custom' ];

    //Date
    $cff_date_position = $options[ 'cff_date_position' ];
    $cff_date_size = $options[ 'cff_date_size' ];
    $cff_date_weight = $options[ 'cff_date_weight' ];
    $cff_date_color = $options[ 'cff_date_color' ];
    $cff_date_formatting = $options[ 'cff_date_formatting' ];
    $cff_date_custom = $options[ 'cff_date_custom' ];
    $cff_date_before = $options[ 'cff_date_before' ];
    $cff_date_after = $options[ 'cff_date_after' ];
    //View on Facebook link
    $cff_link_size = $options[ 'cff_link_size' ];
    $cff_link_weight = $options[ 'cff_link_weight' ];
    $cff_link_color = $options[ 'cff_link_color' ];
    $cff_facebook_link_text = $options[ 'cff_facebook_link_text' ];
    $cff_view_link_text = $options[ 'cff_view_link_text' ];
    //Meta
    $cff_icon_style = $options[ 'cff_icon_style' ];
    $cff_meta_text_color = $options[ 'cff_meta_text_color' ];
    $cff_meta_bg_color = $options[ 'cff_meta_bg_color' ];
    $cff_nocomments_text = $options[ 'cff_nocomments_text' ];
    $cff_hide_comments = $options[ 'cff_hide_comments' ];
    //Misc
    $cff_feed_width = $options[ 'cff_feed_width' ];
    $cff_feed_height = $options[ 'cff_feed_height' ];
    $cff_feed_padding = $options[ 'cff_feed_padding' ];
    $cff_like_box_position = $options[ 'cff_like_box_position' ];
    $cff_show_media = $options[ 'cff_show_media' ];
    $cff_open_links = $options[ 'cff_open_links' ];
    $cff_bg_color = $options[ 'cff_bg_color' ];
    $cff_likebox_bg_color = $options[ 'cff_likebox_bg_color' ];
    $cff_video_height = $options[ 'cff_video_height' ];
    $cff_show_author = $options[ 'cff_show_author' ];
    //New
    $cff_custom_css = $options[ 'cff_custom_css' ];
    $cff_title_link = $options[ 'cff_title_link' ];
    $cff_event_title_link = $options[ 'cff_event_title_link' ];
    $cff_video_action = $options[ 'cff_video_action' ];
    $cff_sep_color = $options[ 'cff_sep_color' ];
    $cff_sep_size = $options[ 'cff_sep_size' ];
	
	// Texts lengths
	$cff_title_length   = 'cff_title_length';
    $cff_body_length    = 'cff_body_length';
    // Read in existing option value from database
    $cff_title_length_val = get_option( $cff_title_length );
    $cff_body_length_val = get_option( $cff_body_length );
    // See if the user has posted us some information. If they did, this hidden field will be set to 'Y'.
    if( isset($_POST[ $style_hidden_field_name ]) && $_POST[ $style_hidden_field_name ] == 'Y' ) {
		// Read their posted value
        $cff_title_length_val = $_POST[ $cff_title_length ];
        $cff_body_length_val = $_POST[ $cff_body_length ];
        // Save the posted value in the database
        update_option( $cff_title_length, $cff_title_length_val );
        update_option( $cff_body_length, $cff_body_length_val );
    
        //Update the page variable
        //Post types
        $cff_show_links_type = $_POST[ 'cff_show_links_type' ];
        $cff_show_event_type = $_POST[ 'cff_show_event_type' ];
        $cff_show_video_type = $_POST[ 'cff_show_video_type' ];
        $cff_show_photos_type = $_POST[ 'cff_show_photos_type' ];
        $cff_show_status_type = $_POST[ 'cff_show_status_type' ];
        //Layout
        $cff_preset_layout = $_POST[ 'cff_preset_layout' ];
        $cff_media_size = $_POST[ 'cff_media_size' ];
        //Include
        $cff_show_text = $_POST[ 'cff_show_text' ];
        $cff_show_desc = $_POST[ 'cff_show_desc' ];
        $cff_show_shared_links = $_POST[ 'cff_show_shared_links' ];
        $cff_show_date = $_POST[ 'cff_show_date' ];
        $cff_show_media = $_POST[ 'cff_show_media' ];
        $cff_show_event_title = $_POST[ 'cff_show_event_title' ];
        $cff_show_event_details = $_POST[ 'cff_show_event_details' ];
        $cff_show_meta = $_POST[ 'cff_show_meta' ];
        $cff_show_link = $_POST[ 'cff_show_link' ];
        $cff_show_like_box = $_POST[ 'cff_show_like_box' ];
        //Typography
        $cff_title_format = $_POST[ 'cff_title_format' ];
        $cff_title_size = $_POST[ 'cff_title_size' ];
        $cff_title_weight = $_POST[ 'cff_title_weight' ];
        $cff_title_color = $_POST[ 'cff_title_color' ];
        $cff_body_size = $_POST[ 'cff_body_size' ];
        $cff_body_weight = $_POST[ 'cff_body_weight' ];
        $cff_body_color = $_POST[ 'cff_body_color' ];
        $cff_event_title_format = $_POST[ 'cff_event_title_format' ];
        $cff_event_title_size = $_POST[ 'cff_event_title_size' ];
        $cff_event_title_weight = $_POST[ 'cff_event_title_weight' ];
        $cff_event_title_color = $_POST[ 'cff_event_title_color' ];
        $cff_event_details_size = $_POST[ 'cff_event_details_size' ];
        $cff_event_details_weight = $_POST[ 'cff_event_details_weight' ];
        $cff_event_details_color = $_POST[ 'cff_event_details_color' ];
        $cff_event_date_formatting = $_POST[ 'cff_event_date_formatting' ];
        $cff_event_date_custom = $_POST[ 'cff_event_date_custom' ];

        //Date
        $cff_date_position = $_POST[ 'cff_date_position' ];
        $cff_date_size = $_POST[ 'cff_date_size' ];
        $cff_date_weight = $_POST[ 'cff_date_weight' ];
        $cff_date_color = $_POST[ 'cff_date_color' ];
        $cff_date_formatting = $_POST[ 'cff_date_formatting' ];
        $cff_date_custom = $_POST[ 'cff_date_custom' ];
        $cff_date_before = $_POST[ 'cff_date_before' ];
        $cff_date_after = $_POST[ 'cff_date_after' ];
        //View on Facebook link
        $cff_link_size = $_POST[ 'cff_link_size' ];
        $cff_link_weight = $_POST[ 'cff_link_weight' ];
        $cff_link_color = $_POST[ 'cff_link_color' ];
        $cff_facebook_link_text = $_POST[ 'cff_facebook_link_text' ];
        $cff_view_link_text = $_POST[ 'cff_view_link_text' ];
        //Meta
        $cff_icon_style = $_POST[ 'cff_icon_style' ];
        $cff_meta_text_color = $_POST[ 'cff_meta_text_color' ];
        $cff_meta_bg_color = $_POST[ 'cff_meta_bg_color' ];
        $cff_nocomments_text = $_POST[ 'cff_nocomments_text' ];
        $cff_hide_comments = $_POST[ 'cff_hide_comments' ];
        //Misc
        $cff_feed_width = $_POST[ 'cff_feed_width' ];
        $cff_feed_height = $_POST[ 'cff_feed_height' ];
        $cff_feed_padding = $_POST[ 'cff_feed_padding' ];
        $cff_like_box_position = $_POST[ 'cff_like_box_position' ];
        $cff_show_media = $_POST[ 'cff_show_media' ];
        $cff_open_links = $_POST[ 'cff_open_links' ];
        $cff_bg_color = $_POST[ 'cff_bg_color' ];
        $cff_likebox_bg_color = $_POST[ 'cff_likebox_bg_color' ];
        $cff_video_height = $_POST[ 'cff_video_height' ];
        $cff_show_author = $_POST[ 'cff_show_author' ];
        //New
        $cff_custom_css = $_POST[ 'cff_custom_css' ];
        $cff_title_link = $_POST[ 'cff_title_link' ];
        $cff_event_title_link = $_POST[ 'cff_event_title_link' ];
        $cff_video_action = $_POST[ 'cff_video_action' ];
        $cff_sep_color = $_POST[ 'cff_sep_color' ];
        $cff_sep_size = $_POST[ 'cff_sep_size' ];
        //Update the option in the array in the database
        //Post types
        $options[ 'cff_show_links_type' ] = $cff_show_links_type;
        $options[ 'cff_show_event_type' ] = $cff_show_event_type;
        $options[ 'cff_show_video_type' ] = $cff_show_video_type;
        $options[ 'cff_show_photos_type' ] = $cff_show_photos_type;
        $options[ 'cff_show_status_type' ] = $cff_show_status_type;
        //Layout
        $options[ 'cff_preset_layout' ] = $cff_preset_layout;
        $options[ 'cff_media_size' ] = $cff_media_size;
        //Include
        $options[ 'cff_show_text' ] = $cff_show_text;
        $options[ 'cff_show_desc' ] = $cff_show_desc;
        $options[ 'cff_show_shared_links' ] = $cff_show_shared_links;
        $options[ 'cff_show_date' ] = $cff_show_date;
        $options[ 'cff_show_media' ] = $cff_show_media;
        $options[ 'cff_show_event_title' ] = $cff_show_event_title;
        $options[ 'cff_show_event_details' ] = $cff_show_event_details;
        $options[ 'cff_show_meta' ] = $cff_show_meta;
        $options[ 'cff_show_link' ] = $cff_show_link;
        $options[ 'cff_show_like_box' ] = $cff_show_like_box;
        //Typography
        $options[ 'cff_title_format' ] = $cff_title_format;
        $options[ 'cff_title_size' ] = $cff_title_size;
        $options[ 'cff_title_weight' ] = $cff_title_weight;
        $options[ 'cff_title_color' ] = $cff_title_color;
        $options[ 'cff_body_size' ] = $cff_body_size;
        $options[ 'cff_body_weight' ] = $cff_body_weight;
        $options[ 'cff_body_color' ] = $cff_body_color;
        $options[ 'cff_event_title_format' ] = $cff_event_title_format;
        $options[ 'cff_event_title_size' ] = $cff_event_title_size;
        $options[ 'cff_event_title_weight' ] = $cff_event_title_weight;
        $options[ 'cff_event_title_color' ] = $cff_event_title_color;
        $options[ 'cff_event_details_size' ] = $cff_event_details_size;
        $options[ 'cff_event_details_weight' ] = $cff_event_details_weight;
        $options[ 'cff_event_details_color' ] = $cff_event_details_color;
        $options[ 'cff_event_date_formatting' ] = $cff_event_date_formatting;
        $options[ 'cff_event_date_custom' ] = $cff_event_date_custom;
        
        //Date
        $options[ 'cff_date_position' ] = $cff_date_position;
        $options[ 'cff_date_size' ] = $cff_date_size;
        $options[ 'cff_date_weight' ] = $cff_date_weight;
        $options[ 'cff_date_color' ] = $cff_date_color;
        $options[ 'cff_date_formatting' ] = $cff_date_formatting;
        $options[ 'cff_date_custom' ] = $cff_date_custom;
        $options[ 'cff_date_before' ] = $cff_date_before;
        $options[ 'cff_date_after' ] = $cff_date_after;
        //Link
        $options[ 'cff_link_size' ] = $cff_link_size;
        $options[ 'cff_link_weight' ] = $cff_link_weight;
        $options[ 'cff_link_color' ] = $cff_link_color;
        $options[ 'cff_facebook_link_text' ] = $cff_facebook_link_text;
        $options[ 'cff_view_link_text' ] = $cff_view_link_text;
        //Meta
        $options[ 'cff_icon_style' ] = $cff_icon_style;
        $options[ 'cff_meta_text_color' ] = $cff_meta_text_color;
        $options[ 'cff_meta_bg_color' ] = $cff_meta_bg_color;
        $options[ 'cff_nocomments_text' ] = $cff_nocomments_text;
        $options[ 'cff_hide_comments' ] = $cff_hide_comments;
        //Misc
        $options[ 'cff_feed_width' ] = $cff_feed_width;
        $options[ 'cff_feed_height' ] = $cff_feed_height;
        $options[ 'cff_feed_padding' ] = $cff_feed_padding;
        $options[ 'cff_like_box_position' ] = $cff_like_box_position;
        $options[ 'cff_show_media' ] = $cff_show_media;
        $options[ 'cff_open_links' ] = $cff_open_links;
        $options[ 'cff_bg_color' ] = $cff_bg_color;
        $options[ 'cff_likebox_bg_color' ] = $cff_likebox_bg_color;
        $options[ 'cff_video_height' ] = $cff_video_height;
        $options[ 'cff_show_author' ] = $cff_show_author;
        //New
        $options[ 'cff_custom_css' ] = $cff_custom_css;
        $options[ 'cff_title_link' ] = $cff_title_link;
        $options[ 'cff_event_title_link' ] = $cff_event_title_link;
        $options[ 'cff_video_action' ] = $cff_video_action;
        $options[ 'cff_sep_color' ] = $cff_sep_color;
        $options[ 'cff_sep_size' ] = $cff_sep_size;
        //Update the array
        update_option( 'cff_style_settings', $options );
        // Put an settings updated message on the screen 
    ?>
    <div class="updated"><p><strong><?php _e('Settings saved.', 'custom-facebook-feed' ); ?></strong></p></div>
    <?php } ?> 
 
    <div class="wrap">
        <h2><b><?php _e('Custom Facebook Feed - Layout and Style'); ?></b></h2>
        <form name="form1" method="post" action="">
            <input type="hidden" name="<?php echo $style_hidden_field_name; ?>" value="Y">
            <br />
            <hr />
            <table class="form-table">
                <tbody>
                    <h3><?php _e('General'); ?></h3>
                    <tr valign="top">
                        <th scope="row"><?php _e('Feed Width'); ?></th>
                        <td>
                            <input name="cff_feed_width" type="text" value="<?php esc_attr_e( $cff_feed_width ); ?>" size="6" />
                            <span>Eg. 500px, 50%, 10em.  <i style="color: #666; font-size: 11px; margin-left: 5px;">Default is 100%</i></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Feed Height'); ?></th>
                        <td>
                            <input name="cff_feed_height" type="text" value="<?php esc_attr_e( $cff_feed_height ); ?>" size="6" />
                            <span>Eg. 500px, 50em. <i style="color: #666; font-size: 11px; margin-left: 5px;">Leave empty to set no maximum height. If the feed exceeds this height then a scroll bar will be used.</i></span>
                        </td>
                    </tr>
                        <th scope="row"><?php _e('Feed Padding'); ?></th>
                        <td>
                            <input name="cff_feed_padding" type="text" value="<?php esc_attr_e( $cff_feed_padding ); ?>" size="6" />
                            <span>Eg. 20px, 5%. <i style="color: #666; font-size: 11px; margin-left: 5px;">This is the amount of padding/spacing that goes around the feed. This is particularly useful if you intend to set a background color on the feed.</i></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Feed Background Color'); ?></th>
                        <td>
                            <label for="cff_bg_color">#</label>
                            <input name="cff_bg_color" type="text" value="<?php esc_attr_e( $cff_bg_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <span><a href="http://www.colorpicker.com/" target="_blank">Color Picker</a></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Show name and picture of author'); ?></th>
                        <td>
                            <input name="cff_show_author" type="checkbox" id="cff_show_author" <?php if($cff_show_author == true) echo "checked"; ?> />
                            <label for="cff_show_status_type">Yes</label>
                            <i style="color: #666; font-size: 11px; margin-left: 5px;">This will show the thumbnail picture and name of the post author at the top of each post</i>
                            
                        </td>
                    </tr>
                </tbody>
            </table>
            <br />
            <hr />
            <table class="form-table">
                <tbody>
                    <h3><?php _e('Post Types'); ?></h3>
                    <tr valign="top">
                        <th scope="row"><?php _e('Only show these types of posts:'); ?></th>
                        <td>
                            <div>
                                <input name="cff_show_status_type" type="checkbox" id="cff_show_status_type" <?php if($cff_show_status_type == true) echo "checked"; ?> />
                                <label for="cff_show_status_type">Statuses</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_event_type" id="cff_show_event_type" <?php if($cff_show_event_type == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_event_type">Events</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_photos_type" id="cff_show_photos_type" <?php if($cff_show_photos_type == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_photos_type">Photos</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_video_type" id="cff_show_video_type" <?php if($cff_show_video_type == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_video_type">Videos</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_links_type" id="cff_show_links_type" <?php if($cff_show_links_type == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_links_type">Links</label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
            <hr />
            <h3><?php _e('Post Layout'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td><p>Choose a layout from the 3 below:</p></td>
                        <td>
                            <select name="cff_preset_layout">
                                <option value="thumb" <?php if($cff_preset_layout == "thumb") echo 'selected="selected"' ?> >Thumbnail</option>
                                <option value="half" <?php if($cff_preset_layout == "half") echo 'selected="selected"' ?> >Half-width</option>
                                <option value="full" <?php if($cff_preset_layout == "full") echo 'selected="selected"' ?> >Full-width</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Thumbnail:'); ?></th>
                        <td>
                            <img src="<?php echo plugins_url( 'img/layout-thumb.png' , __FILE__ ) ?>" alt="Thumbnail Layout" width="400px" style="border: 1px solid #ccc;" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Half-width:'); ?></th>
                        <td>
                            <img src="<?php echo plugins_url( 'img/layout-half.png' , __FILE__ ) ?>" alt="Half Width Layout" width="400px" style="border: 1px solid #ccc;" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Full-width:'); ?></th>
                        <td>
                            <img src="<?php echo plugins_url( 'img/layout-full.png' , __FILE__ ) ?>" alt="Full Width Layout" width="400px" style="border: 1px solid #ccc;" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Include the following in posts (when applicable):'); ?></th>
                        <td>
                            <div>
                                <input name="cff_show_text" type="checkbox" id="cff_show_text" <?php if($cff_show_text == true) echo "checked"; ?> />
                                <label for="cff_show_text">Post text</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_desc" id="cff_show_desc" <?php if($cff_show_desc == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_desc">Photo/Video description</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_shared_links" id="cff_show_shared_links" <?php if($cff_show_shared_links == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_shared_links">Shared links</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_date" id="cff_show_date" <?php if($cff_show_date == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_date">Date</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_media" id="cff_show_media" <?php if($cff_show_media == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_media">Photos/videos</label>
                            </div>
                            
                            <div>
                                <input type="checkbox" name="cff_show_event_title" id="cff_show_event_title" <?php if($cff_show_event_title == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_event_title">Event title</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_event_details" id="cff_show_event_details" <?php if($cff_show_event_details == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_event_details">Event details</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_meta" id="cff_show_meta" <?php if($cff_show_meta == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_meta">Like/shares/comments</label>
                            </div>
                            <div>
                                <input type="checkbox" name="cff_show_link" id="cff_show_link" <?php if($cff_show_link == true) echo 'checked="checked"' ?> />
                                <label for="cff_show_link">View on Facebook/View Link</label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <?php submit_button(); ?>
            <hr />
            <h3><?php _e('Typography'); ?></h3>
            <p><i style="color: #666; font-size: 11px; margin-left: 5px;">'Inherit' means that the text will inherit the styles from your theme.</i></p>
            <table class="form-table">
                <tbody>
                    <tr><td><b style="font-size: 14px;"><?php _e('Post Text'); ?></b></td></tr>
                    <tr>
                        <th><label for="cff_title_format" class="bump-left">Format</label></th>
                        <td>
                            <select name="cff_title_format">
                                <option value="p" <?php if($cff_title_format == "p") echo 'selected="selected"' ?> >Paragraph</option>
                                <option value="h3" <?php if($cff_title_format == "h3") echo 'selected="selected"' ?> >Heading 3</option>
                                <option value="h4" <?php if($cff_title_format == "h4") echo 'selected="selected"' ?> >Heading 4</option>
                                <option value="h5" <?php if($cff_title_format == "h5") echo 'selected="selected"' ?> >Heading 5</option>
                                <option value="h6" <?php if($cff_title_format == "h6") echo 'selected="selected"' ?> >Heading 6</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_title_size" class="bump-left">Text Size</label></th>
                        <td>
                            <select name="cff_title_size">
                                <option value="inherit" <?php if($cff_title_size == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="10" <?php if($cff_title_size == "10") echo 'selected="selected"' ?> >10px</option>
                                <option value="11" <?php if($cff_title_size == "11") echo 'selected="selected"' ?> >11px</option>
                                <option value="12" <?php if($cff_title_size == "12") echo 'selected="selected"' ?> >12px</option>
                                <option value="14" <?php if($cff_title_size == "14") echo 'selected="selected"' ?> >14px</option>
                                <option value="16" <?php if($cff_title_size == "16") echo 'selected="selected"' ?> >16px</option>
                                <option value="18" <?php if($cff_title_size == "18") echo 'selected="selected"' ?> >18px</option>
                                <option value="20" <?php if($cff_title_size == "20") echo 'selected="selected"' ?> >20px</option>
                                <option value="24" <?php if($cff_title_size == "24") echo 'selected="selected"' ?> >24px</option>
                                <option value="28" <?php if($cff_title_size == "28") echo 'selected="selected"' ?> >28px</option>
                                <option value="32" <?php if($cff_title_size == "32") echo 'selected="selected"' ?> >32px</option>
                                <option value="36" <?php if($cff_title_size == "36") echo 'selected="selected"' ?> >36px</option>
                                <option value="42" <?php if($cff_title_size == "42") echo 'selected="selected"' ?> >42px</option>
                                <option value="48" <?php if($cff_title_size == "48") echo 'selected="selected"' ?> >48px</option>
                                <option value="60" <?php if($cff_title_size == "54") echo 'selected="selected"' ?> >54px</option>
                                <option value="60" <?php if($cff_title_size == "60") echo 'selected="selected"' ?> >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_title_weight" class="bump-left">Text Weight</label></th>
                        <td>
                            <select name="cff_title_weight">
                                <option value="inherit" <?php if($cff_title_weight == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="normal" <?php if($cff_title_weight == "normal") echo 'selected="selected"' ?> >Normal</option>
                                <option value="bold" <?php if($cff_title_weight == "bold") echo 'selected="selected"' ?> >Bold</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_title_color" class="bump-left">Text Color</label></th>
                        <td>
                            #<input name="cff_title_color" type="text" value="<?php esc_attr_e( $cff_title_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <span><a href="http://www.colorpicker.com/" target="_blank">Color Picker</a></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_title_link" class="bump-left">Link text to Facebook post?</label></th>
                        <td><input type="checkbox" name="cff_title_link" id="cff_title_link" <?php if($cff_title_link == true) echo 'checked="checked"' ?> />&nbsp;Yes</td>
                    </tr>
                            
                    <tr><td><b style="font-size: 14px;"><?php _e('Photo/Video Description'); ?></b></td></tr>
                    
                    <tr>
                        <th><label for="cff_body_size" class="bump-left">Text Size</label></th>
                        <td>
                            <select name="cff_body_size">
                                <option value="inherit" <?php if($cff_body_size == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="10" <?php if($cff_body_size == "10") echo 'selected="selected"' ?> >10px</option>
                                <option value="11" <?php if($cff_body_size == "11") echo 'selected="selected"' ?> >11px</option>
                                <option value="12" <?php if($cff_body_size == "12") echo 'selected="selected"' ?> >12px</option>
                                <option value="14" <?php if($cff_body_size == "14") echo 'selected="selected"' ?> >14px</option>
                                <option value="16" <?php if($cff_body_size == "16") echo 'selected="selected"' ?> >16px</option>
                                <option value="18" <?php if($cff_body_size == "18") echo 'selected="selected"' ?> >18px</option>
                                <option value="20" <?php if($cff_body_size == "20") echo 'selected="selected"' ?> >20px</option>
                                <option value="24" <?php if($cff_body_size == "24") echo 'selected="selected"' ?> >24px</option>
                                <option value="28" <?php if($cff_body_size == "28") echo 'selected="selected"' ?> >28px</option>
                                <option value="32" <?php if($cff_body_size == "32") echo 'selected="selected"' ?> >32px</option>
                                <option value="36" <?php if($cff_body_size == "36") echo 'selected="selected"' ?> >36px</option>
                                <option value="42" <?php if($cff_body_size == "42") echo 'selected="selected"' ?> >42px</option>
                                <option value="48" <?php if($cff_body_size == "48") echo 'selected="selected"' ?> >48px</option>
                                <option value="60" <?php if($cff_body_size == "54") echo 'selected="selected"' ?> >54px</option>
                                <option value="60" <?php if($cff_body_size == "60") echo 'selected="selected"' ?> >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_body_weight" class="bump-left">Text Weight</label></th>
                        <td>
                            <select name="cff_body_weight">
                                <option value="inherit" <?php if($cff_body_weight == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="normal" <?php if($cff_body_weight == "normal") echo 'selected="selected"' ?> >Normal</option>
                                <option value="bold" <?php if($cff_body_weight == "bold") echo 'selected="selected"' ?> >Bold</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_body_color" class="bump-left">Text Color</label></th>
                        
                        <td>
                            #<input name="cff_body_color" type="text" value="<?php esc_attr_e( $cff_body_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr><td><b style="font-size: 14px;"><?php _e('Event Title'); ?></b></td></tr>
                    
                    <tr>
                        <th><label for="cff_event_title_format" class="bump-left">Format</label></th>
                        <td>
                            <select name="cff_event_title_format">
                                <option value="p" <?php if($cff_event_title_format == "p") echo 'selected="selected"' ?> >Paragraph</option>
                                <option value="h3" <?php if($cff_event_title_format == "h3") echo 'selected="selected"' ?> >Heading 3</option>
                                <option value="h4" <?php if($cff_event_title_format == "h4") echo 'selected="selected"' ?> >Heading 4</option>
                                <option value="h5" <?php if($cff_event_title_format == "h5") echo 'selected="selected"' ?> >Heading 5</option>
                                <option value="h6" <?php if($cff_event_title_format == "h6") echo 'selected="selected"' ?> >Heading 6</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="cff_event_title_size" class="bump-left">Text Size</label></th>
                        <td>
                            <select name="cff_event_title_size">
                                <option value="inherit" <?php if($cff_event_title_size == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="10" <?php if($cff_event_title_size == "10") echo 'selected="selected"' ?> >10px</option>
                                <option value="11" <?php if($cff_event_title_size == "11") echo 'selected="selected"' ?> >11px</option>
                                <option value="12" <?php if($cff_event_title_size == "12") echo 'selected="selected"' ?> >12px</option>
                                <option value="14" <?php if($cff_event_title_size == "14") echo 'selected="selected"' ?> >14px</option>
                                <option value="16" <?php if($cff_event_title_size == "16") echo 'selected="selected"' ?> >16px</option>
                                <option value="18" <?php if($cff_event_title_size == "18") echo 'selected="selected"' ?> >18px</option>
                                <option value="20" <?php if($cff_event_title_size == "20") echo 'selected="selected"' ?> >20px</option>
                                <option value="24" <?php if($cff_event_title_size == "24") echo 'selected="selected"' ?> >24px</option>
                                <option value="28" <?php if($cff_event_title_size == "28") echo 'selected="selected"' ?> >28px</option>
                                <option value="32" <?php if($cff_event_title_size == "32") echo 'selected="selected"' ?> >32px</option>
                                <option value="36" <?php if($cff_event_title_size == "36") echo 'selected="selected"' ?> >36px</option>
                                <option value="42" <?php if($cff_event_title_size == "42") echo 'selected="selected"' ?> >42px</option>
                                <option value="48" <?php if($cff_event_title_size == "48") echo 'selected="selected"' ?> >48px</option>
                                <option value="60" <?php if($cff_event_title_size == "54") echo 'selected="selected"' ?> >54px</option>
                                <option value="60" <?php if($cff_event_title_size == "60") echo 'selected="selected"' ?> >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_event_title_weight" class="bump-left">Text Weight</label></th>
                        <td>
                            <select name="cff_event_title_weight">
                                <option value="inherit" <?php if($cff_event_title_weight == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="normal" <?php if($cff_event_title_weight == "normal") echo 'selected="selected"' ?> >Normal</option>
                                <option value="bold" <?php if($cff_event_title_weight == "bold") echo 'selected="selected"' ?> >Bold</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_event_title_color" class="bump-left">Text Color</label></th>
                        <td>
                            <input name="cff_event_title_color" type="text" value="<?php esc_attr_e( $cff_event_title_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_title_link" class="bump-left">Link title to Facebook event page?</label></th>
                        <td><input type="checkbox" name="cff_event_title_link" id="cff_event_title_link" <?php if($cff_event_title_link == true) echo 'checked="checked"' ?> />&nbsp;Yes</td>
                    </tr>
                    <tr><td><b style="font-size: 14px;"><?php _e('Event Details'); ?></b></td></tr>
                    
                    <tr>
                        <th><label for="cff_event_details_size" class="bump-left">Text Size</label></th>
                        <td>
                            <select name="cff_event_details_size">
                                <option value="inherit" <?php if($cff_event_details_size == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="10" <?php if($cff_event_details_size == "10") echo 'selected="selected"' ?> >10px</option>
                                <option value="11" <?php if($cff_event_details_size == "11") echo 'selected="selected"' ?> >11px</option>
                                <option value="12" <?php if($cff_event_details_size == "12") echo 'selected="selected"' ?> >12px</option>
                                <option value="14" <?php if($cff_event_details_size == "14") echo 'selected="selected"' ?> >14px</option>
                                <option value="16" <?php if($cff_event_details_size == "16") echo 'selected="selected"' ?> >16px</option>
                                <option value="18" <?php if($cff_event_details_size == "18") echo 'selected="selected"' ?> >18px</option>
                                <option value="20" <?php if($cff_event_details_size == "20") echo 'selected="selected"' ?> >20px</option>
                                <option value="24" <?php if($cff_event_details_size == "24") echo 'selected="selected"' ?> >24px</option>
                                <option value="28" <?php if($cff_event_details_size == "28") echo 'selected="selected"' ?> >28px</option>
                                <option value="32" <?php if($cff_event_details_size == "32") echo 'selected="selected"' ?> >32px</option>
                                <option value="36" <?php if($cff_event_details_size == "36") echo 'selected="selected"' ?> >36px</option>
                                <option value="42" <?php if($cff_event_details_size == "42") echo 'selected="selected"' ?> >42px</option>
                                <option value="48" <?php if($cff_event_details_size == "48") echo 'selected="selected"' ?> >48px</option>
                                <option value="60" <?php if($cff_event_details_size == "54") echo 'selected="selected"' ?> >54px</option>
                                <option value="60" <?php if($cff_event_details_size == "60") echo 'selected="selected"' ?> >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_event_details_weight" class="bump-left">Text Weight</label></th>
                        <td>
                            <select name="cff_event_details_weight">
                                <option value="inherit" <?php if($cff_event_details_weight == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="normal" <?php if($cff_event_details_weight == "normal") echo 'selected="selected"' ?> >Normal</option>
                                <option value="bold" <?php if($cff_event_details_weight == "bold") echo 'selected="selected"' ?> >Bold</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_event_details_color" class="bump-left">Text Color</label></th>
                        <td>
                            #<input name="cff_event_details_color" type="text" value="<?php esc_attr_e( $cff_event_details_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_event_date_formatting" class="bump-left">Event date formatting</label></th>
                        <td>
                            <select name="cff_event_date_formatting">
                                <?php $original = strtotime('2013-07-25T17:30:00+0000'); ?>
                                <option value="1" <?php if($cff_event_date_formatting == "1") echo 'selected="selected"' ?> ><?php echo date('F j, Y, g:ia', $original); ?></option>
                                <option value="2" <?php if($cff_event_date_formatting == "2") echo 'selected="selected"' ?> ><?php echo date('F jS, g:ia', $original); ?></option>
                                <option value="3" <?php if($cff_event_date_formatting == "3") echo 'selected="selected"' ?> ><?php echo date('g:ia - F jS', $original); ?></option>
                                <option value="4" <?php if($cff_event_date_formatting == "4") echo 'selected="selected"' ?> ><?php echo date('g:ia, F jS', $original); ?></option>
                                <option value="5" <?php if($cff_event_date_formatting == "5") echo 'selected="selected"' ?> ><?php echo date('l F jS - g:ia', $original); ?></option>
                                <option value="6" <?php if($cff_event_date_formatting == "6") echo 'selected="selected"' ?> ><?php echo date('D M jS, Y, g:iA', $original); ?></option>
                                <option value="7" <?php if($cff_event_date_formatting == "7") echo 'selected="selected"' ?> ><?php echo date('l F jS, Y, g:iA', $original); ?></option>
                                <option value="8" <?php if($cff_event_date_formatting == "8") echo 'selected="selected"' ?> ><?php echo date('l F jS, Y - g:ia', $original); ?></option>
                                <option value="9" <?php if($cff_event_date_formatting == "9") echo 'selected="selected"' ?> ><?php echo date("l M jS, 'y", $original); ?></option>
                                <option value="10" <?php if($cff_event_date_formatting == "10") echo 'selected="selected"' ?> ><?php echo date('m.d.y - g:iA', $original); ?></option>
                                <option value="11" <?php if($cff_event_date_formatting == "11") echo 'selected="selected"' ?> ><?php echo date('m/d/y, g:ia', $original); ?></option>
                                <option value="12" <?php if($cff_event_date_formatting == "12") echo 'selected="selected"' ?> ><?php echo date('d.m.y - g:iA', $original); ?></option>
                                <option value="13" <?php if($cff_event_date_formatting == "13") echo 'selected="selected"' ?> ><?php echo date('d/m/y, g:ia', $original); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_event_date_custom" class="bump-left">Custom event date format</label></th>
                        <td>
                            <input name="cff_event_date_custom" type="text" value="<?php esc_attr_e( $cff_event_date_custom ); ?>" size="10" placeholder="Eg. F j, Y - g:ia" />
                            <i style="color: #666; font-size: 11px;">(<a href="http://smashballoon.com/custom-facebook-feed/docs/date/" target="_blank">Examples</a>)</i>
                        </td>
                    </tr>

                    <tr><td><b style="font-size: 14px;"><?php _e('Date'); ?></b></td></tr>
                    <tr>
                        <th><label for="cff_date_position" class="bump-left">Position</label></th>
                        <td>
                            <select name="cff_date_position">
                                <option value="below" <?php if($cff_date_position == "below") echo 'selected="selected"' ?> >Below Text</option>
                                <option value="above" <?php if($cff_date_position == "above") echo 'selected="selected"' ?> >Above Text</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_date_size" class="bump-left">Text Size</label></th>
                        <td>
                            <select name="cff_date_size">
                                <option value="inherit" <?php if($cff_date_size == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="10" <?php if($cff_date_size == "10") echo 'selected="selected"' ?> >10px</option>
                                <option value="11" <?php if($cff_date_size == "11") echo 'selected="selected"' ?> >11px</option>
                                <option value="12" <?php if($cff_date_size == "12") echo 'selected="selected"' ?> >12px</option>
                                <option value="14" <?php if($cff_date_size == "14") echo 'selected="selected"' ?> >14px</option>
                                <option value="16" <?php if($cff_date_size == "16") echo 'selected="selected"' ?> >16px</option>
                                <option value="18" <?php if($cff_date_size == "18") echo 'selected="selected"' ?> >18px</option>
                                <option value="20" <?php if($cff_date_size == "20") echo 'selected="selected"' ?> >20px</option>
                                <option value="24" <?php if($cff_date_size == "24") echo 'selected="selected"' ?> >24px</option>
                                <option value="28" <?php if($cff_date_size == "28") echo 'selected="selected"' ?> >28px</option>
                                <option value="32" <?php if($cff_date_size == "32") echo 'selected="selected"' ?> >32px</option>
                                <option value="36" <?php if($cff_date_size == "36") echo 'selected="selected"' ?> >36px</option>
                                <option value="42" <?php if($cff_date_size == "42") echo 'selected="selected"' ?> >42px</option>
                                <option value="48" <?php if($cff_date_size == "48") echo 'selected="selected"' ?> >48px</option>
                                <option value="60" <?php if($cff_date_size == "54") echo 'selected="selected"' ?> >54px</option>
                                <option value="60" <?php if($cff_date_size == "60") echo 'selected="selected"' ?> >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_date_weight" class="bump-left">Text Weight</label></th>
                        <td>
                            <select name="cff_date_weight">
                                <option value="inherit" <?php if($cff_date_weight == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="normal" <?php if($cff_date_weight == "normal") echo 'selected="selected"' ?> >Normal</option>
                                <option value="bold" <?php if($cff_date_weight == "bold") echo 'selected="selected"' ?> >Bold</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_date_color" class="bump-left">Text Color</label></th>
                        <td>
                            #<input name="cff_date_color" type="text" value="<?php esc_attr_e( $cff_date_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                            
                    <tr>
                        <th><label for="cff_date_formatting" class="bump-left">Date formatting</label></th>
                        <td>
                            <select name="cff_date_formatting">
                                <?php $original = strtotime('2013-07-25T17:30:00+0000'); ?>
                                <option value="1" <?php if($cff_date_formatting == "1") echo 'selected="selected"' ?> >Posted 2 days ago</option>
                                <option value="2" <?php if($cff_date_formatting == "2") echo 'selected="selected"' ?> ><?php echo date('F jS, g:i a', $original); ?></option>
                                <option value="3" <?php if($cff_date_formatting == "3") echo 'selected="selected"' ?> ><?php echo date('F jS', $original); ?></option>
                                <option value="4" <?php if($cff_date_formatting == "4") echo 'selected="selected"' ?> ><?php echo date('D F jS', $original); ?></option>
                                <option value="5" <?php if($cff_date_formatting == "5") echo 'selected="selected"' ?> ><?php echo date('l F jS', $original); ?></option>
                                <option value="6" <?php if($cff_date_formatting == "6") echo 'selected="selected"' ?> ><?php echo date('D M jS, Y', $original); ?></option>
                                <option value="7" <?php if($cff_date_formatting == "7") echo 'selected="selected"' ?> ><?php echo date('l F jS, Y', $original); ?></option>
                                <option value="8" <?php if($cff_date_formatting == "8") echo 'selected="selected"' ?> ><?php echo date('l F jS, Y - g:i a', $original); ?></option>
                                <option value="9" <?php if($cff_date_formatting == "9") echo 'selected="selected"' ?> ><?php echo date("l M jS, 'y", $original); ?></option>
                                <option value="10" <?php if($cff_date_formatting == "10") echo 'selected="selected"' ?> ><?php echo date('m.d.y', $original); ?></option>
                                <option value="11" <?php if($cff_date_formatting == "11") echo 'selected="selected"' ?> ><?php echo date('m/d/y', $original); ?></option>
                                <option value="12" <?php if($cff_date_formatting == "12") echo 'selected="selected"' ?> ><?php echo date('d.m.y', $original); ?></option>
                                <option value="13" <?php if($cff_date_formatting == "13") echo 'selected="selected"' ?> ><?php echo date('d/m/y', $original); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_date_custom" class="bump-left">Custom format</label></th>
                        <td>
                            <input name="cff_date_custom" type="text" value="<?php esc_attr_e( $cff_date_custom ); ?>" size="10" placeholder="Eg. F j, Y" />
                            <i style="color: #666; font-size: 11px;">(<a href="http://smashballoon.com/custom-facebook-feed/docs/date/" target="_blank">Examples</a>)</i>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_date_before" class="bump-left">Text before date</label></th>
                        <td><input name="cff_date_before" type="text" value="<?php esc_attr_e( $cff_date_before ); ?>" size="10" placeholder="Eg. Posted" /></td>
                    </tr>
                    <tr>
                        <th><label for="cff_date_after" class="bump-left">Text after date</label></th>
                        <td><input name="cff_date_after" type="text" value="<?php esc_attr_e( $cff_date_after ); ?>" size="10" placeholder="Eg. ago" /></td>
                    </tr>
                    <tr><td><b style="font-size: 14px;"><?php _e('Link to Facebook'); ?></b></td></tr>
                        
                    <tr>
                        <th><label for="cff_link_size" class="bump-left">Text Size</label></th>
                        <td>
                            <select name="cff_link_size">
                                <option value="inherit" <?php if($cff_link_size == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="10" <?php if($cff_link_size == "10") echo 'selected="selected"' ?> >10px</option>
                                <option value="11" <?php if($cff_link_size == "11") echo 'selected="selected"' ?> >11px</option>
                                <option value="12" <?php if($cff_link_size == "12") echo 'selected="selected"' ?> >12px</option>
                                <option value="14" <?php if($cff_link_size == "14") echo 'selected="selected"' ?> >14px</option>
                                <option value="16" <?php if($cff_link_size == "16") echo 'selected="selected"' ?> >16px</option>
                                <option value="18" <?php if($cff_link_size == "18") echo 'selected="selected"' ?> >18px</option>
                                <option value="20" <?php if($cff_link_size == "20") echo 'selected="selected"' ?> >20px</option>
                                <option value="24" <?php if($cff_link_size == "24") echo 'selected="selected"' ?> >24px</option>
                                <option value="28" <?php if($cff_link_size == "28") echo 'selected="selected"' ?> >28px</option>
                                <option value="32" <?php if($cff_link_size == "32") echo 'selected="selected"' ?> >32px</option>
                                <option value="36" <?php if($cff_link_size == "36") echo 'selected="selected"' ?> >36px</option>
                                <option value="42" <?php if($cff_link_size == "42") echo 'selected="selected"' ?> >42px</option>
                                <option value="48" <?php if($cff_link_size == "48") echo 'selected="selected"' ?> >48px</option>
                                <option value="60" <?php if($cff_link_size == "54") echo 'selected="selected"' ?> >54px</option>
                                <option value="60" <?php if($cff_link_size == "60") echo 'selected="selected"' ?> >60px</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_link_weight" class="bump-left">Text Weight</label></th>
                        <td>
                            <select name="cff_link_weight">
                                <option value="inherit" <?php if($cff_link_weight == "inherit") echo 'selected="selected"' ?> >Inherit</option>
                                <option value="normal" <?php if($cff_link_weight == "normal") echo 'selected="selected"' ?> >Normal</option>
                                <option value="bold" <?php if($cff_link_weight == "bold") echo 'selected="selected"' ?> >Bold</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_link_color" class="bump-left">Text Color</label></th>
                        <td>
                            <input name="cff_link_color" type="text" value="<?php esc_attr_e( $cff_link_color ); ?>" size="10" placeholder="Eg. ED9A00" />
                            <a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_facebook_link_text" class="bump-left">Custom 'View on Facebook' text</label></th>
                        <td>
                            <input name="cff_facebook_link_text" type="text" value="<?php esc_attr_e( $cff_facebook_link_text ); ?>" size="20" />
                            <i style="color: #666; font-size: 11px; margin-left: 5px;">Use different text in place of the default 'View on Facebook' link</i>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cff_view_link_text" class="bump-left">Custom 'View Link' text</label></th>
                        <td>
                            <input name="cff_view_link_text" type="text" value="<?php esc_attr_e( $cff_view_link_text ); ?>" size="20" />
                            <i style="color: #666; font-size: 11px; margin-left: 5px;">Use different text in place of the default 'View on Facebook' link</i>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
            <?php submit_button(); ?>
            <hr />
            <h3><?php _e('Likes, Shares and Comments'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e('Icon Style'); ?></th>
                        <td>
                            <select name="cff_icon_style">
                                <option value="light" <?php if($cff_icon_style == "light") echo 'selected="selected"' ?> >Light (for light backgrounds)</option>
                                <option value="dark" <?php if($cff_icon_style == "dark") echo 'selected="selected"' ?> >Dark (for dark backgrounds)</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Text Color'); ?></th>
                        <td>
                            <label for="cff_meta_text_color">#</label>
                            <input name="cff_meta_text_color" type="text" value="<?php esc_attr_e( $cff_meta_text_color ); ?>" size="10" />
                            <span>Eg. ED9A00</span>&nbsp;&nbsp;<a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Background Color'); ?></th>
                        <td>
                            <label for="cff_meta_bg_color">#</label>
                            <input name="cff_meta_bg_color" type="text" value="<?php esc_attr_e( $cff_meta_bg_color ); ?>" size="10" />
                            <span>Eg. ED9A00</span>&nbsp;&nbsp;<a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('If there are no comments'); ?></th>
                        <td>
                            <label for="cff_nocomments_text">Display this text:</label>
                            <input name="cff_nocomments_text" type="text" value="<?php esc_attr_e( $cff_nocomments_text ); ?>" size="30" />
                            &nbsp; <span>OR</span> &nbsp;
                            <input type="checkbox" name="cff_hide_comments" id="cff_hide_comments" <?php if($cff_hide_comments == true) echo 'checked="checked"' ?> />
                            <label for="cff_hide_comments">Hide the comments box</label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br />
            <hr />
            <h3><?php _e('Custom CSS'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <td>
                        Enter your own custom CSS in the box below
                        </td>
                    </tr>
                    <tr valign="top">
                        <td>
                            <textarea name="cff_custom_css" id="cff_custom_css" style="width: 70%;" rows="7"><?php esc_attr_e( $cff_custom_css ); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br />
            <hr />
            <h3><?php _e('Misc'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr><td><b style="font-size: 14px;"><?php _e('Text Character Limits'); ?></b></td></tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Maximum Post Text Length'); ?></label></th>
                        <td>
                            <input name="cff_title_length" type="text" value="<?php esc_attr_e( $cff_title_length_val ); ?>" size="4" /> <span>Characters.</span> <span>Eg. 200</span> <i style="color: #666; font-size: 11px; margin-left: 5px;">(Leave empty to set no maximum length)</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Maximum Description Length'); ?></label></th>
                        <td>
                            <input name="cff_body_length" type="text" value="<?php esc_attr_e( $cff_body_length_val ); ?>" size="4" /> <span>Characters.</span> <i style="color: #666; font-size: 11px; margin-left: 5px;">(Leave empty to set no maximum length)</i>
                        </td>
                    </tr>
                    <tr><td><b style="font-size: 14px;"><?php _e('Like Box'); ?></b></td></tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Show the Like Box'); ?></label></th>
                        <td>
                            <input type="checkbox" name="cff_show_like_box" id="cff_show_like_box" <?php if($cff_show_like_box == true) echo 'checked="checked"' ?> />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Like Box Position'); ?></label></th>
                        <td>
                            <select name="cff_like_box_position">
                                <option value="bottom" <?php if($cff_like_box_position == "bottom") echo 'selected="selected"' ?> >Bottom</option>
                                <option value="top" <?php if($cff_like_box_position == "top") echo 'selected="selected"' ?> >Top</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Like Box Background Color'); ?></label></th>
                        <td>
                            <label for="cff_likebox_bg_color">#</label>
                            <input name="cff_likebox_bg_color" type="text" value="<?php esc_attr_e( $cff_likebox_bg_color ); ?>" size="10" />
                            <span>Eg. ED9A00</span>&nbsp;&nbsp;<a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr><td><b style="font-size: 14px;"><?php _e('Video'); ?></b></td></tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Embedded Video Height Override'); ?></label></th>
                        <td>
                            <input name="cff_video_height" type="text" value="<?php esc_attr_e( $cff_video_height ); ?>" size="10" /> <span>Eg. 300px</span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Non-embedded Video'); ?></label></th>
                        <td>
                            <select name="cff_video_action">
                                <option value="file" <?php if($cff_video_action == "file") echo 'selected="selected"' ?> >Play video in new window</option>
                                <option value="post" <?php if($cff_video_action == "post") echo 'selected="selected"' ?> >Link to Facebook video post</option>
                            </select>
                        </td>
                    </tr>
                    <tr><td><b style="font-size: 14px;"><?php _e('Separating Line'); ?></b></td></tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Separating Line Color'); ?></label></th>
                        <td>
                            <label for="cff_sep_color">#</label>
                            <input name="cff_sep_color" type="text" value="<?php esc_attr_e( $cff_sep_color ); ?>" size="10" />
                            <span>Eg. ED9A00</span>&nbsp;&nbsp;<a href="http://www.colorpicker.com/" target="_blank">Color Picker</a>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label class="bump-left"><?php _e('Separating Line Thickness'); ?></label></th>
                        <td>
                            <input name="cff_sep_size" type="text" value="<?php esc_attr_e( $cff_sep_size ); ?>" size="1" /><span>px</span> <i style="color: #666; font-size: 11px; margin-left: 5px;">(Leave empty to hide)</i>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
        <style type="text/css">
            .bump-left{
                padding-left: 10px;
            }
        </style>
<?php 
} //End Style_Page 
?>