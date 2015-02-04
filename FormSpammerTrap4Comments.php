<?php

/**
 * Plugin Name: FormSpammerTrap for Comments
 * Plugin URI: http://FormSpammerTrap.com/wordpress-plugin-details.php
 * Description: Adds trapping for Spam and Form Bots on Comment forms without annoying Captchas, hidden fields, or other tricks that do not work. Looks for 'human activity' on the comment form; non-humans (bots) get sent to the <a href='http://www.FormSpammerTrap.com' target='_blank'>FormSpammerTrap.com</a> site, and their spam comment is discarded. Also allows you to limit the number of URLs in a message by removing excess URLs, and change the text that appears before or after the comment form fields. Settings are in the Settings, 'FormSpammerTrap for Comment Settings' menu.
 * Version: 1.0
 * Author: Rick Hellewell
 * Author URI: http://CellarWeb.com
 * Text Domain: 
 * Domain Path: 
 * Network: 
 * License: GPL2
 */
 
 // Plugin based on http://codex.wordpress.org/Creating_Options_Pages; modified from there

/*
    Copyright 2014-2015 by Rick Hellewell / CellarWeb.com and FormSpammerTrap.com
		email: rhellewell@gmail.com  

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// ----------------------------------------------------------------
// ----------------------------------------------------------------

//	build the class for all of this 
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'FormSpammerTrap for Comments Settings', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'fst4c_options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>FormSpammerTrap for Comments Settings</h2>
			<h4>Version  <?php echo $this->options[the_version]; ?> </h4>
			<?php fst4c_info_top(); ?>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'fst4c-setting-admin' );
                submit_button(); 
            ?>
            </form>
			<?php fst4c_info_bottom();		// display bottom info stuff
			?>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            'fst4c_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'fst4c-setting-admin' // Page
        );  
        add_settings_field(
            'the_version', 
            'FormSpammerTrap for Comments Version', 
            array( $this, 'the_version_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
        );      

        add_settings_field(
            'text_before', 
            'Text before comment area (replaces default text; leave blank to use default text)', 
            array( $this, 'text_before_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
        );      

        add_settings_field(
            'text_after', 
            'Text after comment area (replaces default text; leave blank to use default text)', 
            array( $this, 'text_after_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
        );      

        add_settings_field(
            'name_email_req', 
            'Require Name and Email?', 
            array( $this, 'name_email_req_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
        );      

        add_settings_field(
            'urls_allowed', 
            'Number of URLs allowed in comment area (most form spam contains multiple URLs; we recommend 1, max 9)', 
            array( $this, 'urls_allowed_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'input', 'fieldsize' => '1', 'fieldmax' => '1')
        );      

        add_settings_field(
            'url_redacted', 
            'Replace excess URLs with [URL Redacted]? (otherwise excess URLs are just deleted)', 
            array( $this, 'url_redacted_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
        );      

        add_settings_field(
            'show_fst_message', 
            'Show the redirect message about the FormSpammerTrap site under the form?', 
            array( $this, 'show_fst_message_callback' ), // Callback, determines function that builds the input tag
            'fst4c-setting-admin', 
            'setting_section_id', // Section           
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['the_version'] ) )
            $new_input['the_version'] =  $input['the_version'];

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        if( isset( $input['text_before'] ) )
            $new_input['text_before'] = sanitize_text_field( $input['text_before'] );


        if( isset( $input['text_after'] ) )
            $new_input['text_after'] = sanitize_text_field( $input['text_after'] );

        if( isset( $input['urls_allowed'] ) )
            $new_input['urls_allowed'] = absint( $input['urls_allowed'] );

        if( isset( $input['name_email_req'] ) ) 
			$new_input['name_email_req'] = "1";

        if( isset( $input['url_redacted'] ) ) 
			$new_input['url_redacted'] = "1";

        if( isset( $input['show_fst_message'] ) ) 
			$new_input['show_fst_message'] = "1";

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print '<h3><strong>Settings for FormSpammerTrap for Comments</strong></h3>';
		print '<p><em>No HTML or code allowed in settings fields; it will be stripped out, leaving only plain text.</em></p>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function the_version_callback()
    {
       printf(
            '<input type="text" type="hidden" id="the_version" name="fst4c_options[the_version]" value="1.00" readonly="readonly" />',
            isset( $this->options['the_version'] ) ? esc_attr( $this->options['the_version']) : esc_attr( $this->options['the_version'])
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function text_before_callback()
    {
        printf(
            '<textarea id="text_before" name="fst4c_options[text_before]" cols="60" rows="3" >%s </textarea>',
            isset( $this->options['text_before'] ) ? esc_attr( $this->options['text_before']) : ''
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function text_after_callback()
    {
        printf(
            '<textarea id="text_after" name="fst4c_options[text_after]"  cols="60" rows="3">%s </textarea>',
            isset( $this->options['text_after'] ) ? esc_attr( $this->options['text_after']) : ''
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function name_email_req_callback()	// require name and email checkbox
    {
	 printf(
            "<input type='checkbox' id='name_email_req' name='fst4c_options[name_email_req]'   value='1' " . checked( '1', $this->options[name_email_req] , false ) . " /> ",
            isset($this->options['name_email_req'] ) ?  '1' : '0'
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function urls_allowed_callback()
    {
        printf(
            '<input type="text" id="urls_allowed" name="fst4c_options[urls_allowed]" value="%s" size="1" maxlength="1" />',
            isset( $this->options['urls_allowed'] ) ? esc_attr( $this->options['urls_allowed']) : ''
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function url_redacted_callback()	// require name and email checkbox
    {
	 printf(
            "<input type='checkbox' id='url_redacted' name='fst4c_options[url_redacted]'   value='1' " . checked( '1', $this->options[url_redacted] , false ) . " /> ",
            isset($this->options['url_redacted'] ) ?  '1' : '0'
        );
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function show_fst_message_callback()	// require name and email checkbox
    {
	 printf(
            "<input type='checkbox' id='show_fst_message' name='fst4c_options[show_fst_message]'   value='1' " . checked( '1', $this->options[show_fst_message] , false ) . " /> ",
            isset($this->options['show_fst_message'] ) ?  '1' : '0'
        );
    }

}

if( is_admin() )
// end of the class stuff

    $my_settings_page = new MySettingsPage();

// ---------------------------------------------------------------------------- 
//	display the top info part of the page	
// ---------------------------------------------------------------------------- 
function fst4c_info_top() {
	?>
	<div class="wrap">
	<p>FormSpammerTrap 4 Comments adds form spam bot blocking to your comment form.</p><p>It senses human interaction with the comment form. It does not require those irritating captchas, hidden fields, silly questions, or aother annoying things others use to try to (but fail to) block spam-bots.</p>
<p>If a spam-bot tries to submit a comment, they will be sent to our <a href="http://formspammertrap.com" title="FormSpammerTrap.com" alt="FormSpammerTrap.com">FormSpammerTrap</a> page, and you will not see the spam-bot comment on your system.</p>
<p>You will find more information at our <a href="http://formspammertrap.com" title="FormSpammerTrap.com" alt="FormSpammerTrap.com">FormSpammerTrap</a> web site. We also have solutions for WordPress comment forms and custom-built sites. You can contact us with any questions or issues on that site.</p>
<hr>
<!--<p><strong>These options are available:</strong></p>-->
</div>
<?php 

}
// ---------------------------------------------------------------------------- 
// display the bottom info part of the page
// ---------------------------------------------------------------------------- 
function fst4c_info_bottom() {
	// print copyright with current year, never needs updating
	$xstartyear = "2014";
	$xname = "Rick Hellewell";
	$xcompanylink1 = ' <a href="http://cellarweb.com" title="CellarWeb" >CellarWeb/com</a>';
	// leave this empty if no company 2
	$xcompanylink2 = ' <a href="http://formspammertrap.com" title="FormSpammerTrap" >FormSpammerTrap.com</a>';
	// output
	echo 'Copyright &copy; ' . $xstartyear . '  - ' . date("Y") . ' by ' . $xname . ' and ' . $xcompanylink1 ;
	if ($xcompanylink2) {
		echo ' and ';
		echo $xcompanylink2;
		}
	echo ' , All Rights Reserved. Released under GPL2 license.';
	echo '<br />Additional code from comment-form-inline-errors WP Plugin via GPL2 License<br />All Rights Reserved. Released under GPL2 license.';
	return; 
}
// end print copyright ---------------------------------------------------------

// ---------------------------------------------------------------------------- 
// ``end of admin area
// ---------------------------------------------------------------------------- 

// ---------------------------------------------------------------------------- 
// start of operational area that changes the comments box stuff 		
// ---------------------------------------------------------------------------- 
// check to make sure jquery is loaded
function check_for_jquery() {
	if(!wp_script_is( 'jquery', $list = 'done' )) { return; }			// exit if jquery is not loaded (rare)
	}
// adds the action at end of head, to make sure jquery has had time to load
add_action("wp_head","check_for_jquery",99); 
// end jquery check


// ---------------------------------------------------------------------------- 
// uses jquery to change the comment form's 'action' value after document ready
function change_comment_action () {
?>
<script type="text/javascript">
<!-- 
	jQuery(document).ready(function() {
	jQuery("#commentform").attr('action', 'http://formspammertrap.com');
	});
	 -->
</script>
<?php
return; }

add_action( 'wp_head', 'change_comment_action' ,99);

// ---------------------------------------------------------------------------- 
// use jquery to force the insert of the formspammertrap_cl() function inside the required fields
// 		attempts to fix themes (like from Cryout Creations) that use non-standard way of building
//			comment forms by ignoring the default_fields stuff
function force_insert_fst_code() {
?>
<script>
jQuery(document).ready(function(){
   /* add onclick and onfocus to each required field
   		tag-type#tag-id*/
   jQuery('textarea#comment').attr('onclick','formspammertrap_CL();');
   jQuery('textarea#comment').attr('onfocus','formspammertrap_CL();');
   
   jQuery('input#author').attr('onclick','formspammertrap_CL();');
   jQuery('input#author').attr('onfocus','formspammertrap_CL();');

   jQuery('input#email').attr('onclick','formspammertrap_CL();');
   jQuery('input#email').attr('onfocus','formspammertrap_CL();');

   jQuery('textarea#comment').attr('placeholder','Comment (*required)');


});
</script>
<?php
return; }

