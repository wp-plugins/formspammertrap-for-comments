<?php

/**
	* Plugin Name: FormSpammerTrap for Comments
	* Plugin URI: http://FormSpammerTrap.com/wordpress-plugin-details.php
	* Description: Stops Spam and Form bots from using your Comment forms without annoying Captchas, hidden fields, or other tricks that do not work. Looks for 'human activity' on the comment form; non-humans (bots) get sent to the <a href='http://www.FormSpammerTrap.com' target='_blank'>FormSpammerTrap.com</a> site, and their spam comment is discarded. Also allows you to limit the number of URLs in a message by removing excess URLs, and change text that appears before/after the comment form fields. Settings are in the Settings, 'FormSpammerTrap for Comment Settings' menu.
	* Version: 1.08
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

	// Add settings link on plugin page
	 function fst4c_settings_link($links) { 
	  $settings_link = '<a href="options-general.php?page=FormSpammerTrap4Comments-settings" title="FormSpammerTrap4Comments Settings Page">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}
	$plugin = plugin_basename(__FILE__); 
	
	add_filter("plugin_action_links_$plugin", 'fst4c_settings_link' );

//	build the class for all of this 
class MySettingsPage
{

   	// Holds the values to be used in the fields callbacks
	private $options;

	// start your engines!
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	// add options page
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin', 
			'FormSpammerTrap for Comments Settings', 
			'manage_options', 
			'FormSpammerTrap4Comments-settings', 
			array( $this, 'create_admin_page' )
		);
	}

   // options page callback
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option( 'fst4c_options' );
		?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2>FormSpammerTrap for Comments Settings</h2>
	<h4>Version <?php echo $this->options[the_version]; ?> </h4>
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

	// Register and add the settings
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
			'FormSpammerTrap for Comments version', 
			array( $this, 'the_version_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
		);	  

		add_settings_field(
			'text_before', 
			'Text before comment area for not-logged-in users (replaces default text; leave blank to use default text)', 
			array( $this, 'text_before_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
		);	  

		add_settings_field(
			'text_after', 
			'Text after comment area (replaces default text; leave blank to use default text)', 
			array( $this, 'text_after_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
		);	  

		add_settings_field(
			'name_email_req', 
			'Require Name and Email?', 
			array( $this, 'name_email_req_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'show_allowed_html', 
			'Show allowed HTML code message after comment area?', 
			array( $this, 'show_allowed_html_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
		);	  

		add_settings_field(
			'remove_html', 
			'Remove all HTML tags from all fields?', 
			array( $this, 'remove_html_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'input', 'fieldsize' => '30', 'fieldmax' => '50')
		);	  

		add_settings_field(
			'urls_allowed', 
			'Number of URLs allowed in comment area (most form spam contains multiple URLs; we recommend 1, max 9)', 
			array( $this, 'urls_allowed_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'input', 'fieldsize' => '1', 'fieldmax' => '1')
		);	  

		add_settings_field(
			'url_redacted', 
			'Replace excess URLs with [URL Redacted]? (otherwise excess URLs are just deleted)', 
			array( $this, 'url_redacted_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'show_fst_message', 
			'Show the redirect message about the FormSpammerTrap site under the form?', 
			array( $this, 'show_fst_message_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'wrap_required_text', 
			'Wrap the \'required\' text under the field label?', 
			array( $this, 'wrap_required_text_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'reply_to_name', 
			'Add the commenter\'s name to the Reply link text', 
			array( $this, 'reply_to_name_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'title_reply', 
			'Change the "Leave a Reply" text', 
			array( $this, 'title_reply_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'reply_to_text', 
			'Change the "Reply to" text', 
			array( $this, 'reply_to_text_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'cancel_reply_link', 
			'Change the "Cancel Reply" text', 
			array( $this, 'cancel_reply_link_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  

		add_settings_field(
			'submit_label', 
			'Change the text inside the Submit button', 
			array( $this, 'submit_label_callback' ), 
			'fst4c-setting-admin', 
			'setting_section_id', // Section		   
			array('fieldtype' => 'checkbox', 'fieldsize' => null, 'fieldmax' => null )
		);	  


	}

	// sanitize the settings fields on submit
	// 	@param array $input Contains all settings fields as array keys
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

		if( isset( $input['show_allowed_html'] ) ) 
			$new_input['show_allowed_html'] = "1";

		if( isset( $input['remove_html'] ) ) 
			$new_input['remove_html'] = "1";

		if( isset( $input['show_fst_message'] ) ) 
			$new_input['show_fst_message'] = "1";

		if( isset( $input['wrap_required_text'] ) ) 
			$new_input['wrap_required_text'] = "1";

		if( isset( $input['reply_to_name'] ) ) 
			$new_input['reply_to_name'] = "1";

		if( isset( $input['submit_label'] ) ) 
			$new_input['submit_label'] = sanitize_text_field( $input['submit_label'] );

		if( isset( $input['title_reply'] ) ) 
			$new_input['title_reply'] = sanitize_text_field( $input['title_reply'] );

		if( isset( $input['reply_to_text'] ) ) 
			$new_input['reply_to_text'] = sanitize_text_field( $input['reply_to_text'] );

		if( isset( $input['cancel_reply_link'] ) ) 
			$new_input['cancel_reply_link'] = sanitize_text_field( $input['cancel_reply_link'] );

		return $new_input;
	}

	// print the Section text
	public function print_section_info()
	{
		print '<h3><strong>Settings for FormSpammerTrap for Comments</strong></h3>';
		print '<p><em>No HTML or code allowed in the text boxes below; it will be stripped out, leaving only plain text.</em> Note that some settings depend on other things, and some themes may not support all settings. See the notes to the right of each setting.</p>';
		print '<p>Save your settings once after upgrading to the latest version.</p>';
	}

	// version number callback
	public function the_version_callback()
	{
	   printf(
			'<input type="text" type="hidden" id="the_version" name="fst4c_options[the_version]" value="1.08 (25-Aug-2015)" readonly="readonly" width="5" maxlength="5" />',
			isset( $this->options['the_version'] ) ? esc_attr( $this->options['the_version']) : esc_attr( $this->options['the_version'])
		);
	}

	// text before callback
	public function text_before_callback()
	{
		printf(
			'<table><tr><td><textarea id="text_before" name="fst4c_options[text_before]" cols="60" rows="3" >%s </textarea></td><td valign="top">Enter the text you want to appear above the comment text area. Leave this blank to use the default text. (No HTML codes allowed.) <em>Note that this text will not appear for logged-in users because of how the WP core code displays this value.</em></td></tr></table>',
			isset( $this->options['text_before'] ) ? esc_attr( $this->options['text_before']) : ''
		);
	}

	// text after callback 
	public function text_after_callback()
	{
		printf(
			'<table><tr><td><textarea id="text_after" name="fst4c_options[text_after]"  cols="60" rows="3">%s </textarea></td><td valign="top">Enter the text you want to appear below the comment text area; just above the submit button. Leave this blank to use the default text. (No HTML codes allowed.)<br> Note that this will replace the default text that shows what HTML code is allowed.</td></tr></table>',
			isset( $this->options['text_after'] ) ? esc_attr( $this->options['text_after']) : ''
		);
	}
	// show allowed html callback
	public function show_allowed_html_callback()	// require name and email checkbox
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='show_allowed_html' name='fst4c_options[show_allowed_html]'   value='1' " . checked( '1', $this->options[show_allowed_html] , false ) . " /></td><td valign='top'>Show a message about the  HTML codes that are allowed in the comment text area, placing that text after the comment area. Your current 'allowed html code' settings will show the following text in addition to any other text you specify after the comment textarea: <br><br>&nbsp;&nbsp;&nbsp;&nbsp;<em>The following HTML codes are allowed in the comment area: " . allowed_tags() . "</em><br><br><i>Do not enable this option if you have enabled the 'Remove all HTML tags' option below.</i></td></tr></table> ",
			isset($this->options['show_allowed_html'] ) ?  '1' : '0'
		);
	}
	// remove all html callback
	public function remove_html_callback()	// remove all HTML codes
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='remove_html' name='fst4c_options[remove_html]'   value='1' " . checked( '1', $this->options[remove_html] , false ) . " /></td><td valign='top'>This will remove all HTML code from comments, even <strong>bold</strong> and <i>italic</i>, leaving just text and URLs. </i><br>Note that the plugin will always remove 'onmouseover' tags which may contain dangerous code; this option may give you additional protection.<br><i>Do not enable this option if you enable the 'Show allowed HTML code message' option.</i></td></tr></table> ",
			isset($this->options['remove_html'] ) ?  '1' : '0'
		);
	}
 

	// email required callback
	public function name_email_req_callback()	// require name and email checkbox
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='name_email_req' name='fst4c_options[name_email_req]'   value='1' " . checked( '1', $this->options[name_email_req] , false ) . " /></td><td valign='top'>Check this box to require the name and email field entry. This overrides the Settings, Discussion setting. Uncheck to use the Settings, Discussion setting.</td></tr></table> ",
			isset($this->options['name_email_req'] ) ?  '1' : '0'
		);
	}
 
	// urls allowed callback
	public function urls_allowed_callback()
	{
		printf(
			'<table><tr><td><input type="text" id="urls_allowed" name="fst4c_options[urls_allowed]" value="%s" size="1" maxlength="1" /></td><td valign="top">Enter the number of URLs allowed in the comment area (0-9). Most spammers will try to include lots of URLs in comment. We recommend no more than 1 URL in a comment. Any excess URLs will be redacted or deleted (see next option).</td></tr></table>',
			isset( $this->options['urls_allowed'] ) ? esc_attr( $this->options['urls_allowed']) : ''
		);
	}

	// url redacted callback
	public function url_redacted_callback()	// require name and email checkbox
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='url_redacted' name='fst4c_options[url_redacted]'   value='1' " . checked( '1', $this->options[url_redacted] , false ) . " /></td><td valign='top'>Check this box to replace excess URLs with [URL Redacted]. Unchecked will just remove excess URLSs.</td></tr></table> ",
			isset($this->options['url_redacted'] ) ?  '1' : '0'
		);
	}
	
	// show formspammertrap.com message callback
	public function show_fst_message_callback()	// require name and email checkbox
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='show_fst_message' name='fst4c_options[show_fst_message]'   value='1' " . checked( '1', $this->options[show_fst_message] , false ) . " /></td><td valign='top'>Check this box to let the visitor know that spammers will go to the FormSpammerTrap.com web site (includes a link to the FormSpammerTrap.com web site). Unchecked will not display the message. Useful to alert visitors that any form spam will be sent away from your site, but will work fine without enabling this.</td></tr></table> ",
			isset($this->options['show_fst_message'] ) ?  '1' : '0'
		);
	}
	
	// wrap required text callback/
	public function wrap_required_text_callback()	// wrap the required text in the label area (needed for some themes)
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='wrap_required_text' name='fst4c_options[wrap_required_text]'   value='1' " . checked( '1', $this->options[wrap_required_text] , false ) . " /></td><td valign='top'>Check this box to force the 'required' text on a separate line in the label area to the left of input fields. This is needed on some themes that don't have enough room for that text in the label area. Leave unchecked leave the 'required' text just after the field label. Note that not all themes put a label next to the input fields.</td></tr></table> ",
			isset($this->options['wrap_required_text'] ) ?  '1' : '0'
		);
	}
	
	
	// reply to name callback
	public function reply_to_name_callback()	// wrap the required text in the label area (needed for some themes)
	{
	 printf(
			"<table><tr><td><input type='checkbox' id='reply_to_name' name='fst4c_options[reply_to_name]'   value='1' " . checked( '1', $this->options[reply_to_name] , false ) . " /></td><td valign='top'>Enable to add the name of the commenter to the Reply link text on comments. So a comment from 'Rick H' will have a reply link text of 'Reply to Rick H' (it uses the full name from the author field or the logged in user). Leave blank to use the standard 'Reply' link text. Note that some themes many not support this option.</td></tr></table> ",
			isset($this->options['reply_to_name'] ) ?  '1' : '0'
		);
	}
	
	// reply title callback
	public function title_reply_callback()	// wrap the required text in the label area (needed for some themes)
	{
	 printf(
			"<table><tr><td><input type='text' id='title_reply' name='fst4c_options[title_reply]'   value='%s' size='30' maxlength='30'/></td><td valign='top'>The text to replace the 'Leave a Reply' link. (no HTML, just text). Leave this blank to use the default text of 'Leave a Reply'. Limit of 30 characters.</td></tr></table> ",
			isset( $this->options['title_reply'] ) ? esc_attr( $this->options['title_reply']) : ''
		);
	}
	
	// reply to callback
	public function reply_to_text_callback()	// wrap the required text in the label area (needed for some themes)
	{
	 printf(
			"<table><tr><td><input type='text' id='reply_to_text' name='fst4c_options[reply_to_text]'   value='%s' size='30' maxlength='30'/></td><td valign='top'>The text replace the 'Leave a Reply to name' link (no HTML, just text). Leave this blank to use the default text of 'Leave a Reply to name'. Limit of 30 characters.</td></tr></table> ",
			isset( $this->options['reply_to_text'] ) ? esc_attr( $this->options['reply_to_text']) : ''
		);
	}
	
	// cancel reply link callback
	public function cancel_reply_link_callback()	// wrap the required text in the label area (needed for some themes)
	{
	 printf(
			"<table><tr><td><input type='text' id='cancel_reply_link' name='fst4c_options[cancel_reply_link]'   value='%s' size='30' maxlength='30'/></td><td valign='top'>The text to replace the 'Cancel Reply' link (no HTML, just text). Leave this blank to use the default text of 'Cancel Reply'.  Limit of 30 characters. <em>Note: not used in all themes.</em></td></tr></table> ",
			isset( $this->options['cancel_reply_link'] ) ? esc_attr( $this->options['cancel_reply_link']) : ''
		);
	}
	
	// submit label callback
	public function submit_label_callback()	// wrap the required text in the label area (needed for some themes)
	{
	 printf(
			"<table><tr><td><input type='text' id='submit_label' name='fst4c_options[submit_label]'   value='%s' size='30' maxlength='30'/></td><td valign='top'>The text to put inside the Submit button (no HTML, just text). Leave this blank to use the default text of 'Post Comment'. Make sure that the 'Text Before' value contains the same text if you change the Submit button text. Limit of 30 characters.</td></tr></table> ",
			isset( $this->options['submit_label'] ) ? esc_attr( $this->options['submit_label']) : ''
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

$image = plugin_dir_url( __FILE__ ) . '/assets/icon-128x128.png';

	?>
<div class="wrap"> <img src="<?php echo $image; ?>" />
	<p>FormSpammerTrap 4 Comments adds form spam bot blocking to your comment form, and the comment processing code. We catch comment spam before it gets to your database.</p>
	<p>It senses human interaction with the comment form. It does not require those irritating captchas, hidden fields, silly questions, CSS tricks, or aother annoying things others use to try to (but fail to) block spam-bots.</p>
	<p>The FormSpammerTrap for Comments plugin provides several options to change the text of various parts of the comment form, such as the title of the comment form, the submit button, and more. You can also set the text used for the Reply link, and even include the name of the commenter in that Reply link.</p>
	<p>If a spam-bot tries to submit a comment, even when they bypass your comment form with non-browser tricks (think CURL), they will be sent to our <a href="http://formspammertrap.com" title="FormSpammerTrap.com" alt="FormSpammerTrap.com">FormSpammerTrap</a> page, and you will not see the spam-bot comment on your system.</p>
	<p>The plugin helps catch comment spam *before* it gets into your database, so other plugins (like Akismet) don't have to spend time checking each comment. And you don't have to keep on cleaning out spam comments from your database on the administrative Comment screen.
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
// ---------------------------------------------------------------------------- 
// add the javascript and other stuff only in the comment area, so it's not done in pages without comments		
// adds the action at end of head with a late priority, to make sure jquery has had time to load

// if jquery doesn't load, then something is really wrong, so this will probably never happen
add_action("wp_head","check_for_jquery",99); 
// insert the bogus action site for the form
add_action( 'comment_form', 'change_comment_action' ,99);

// insert the trapping functions on the fields
add_action( 'comment_form', 'force_insert_fst_code' ,99);

// add the cl function script
add_action('comment_form', 'fst4c_cl_script',98);

// only allow the tags we want; needs to be priority 9 to make sure it gets done
// can see the result if you allow the 'HTML code' message to display
// add_action('comment_form','fst4c_modify_tags',9);	 // adds the function to the comment_post action
//add_action('init','fst4c_modify_tags',11);	 // adds the function to the comment_post action
//add_action('comment_post','fst4c_modify_tags',9);	 // adds the function to the comment_post action

// adjust the allowedtags
global $allowedtags;
fst4c_modify_tags();

// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 
// other things to do all at once
// 	these are related to comment functions, so not used on non-comment pages

// set up the new comment form fields
add_filter( 'comment_form_default_fields', 'fst4c_comment_form_fields',9,1 );

// adjust the before the comment form text area 
add_filter('comment_form_defaults', 'fst4c_modify_comment_form_text_area',9,1); 

// adjust after the comment form area
add_filter('comment_form_defaults', 'fst4c_text_around_comment_form',9,1);

// adjust the cancel comment reply link if specified
add_filter( 'comment_form_defaults', 'fst4c_cancel_comment_reply_link',9,1 );

// add reply to name thing
add_filter('comment_reply_link', 'fst4c_adjust_title_reply',9,2);

// preprocess comment after submitted to remove urls
add_filter( 'preprocess_comment' , 'fst4c_comment_remove_url' ); 

// catch if the non-displayed url field is not empty
add_action('preprocess_comment', 'fst4c_url_field_catcher',8);

// Add Nonce To Comment Form
//add_action('comment_form', 'fst4c_add_key',2);

// Add Nonce Check To Comment Form Post processing
//add_action('pre_comment_on_post', 'fst4c_key_check');

// preprocess comment after submitted to remove any mouseover stuff
add_filter( 'preprocess_comment' , 'remove_mouseover',2,1 ); 

// preprocess comment after submitted to remove all htnk codes
add_filter( 'preprocess_comment' , 'remove_html',2,1 ); 

// ---------------------------------------------------------------------------- 

// ---------------------------------------------------------------------------- 
// end of add_actions and add_filters for posts/pages with comments open
// ---------------------------------------------------------------------------- 

// ---------------------------------------------------------------------------- 
// check to make sure jquery is loaded
function check_for_jquery() {
	if(!wp_script_is( 'jquery', $list = 'done' )) { return; }			// exit if jquery is not loaded (rare)
	}
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

// ---------------------------------------------------------------------------- 
// use jquery to force the insert of the formspammertrap_cl() function inside the required fields
// 		attempts to fix themes that use non-standard way of building
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

// ---------------------------------------------------------------------------- 
//	- this section adds formspammertrap_CL() function code
function fst4c_cl_script() {
	$xguid1 = guid();
	$xguid2 = guid();
?>
<script type="text/javascript">
	<!-- 
	var formspammertrap_code_<?php echo $xguid1; ?> = "<?php echo site_url( '/wp-comments-post.php' ); ?>";		// "comment_"
	var formspammertrap_code_<?php echo $xguid2; ?> = "";		// "action.php"
	var FormID="commentform";	  // the name of the form, must match the form 'name' parameter
	
	function formspammertrap_CL() {
	var elem = document.getElementById("commentform");
	elem.action=formspammertrap_code_<?php echo $xguid1; ?>+formspammertrap_code_<?php echo $xguid2; ?>;
	}
	-->
</script>
<?php
	return; }
	
function guid()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X_%04X_%04X_%04X_%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
// ---------------------------------------------------------------------------- 
// this is the new comment fields with the CL() added to onclick/onfocus for required fields only
//		required field includes the comment text area 
//		replaces any customization of the comment form fields

function fst4c_comment_form_fields( $fields ) {
	$xoptions = get_option( 'fst4c_options' );
	$xlabel_submit = $xoptions['submit_label'];
	$req = $xoptions['name_email_req'] ;
	if ($xoptions['wrap_required_text']) {	$wrap_req_text = '<br>Required';} else {$wrap_req_text = '&nbsp;&nbsp;Required';	}
	$aria_req = ( $req ? " aria-required='true' required='true' " :" aria-required= 'false' required='false' " );
	$aria_req_in_placeholder = ( $req ? '(* required) ':'' );
//	unset($GLOBALS[$fields]);		// clear out any existing comment form fields
	$fields =  array(
	// get the required fields
	'author' =>
	'<p class="comment-form-author"><label for="author">' . __( 'Your Name', 'domainreference' ) .
	( $req ? '<span class="required">' . $wrap_req_text . '</span>'  : '' ) . '</label>' .
	'<input placeholder="Your Name ' . $aria_req_in_placeholder . ' " id="author" name="author" type="text" onclick="formspammertrap_CL();" onfocus="formspammertrap_CL();" value="' . esc_attr( $commenter['comment_author'] ) .
	'" size="30"' . $aria_req . ' /></p>',
	
	'email' =>
	'<p class="comment-form-email"><label for="email">' . __( 'Your Email', 'domainreference' )  .
	( $req ? '<span class="required">' . $wrap_req_text . '</span>'  : '' ) . '</label>' .
	'<input placeholder="Your Email ' . $aria_req_in_placeholder . ' " id="email" name="email" type="text"  onclick="formspammertrap_CL()" onfocus="formspammertrap_CL()" value="' . esc_attr(  $commenter['comment_author_email'] ) .
	'" size="30"' . $aria_req . ' /></p>',
	
	'url' =>		// default url, but we don't use this (we remove the URL field with the next statement)
	'<p class="comment-form-url"><label for="url">' . __( 'Your Website', 'domainreference' ) . '</label>' .
	'<input placeholder="Your Web Site" id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
	'" size="30" /></p>',
	
	// sorry no urls allowed, because form spammers like the URL field, and most people don't care about it
	'url' => '',
	
	// add a extra hidden field to the comment form; value will be set with human interaction
	// even though hidden fields aren't really effective at stopping spammers
	'user_ip' =>'<input name="user_ip" type="hidden" id="user_ip"  />'
	
	// you would think that you would put the comment textarea here, but nope. 
	// it needs to use the comment_form_defaults hook, not comment_form_default_fields hook
	
	);
	return $fields; }

// ---------------------------------------------------------------------------- 
// ---------------------------------------------------------------------------- 
//// this blocks spam bots from the comment field 
// note: this must be installed by the comment_form_defaults hook, not the comment_form_default_fields hook,
//		otherwise, you get duplicate comment textareas!
function fst4c_modify_comment_form_text_area($arg) {
//	echo "<pre>";
//	print_r($arg);
//	echo "</pre><hr>";

	$xoptions = get_option( 'fst4c_options' );
	$xlabel_submit = $xoptions['submit_label'];

//	if (is_single() ) { 
//$xlabel_submit .= "611";
//	}
//	if (comments_open() ) { 
//$xlabel_submit .= " 615";
//	}

	if ($xoptions['submit_label']) {$arg['label_submit'] = 	$xlabel_submit ;	}
	if ($xoptions['title_reply']) {$arg['title_reply'] = '<p align="center">'. $xoptions['title_reply'] . '</p>';}
	if ($xoptions['reply_to_text']) {$arg['reply_to_text'] =  $xoptions['reply_to_text'];}
	

	if ($xoptions['wrap_required_text']) {	$wrap_req_text = '<br>Required';} else {$wrap_req_text = '&nbsp;&nbsp;Required';	}

	$arg['comment_field'] = 
	 '<p class="comment-form-comment"><label for="comment" >Your Comment<span class="required">' . $wrap_req_text . '</span></label><textarea placeholder="Your Comment (* required)" id="comment" name="comment" cols="45" rows="12" onclick="formspammertrap_CL();" onfocus="formspammertrap_CL();" aria-required="true" required="required" ></textarea></p>'
;
	return $arg; 
	}

// ---------------------------------------------------------------------------- 
// adjust the cancel comment reply link if specified

function fst4c_cancel_comment_reply_link($arg) {
	$xoptions = get_option( 'fst4c_options' );
	
	if ($xoptions['cancel_reply_link']) {$arg['cancel_reply_link'] =  __($xoptions['cancel_reply_link']);}
	return $arg; 
}

// ---------------------------------------------------------------------------- 
// set the message after the comment form
function fst4c_text_around_comment_form($arg) {
	global $xoptions;
	$xtext_before_comment = $xoptions['text_before'];
	if ($xoptions[urls_allowed]) {
		$xtext_before_comment .= ' <em>You are allowed to enter ' . $xoptions[urls_allowed] . ' URL(s) in the comment area.</em>'; } else {$xtext_before_comment .= ' <em>You are not allowed to enter any URLs in the comment area.</em>';
	}
	$xtext_after_comment = $xoptions['text_after'];
	if ($xtext_before_comment) {
		$arg['comment_notes_before'] = '<p class="form-allowed-tags" align="center">' . sprintf( __(  $xtext_before_comment )) . '</p>';
	}
	if ($xtext_before_comment) {
		$arg['comment_notes_before'] = '<p class="form-allowed-tags" align="center">' . sprintf( __(  $xtext_before_comment )) . '</p>';
	}
	$xshow_allowed_html = $xoptions['show_allowed_html'];
	if ($xshow_allowed_html) {
		$xallowed_tags_msg = '<p>The following HTML codes are allowed in the comment area: ' . allowed_tags() . '</p>';
		$xtext_after_comment .= $xallowed_tags_msg;}
		
	$xtext_after_comment .= '<p>' . $xoptions['text_after'] . '</p>';
	if ($xtext_before_comment) {
		$arg['comment_notes_before'] = '<p class="form-allowed-tags" align="center">' . sprintf( __(  $xtext_before_comment )) . '</p>';
	}
	if ($xtext_after_comment) {
		if ($xoptions['show_fst_message']) {
		$xtext_after_comment .= '<br><em>Form filling spam bots are redirected to the <a href="http://formspammertrap.com" title="FormSpammerTrap.com" target="_blank">FormSpammerTrap.com</a> web site.</em>';}
		$arg['comment_notes_after'] = '<p class="form-allowed-tags" align="center">' . sprintf( __( $xtext_after_comment )) . '</p>';
	}
	return $arg;
}

// ---------------------------------------------------------------------------- 
// check for too many urls in the comment content
function fst4c_comment_remove_url( $commentdata ) {
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
	return $commentdata;	// excess urls stripped or replaced
}
// ---------------------------------------------------------------------------- 
//  Change the comment reply link to use 'Reply to &lt;Author First Name>'
//  idea based on https://raam.org/2013/personalizing-the-wordpress-comment-reply-link/

function fst4c_add_comment_author_to_reply_link($link, $args, $comment){

	$comment = get_comment( $comment );
	// If no comment author is blank, use 'Anonymous'
	if ( empty($comment->comment_author) ) {
		if (!empty($comment->user_id)){
			$user=get_userdata($comment->user_id);
			$author=$user->user_login . '&nbsp;';
		} else {
			$author = __('Anonymous ');
		}
	} else {
		$author = $comment->comment_author . '&nbsp;';
	}
	// we don't use this next code block, since we want to use the whole name
	// If the user provided more than a first name, use only first name
	//	if(strpos($author, ' ')){
	//		$author = substr($author, 0, strpos($author, ' '));
	//	}

	// Replace Reply Link with "Reply to [Author Name]"
	$reply_link_text = $args['reply_text'];
	$link = str_replace($reply_link_text, 'Reply to ' . $author, $link);
		//die("line 743- " . $link);
	return $link;
}
// get the option and add the filter if option enabled
	$xoptions = get_option( 'fst4c_options' );
	$xreply_to_name = $xoptions['reply_to_name'];
	if ($xreply_to_name) {
		add_filter('comment_reply_link', 'fst4c_add_comment_author_to_reply_link', 10, 3);  
	}
	
// ---------------------------------------------------------------------------- 
function fst4c_adjust_title_reply($link, $args) {
	$xoptions = get_option( 'fst4c_options' );
	$xreply_to_name = $xoptions['reply_to_name'];
	// adjust for custom title_reply value
	if (!$reply_to_name) {return $link; } 	// don't change anything
    $comment = get_comment( $comment );
 
    // If no comment author is blank, use 'Anonymous'
    if ( empty($comment->comment_author) ) {
        if (!empty($comment->user_id)){
            $user=get_userdata($comment->user_id);
            $author=$user->user_login;
        } else {
            $author = __('Anonymous');
        }
    } else {
        $author = $comment->comment_author;
    }
    // Replace Reply Link with "Reply to &lt;Author First Name>"
    $reply_link_text = $args['reply_text'];
    $link = str_replace($reply_link_text, 'Reply to ' . $author, $link);
 
    return $link;
}

// ---------------------------------------------------------------------------- 
// override any theme comment validation

// check for the URL field sent, which is always sent by spammers,
// and we don't have that field in our comment form; so just another spambot trap
// if we find the URL field with any other value, off they go to the FormSpammerTrap.com site
function fst4c_url_field_catcher($commentdata) {
	if(! $_POST['user_ip'] === 'notbot') {
		?><script>
		var url = "http://formspammertrap.com";	
		$(location).attr('href',url);
		</script>
		<?php 
		die('Go away evil person!');	// because we don't care if they see the site
		return;
	}
return $commentdata;
}


// ---------------------------------------------------------------------------- 
// add the nonce key to the comment form, and check on submit
// based on http://www.daharveyjr.com/fighting-wordpress-comment-spam-with-a-nonce/
// Generate Nonce
function fst4c_add_key() {
	wp_nonce_field(fst4c_key_check,'fst4c_key');
}
 
function remove_mouseover($commentdata) {
	global $commentdata;
	global $xoptions;
	
	// regex to any 'onmouseover' junk
    $re = "/<.*>|onmouseover.*\"|onmouseover.*'/i";
	$str = $commentdata['comment_content'];
    $subst = "";
	// strip out any 'onmouseover' tags
    $commentdata['comment_content'] = preg_replace($re, $subst, $str);
	
return $commentdata; 
}

// remove all html codes 
function remove_html($commentdata) {
	global $commentdata;
	global $xoptions;
	if ($xoptions['remove_html']) {
 		$commentdata['comment_content'] = strip_tags_content($commentdata['comment_content']) ;
	}
	return $commentdata;
}
// function to strip all tags from $text, no matter where; from the php strip_tags manual comments
function strip_tags_content($text, $tags = '', $invert = FALSE) {

  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
  $tags = array_unique($tags[1]);
   
  if(is_array($tags) AND count($tags) > 0) {
    if($invert == FALSE) {
      return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    else {
      return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
    }
  }
  elseif($invert == FALSE) {
    return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
  }
  return $text;
} 

function fst4c_modify_tags() {		// hint from http://www.goldenapplewebdesign.com/wordpress-take-control-of-html-tag-filtering/
	global $allowedtags; 		// get the global thing
	$allowedtags = array();
$allowedtags = array(
	'a' => array(
        'href' => array (),
        'title' => array ()),
	'b' => array(),
    'blockquote' => array(),
    'cite' => array (),
    'em' => array (), 
	'i' => array (),
    'strike' => array(),
    'strong' => array(),
);	

//$allowedtags['a'] = array();  // this only allows the 'a' tag, not any sub-tags things
return;
}


//function fst4c_allowed_tags( $tags ) {
//	// Add <span> tag
//	$tags['span'] = array(
//		'id' 	=> array(), // Allow ID's in those spans
//		'class' => array(), // Allow classes in those spans
//		'style' => array() // Allow style in those spans
//	);
//	return $tags;
//}
//add_filter( 'themeblvd_allowed_tags', 'fst4c_allowed_tags' );

// Check Nonce Field Validity
function fst4c_key_check() {
	if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'fst4c_key')) {
		?><script>
		var url = "http://formspammertrap.com";	
		$(location).attr('href',url);
		</script>
		<?php 
		die('Go away and no come back! ');	// gooddbye and good riddance!
		return;
	}
}
 

// ---------------------------------------------------------------------------- 
// check for WP/PHP versions, and add inline errors for bad form data
// ---------------------------------------------------------------------------- 
// based on the code from the plugin: https://wordpress.org/plugins/comment-form-inline-errors/
// 		which handles form errors (missing required fields, etc) 
//		plus checks WordPress and PHP versions
//		Code used via that plugin's GPL2, so is lawfully included (but props for saving me a lot of work!)


if (!defined('ABSPATH')) { exit; }

if (!class_exists('wpCommentFormInlineErrors')){
	class wpCommentFormInlineErrors
	{
		/* minimum required wp version */
		public $wpVer = "3.0";
		/* minimum required php version */
		public $phpVer = "5.3";
		// add the action to call the init function
		public function __construct() { add_action('init', array($this, 'init')); }

		// initialize some stuff
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

		// check the WordPress version, and display notices as needed
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

		// Deactivates our plugin if anything goes wrong. Also, removes the
		//		"Plugin activated" message, if we don't pass requriments check.

		private function pluginDeactivate()
		{
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			deactivate_plugins(plugin_basename(__FILE__));
			unset($_GET['activate']);
		}

		// hey you!  get your WordPress upgraded! 
		public function displayVersionNotice()
		{
			global $wp_version;
			$this->displayAdminError(
				'<p>Your version of WordPress is not current, so you shouldn\'t use this plugin. You should really upgrade to the latest WordPress. Do it now!</p><p>Until you upgrade, this plugin won\'t work, leaving your site open to all sorts of hackers. (This plugin requires at least WordPress varsion ' . $this->wpVer . ' or higher.)</p> <p>You are currently using ' . $wp_version . '. Please upgrade your WordPress version now!</p><p>The FormSpammerTrap for Comments plugin has been disabled until you update your WordPress version.</p>');
		}

		// hey you! updatae your PHP version!
		public function displayPHPNotice()
		{
			$this->displayAdminError(
				'<p>You need PHP version at least '. $this->phpVer .' to run this plugin. You should really update your PHP version to protect your site against hackers.</p><p>You are currently using PHP version ' . PHP_VERSION . '.</p><p>The FormSpammerTrap for Comments plugin has been disabled until you update your PHP version.</p>');
		}

		// display the error all pretty
		private function displayAdminError($error) { echo '<div id="message" class="error"><p><strong>' . $error . '</strong></p></div>';  }

		// overwrite WordPress error handling
		function getWpDieHandler($handler){ return array($this, 'handleWpError'); }

		// display the error messages inside the array, make it pretty
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

		// display the error on the form inline nice and fancy
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

		// put the form data back in so it doesn't need to be filled out again
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

		// a bit of special handling for the comment field
		function formCommentDefault($comment_field)
		{
			$formFields = $_SESSION['formFields'];
			unset($_SESSION['formFields']);
			return str_replace('</textarea>', $formFields['comment'] . '</textarea>', $comment_field);
		}

		// a little help with filling out the form
		public function stringContains($needle, $haystack){ return strpos($haystack, $needle) !== FALSE; }

	}

}

new wpCommentFormInlineErrors();

// ---------------------------------------------------------------------------- 
// all done!
// ---------------------------------------------------------------------------- 