add_action( 'wp_head', 'force_insert_fst_code' ,99);

// ---------------------------------------------------------------------------- 
//	- this section adds formspammertrap_CL() function code
//	random variable string: $token = bin2hex(openssl_random_pseudo_bytes(16));
function formspammertrap_cl_script() {
	?>
<script type="text/javascript">
	<!--
	var Clicked =0;
	-->
	<!-- 
	var formspammertrap_code_1099287= "<?php echo site_url( '/wp-comments-post.php' ); ?>";        // "comment_"
	var formspammertrap_code_8893894= "";        // "action.php"
	var FormID="commentform";	  // the name of the form, must match the form 'name' parameter
	
	function formspammertrap_CL() {
	Clicked++;
	var elem = document.getElementById("commentform");
	elem.action=formspammertrap_code_1099287+formspammertrap_code_8893894;
	}
	-->
	</script> 
<?php
	return; }

add_action('wp_head', 'formspammertrap_cl_script');

// ---------------------------------------------------------------------------- 
// this is the new comment fields with the CL() added to onclick/onfocus for required fields only
//		excludes the comment text area 
//		replaces any customization of the comment form fields
function formspammertrap_comment_form_fields( $fields ) {
	$xoptions = get_option( 'fst4c_options' );
$req = $xoptions['name_email_req'] ;
$aria_req = ( $req ? " aria-required='true' required='true' " :" aria-required= 'false' required='false' " );
$aria_req_in_placeholder = ( $req ? '(* required) ':'' );
unset($GLOBALS[$fields]);		// clear out any existing comment form fields
$fields = array();
$fields =  array(
// get the required fields
  'author' =>
    '<p class="comment-form-author"><label for="author">' . __( 'Your Name', 'domainreference' ) .
    ( $req ? '<span class="required">&nbsp;*&nbsp;(required)</span>' . '</label> ' : '' ) .
    '<input placeholder="Your Name ' . $aria_req_in_placeholder . ' " id="author" name="author" type="text" onclick="formspammertrap_CL();" onfocus="formspammertrap_CL();" value="' . esc_attr( $commenter['comment_author'] ) .
    '" size="30"' . $aria_req . ' /></p>',

  'email' =>
    '<p class="comment-form-email"><label for="email">' . __( 'Your Email', 'domainreference' )  .
    ( $req ? '<span class="required">&nbsp;*&nbsp;(required)</span>' . '</label> ' : '' ) .
    '<input placeholder="Your Email ' . $aria_req_in_placeholder . ' " id="email" name="email" type="text"  onclick="formspammertrap_CL()" onfocus="formspammertrap_CL()" value="' . esc_attr(  $commenter['comment_author_email'] ) .
    '" size="30"' . $aria_req . ' /></p>',

  'url' =>
    '<p class="comment-form-url"><label for="url">' . __( 'Your Website', 'domainreference' ) . '</label>' .
    '<input placeholder="Your Web Site" id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
    '" size="30" /></p>'
	
	// you would think that you would put the comment textarea here, but nope. 
	// it needs to use the comment_form_defaults hook, not comment_form_default_fields hook
	// 		(code left here for reference)
	//	,
	//	
	//	'comment_field' =>  '<p class="comment-form-comment"><label for="comment">' . _x( 'Your Comment', 'noun' ) .
	//    '<span class="required">*&nbsp;(required)</span></label><textarea placeholder="Your Comments" id="comment" name="comment" cols="45" rows="8" aria-required="true" required="required"  >' .
	//    '</textarea></p>'
	
	);
	return $fields; 
	}
add_filter( 'comment_form_default_fields', 'formspammertrap_comment_form_fields',9,1 );

// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 
//// this blocks spam bots from the comment field 
// note: this must be installed by the comment_form_defaults hook, not the comment_form_default_fields hook,
//		otherwise, you get duplicate comment textareas!
function wpsites_modify_comment_form_text_area($arg) {

	$arg['comment_field'] = 
	 '<p class="comment-form-comment"><label for="comment"  class="rick">Your Comment<span class="required">&nbsp;*&nbsp;(required)</span></label><textarea placeholder="Your Comment (* required)" id="comment" name="comment" cols="45" rows="12" onclick="formspammertrap_CL();" onfocus="formspammertrap_CL();" aria-required="true" required="required" ></textarea></p>'
;
	return $arg; 
	}
add_filter('comment_form_defaults', 'wpsites_modify_comment_form_text_area',9,1); 

// ---------------------------------------------------------------------------- 
// set the message after the comment form
function fst4c_text_around_comment_form($arg) {
	$xoptions = get_option( 'fst4c_options' );
	$xtext_before_comment = $xoptions['text_before'];
	if ($xoptions[urls_allowed]) {
	$xtext_before_comment .= ' <em>You are allowed to enter ' . $xoptions[urls_allowed] . ' URL(s) in the comment area.</em>'; } else {$xtext_before_comment .= ' <em>You are not allowed to enter any URLs in the comment area.</em>';
	}
	$xtext_after_comment = $xoptions['text_after'];
	if ($xtext_before_comment) {
		$arg['comment_notes_before'] = '<p class="form-allowed-tags" align="center">' . sprintf( __(  $xtext_before_comment )) . '</p>';
	}
	if ($xtext_after_comment) {
		if ($xoptions['show_fst_message']) {
		$xtext_after_comment .= '<p align="center" class="form-allowed-tags"><em>Form filling spam bots are redirected to the <a href="http://formspammertrap.com" title="FormSpammerTrap.com" target="_blank">FormSpammerTrap.com</a> web site.</em></p>';}
		$arg['comment_notes_after'] = '<p class="form-allowed-tags" align="center">' . sprintf( __( $xtext_after_comment )) . '</p></div>';
	}
	return $arg;
}
add_filter('comment_form_defaults', 'fst4c_text_around_comment_form',9,1);

// ---------------------------------------------------------------------------- 
// check for too many urls in the comment content
function preprocess_comment_remove_url( $commentdata ) {

	// get the urls_allowed value
	$xoptions = get_option('fst4c_options');
	$xurls_allowed = $xoptions['urls_allowed'];
	$xurl_redacted = $xoptions['url_redacted'];
	$text = $commentdata['comment_content'];
	
	// regex to find all types of urls in the text
	$regex = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i" ;
	// put found urls in the $urls_array array
	preg_match_all($regex, $text, $urls_array) ;
	// extract the elements of the first element to get a one-dimension array
	$urls_array = $urls_array[0];
	// remove the first 'urls_allowed' count elements from the $results array
	$urls_array = array_slice($urls_array, $xurls_allowed) ;
	// for each $urls_array, find the position in the text, then delete the url from that spot only
	if ($xurl_redacted) {$xredacted_text = '[URL Redacted]';} else {$xredacted_text = '';}
	foreach ($urls_array as $url_item) {
		if ($xposition = strpos($text, $url_item)) {
			$text = str_replace($url_item,$xredacted_text,$text);
		}
	}
	$commentdata['comment_content'] = $text;
	return $commentdata;
}
add_filter( 'preprocess_comment' , 'preprocess_comment_remove_url' ); 
// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 
// grabs the code from the plugin: https://wordpress.org/plugins/comment-form-inline-errors/
// 		which handles form errors (missing required fields, etc
//		that plugin is GPL2, so is lawfully included


if (!defined('ABSPATH')) { exit; }

if (!class_exists('wpCommentFormInlineErrors')){
    class wpCommentFormInlineErrors
    {
        /* minimum required wp version */
        public $wpVer = "3.0";
        /* minimum required php version */
        public $phpVer = "5.3";

        public function __construct() { add_action('init', array($this, 'init')); }

        /**
         * Hook me up, buttercup
         */

        public function init()
        {
            if(!$this->checkRequirements()){ return; }
            session_start();
            /* all these hooks are in wp since version 3.0, that's where we aim. */
            add_filter('wp_die_handler', array($this, 'getWpDieHandler'));
            add_action('comment_form_before_fields', array($this, 'displayFormError'));
            add_action('comment_form_logged_in_after', array($this, 'displayFormError'));
            add_filter('comment_form_default_fields',array($this, 'formDefaults'));
            add_filter('comment_form_field_comment',array($this, 'formCommentDefault'));
        }


        /**
         * Let's check Wordpress version, and PHP version and tell those
         * guys whats needed to upgrade, if anything.
         *
         * @return bool
         */

        private function checkRequirements()
        {
            global $wp_version;
            if (!version_compare($wp_version, $this->wpVer, '>=')){
                $this->pluginDeactivate();
                add_action('admin_notices', array($this, 'displayVersionNotice'));
                return FALSE;
            } elseif (!version_compare(PHP_VERSION, $this->phpVer, '>=')){
                $this->pluginDeactivate();
                add_action('admin_notices', array($this, 'displayPHPNotice'));
                return FALSE;
            }
            return TRUE;
        }


        /**
         * Deactivates our plugin if anything goes wrong. Also, removes the
         * "Plugin activated" message, if we don't pass requriments check.
         */

        private function pluginDeactivate()
        {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            deactivate_plugins(plugin_basename(__FILE__));
            unset($_GET['activate']);
        }


        /**
         * Displays outdated wordpress messsage.
         */

        public function displayVersionNotice()
        {
            global $wp_version;
            $this->displayAdminError(
                '<p>Your version of WordPress is not current, so you shouldn\'t use this plugin. You should really upgrade to the latest WordPress. Do it now!</p><p>Until you upgrade, this plugin won\'t work, leaving your site open to all sorts of hackers. (This plugin requires at least WordPress varsion ' . $this->wpVer . ' or higher.)</p> <p>You are currently using ' . $wp_version . '. Please upgrade your WordPress version now!</p><p>The FormSpammerTrap for Comments plugin has been disabled until you update your WordPress version.</p>');
        }


        /**
         * Displays outdated php message.
         */

        public function displayPHPNotice()
        {
            $this->displayAdminError(
                '<p>You need PHP version at least '. $this->phpVer .' to run this plugin. You should really update your PHP version to protect your site against hackers.</p><p>You are currently using PHP version ' . PHP_VERSION . '.</p><p>The FormSpammerTrap for Comments plugin has been disabled until you update your PHP version.</p>');
        }


        /**
         * Admin error helper
         *
         * @param $error
         */

        private function displayAdminError($error) { echo '<div id="message" class="error"><p><strong>' . $error . '</strong></p></div>';  }


        /************************ Let's do this. ************************/

        /**
         * Overwrites wordpress error handeling.
         *
         * @param $handler
         * @return array
         */

        function getWpDieHandler($handler){ return array($this, 'handleWpError'); }


        /**
         * Now this sounds great does it not? :) After refresh, we can
         * display that message. Easy peasy my man. Of course, only if
         * it's not admin error.
         *
         * @param $message
         * @param string $title
         * @param array $args
         */

        function handleWpError($message, $title='', $args=array())
        {
            // this is simple, if it's not admin error, and we simply continue
            // and sort it our way. Meaning, send errors to form itself and display them thru $_SESSION.
            // and yes, we test if comment id is present, not sure how else to test if commenting featured is being used :)
            if(!is_admin() && !empty($_POST['comment_post_ID']) && is_numeric($_POST['comment_post_ID'])){
                $_SESSION['formError'] = $message;
                // let's save those form fields in session as well hey? bit annoying
                // filling everything again and again. might work
                $denied = array('submit', 'comment_post_ID', 'comment_parent');
                foreach($_POST as $key => $value){
                    if(!in_array($key, $denied)){
                        $_SESSION['formFields'][$key] = stripslashes($value);
                    }
                }
                // write, redirect, go
                session_write_close();
                wp_safe_redirect(get_permalink($_POST['comment_post_ID']) . '#formError', 302);
                exit;
            } else {
                _default_wp_die_handler($message, $title, $args);   // this is for the other errors
            }
        }


        /**
         * Display inline form error.
         */

        public function displayFormError()
        {
            $formError = $_SESSION['formError'];
            unset($_SESSION['formError']);
            if(!empty($formError)){
                echo '<div id="formError" class="formError" style="color:red;">';
                echo '<p>'. $formError .'</p>';
                echo '</div><div class="clear clearfix"></div>';
            }
        }


        /**
         * Reset form defaults to value sent, it's nice when form remebers
         * stuff and doesn't force you to fill in shit again and again.
         *
         * @param $fields
         * @return mixed
         */

        function formDefaults($fields)
        {
            $formFields = $_SESSION['formFields'];
            foreach($fields as $key => $field){
                if($this->stringContains('input', $field)){
                    if($this->stringContains('type="text"', $field)){
                        $fields[$key] = str_replace('value=""', 'value="'. stripslashes($formFields[$key]) .'"', $field);
                    }
                } elseif ($this->stringContains('</textarea>', $field)){
                    $fields[$key] = str_replace('</textarea>', stripslashes($formFields[$key]) .'</textarea>', $field);
                }
            }
            return $fields;
        }


        /**
         * Of course comment field is special :) needs special
         * hook for defaults.
         *
         * @param $comment_field
         * @return mixed
         */

        function formCommentDefault($comment_field)
        {
            $formFields = $_SESSION['formFields'];
            unset($_SESSION['formFields']);
            return str_replace('</textarea>', $formFields['comment'] . '</textarea>', $comment_field);
        }


        /**
         * Just little helper for filling the form again.
         *
         * @param $haystack
         * @param $needle
         * @return bool
         */

        public function stringContains($needle, $haystack){ return strpos($haystack, $needle) !== FALSE; }

    }

}

new wpCommentFormInlineErrors();

// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 

