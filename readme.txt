===FormSpammerTrap for Comments ===
Contributors: Rick Hellewell
Donate link: http://www.FormSpammerTrap.com/
Tags: comments, spam, spambot protection, stop, form spam
Requires at least: 4.0.1
Tested up to: 4.1
Stable tag: 1.02
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Blocks comment spam without captchas, hidden fields, etc. Removes excess URLs from comment area. Allows change to text before and after comment form.

== Description ==

FormSpammerTrap 4 Comments adds form spam bot blocking to your comment form.

It senses human interaction with the comment form. It does not require those irritating captchas, hidden fields, silly questions, or aother annoying things others use to try to (but fail to) block spam-bots.

If a spam-bot tries to submit a comment, they will be sent to our FormSpammerTrap page, and you will not see the spam-bot comment on your system.

You will find more information at our <a href='http://www.FormSpammerTrap.com'>FormSpammerTrap</a> web site. We also have solutions for WordPress contact forms and custom-built sites. You can contact us with any questions or issues on that site..


== Installation ==

This section describes how to install the plugin and get it working.

1. Download the zip file, uncompress, then upload to `/wp-content/plugins/` directory. Or download, then upload/install via the Add Plugin page.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Change settings in Settings, 'FormSpammerTrap for Comments Settings'

== Frequently Asked Questions ==

**How do you block spam bots?**

We use a technique that looks for human interaction with the comment form. The name, email, and comment text area are all checked for this human interaction.

**What happens to a spam bot filling out the form?**

The spam bot is redirected to our FormSpammerTrap page at www.FormSpammerTrap.com . The comment is essentially 'thrown away'; it isn't added to your posts' comment.

**But can't I block comment spam with (take your pick: captchas, hidden fields, CSS tricks, silly questions, whatever)?**

Those techniques just don't work with modern spam-bots. Captchas are easily bypassed. Hidden fields are not hidden to the smarter spam bots, neither are CSS tricks that hide a field from display. And silly questions are just irritating.

Our technique requires a human to interact with the form. Form spamming bots can't provide that interaction. And without that human interaction, submitting the form sends the submitter to our FormSpammerTrap.com site. The comment is never saved. So you don't see the form spam.

**Does the technique also work with comment forms?**

No, it is only for comments. But we have a free solution on our FormSpammerTrap.com web site that will apply the same techniques to Contact forms. It is easy to implement; it's just a template that should work with the theme you are using.

**Are there things on my site that will not work with this plugin?**

Perhaps. Any plugin that modifies the comment form might interfere with our technique. If you have problems, you can temporarily disable our plugin, or the other comment form plugin, and see if the problem re-occurs.

Also, some themes are not well-behaved, and might cause problems.

For instance, we have noticed that some themes are doing goofy (non-standard) things with the comment form code, so if required fields are empty when you submit, you get a 'fill in this field' message for a required field rather than getting redirected to the FormSpammerTrap.com site. Nothing we can do about themes that don't follow good WordPress coding practices.

We have verified that it works properly with the WordPress "Twenty-" themes, plus several others. Let us know if you have other themes where it isn't working properly. 

The plugin does do some jquery stuff to try to insert the trapping techniques into the comment form. This may allow the plugin to work on many themes.

**When I activate the plugin, submitting a comment always redirects to the FormSpammerTrap site. Why?**

As stated above, some themes don't follow proper WordPress coding standards as it relates to supporting changes to the comment form. Because our plugin uses standard WordPress functions to add functionality to the comment form, these themes ignore that standard coding and build their own forms. 

You can verify this by temporarily changing your theme to one that follows WordPress standards, like the TwentyFifteen theme. If the comment form works properly, then you know that the theme is at fault, not our plugin. If the form still doesn't work with the TwentyFifteen theme, then let us know.

We are working on an option to "Gibbs Slap" those themes to bypass their improper theme code as it relates to comment forms.

**What about limiting the number of URLs in a comment?**

Our plugin does that too. Most comment form spammers will try to put lots of links in the comment. You can partially block that with the settingsi n the Discussion menu. We take it a step farther by letting you determine the number of URLs you want in a comment. The plugin then removes removing the excess URLs from the comment.

**What if I want something that shows when excess URLs are deleted?**

The plugin provides for that also. You can determine if the excess URLs are deleted, or if they are replaced with a '[URL Redacted]' text.

**What about changing the text that shows before and after the comment form?**

The plugin provides that also. In the FST4c Settings screen (under the Settings menu), you can enter the text that will be displayed before and after the comment form. We don't allow any formatting of that text, to prevent any security problems. 

**But what if I want the default text to show before and after comments?**

The plugin allows for that. Just leave those fields blank on the FST4C settings screen. 

**What about the error message when a required field is not fille out?**

The plugin shows the error message (such as "Please fill out required field (name)") while re-displaying the comment form, with the fields showing the data previously entered. The visitor can just fill in the required fields, and submit again.

**What if I don't like how the plugin changes things?**

You can just deactivate the plugin. Your settings will be saved if you want to reactivate later.

**Does the plugin make changes to the database?**

The plugin only adds one 'row' to the Options database, using standard WordPress functions. The plugin will read the values as needed, minimizing calls to the database to limit any overhead against the database.

**Does the plugin require anything extra on the client (visitor) browser?**

Not that we are aware of. The things we do are done through standard WordPress calls, plus one bit of "DOM" access, which all modern browsers support. There should be no effect of the plugin on how the visitor interacts with your site.

**Where can we go for support if there is a problem or question - or a new feature we think will be nifty?**

You can use the plugin support page for questions. Or you can contact us directly via the Contact Us page at www.FormSpammerTrap.com . We usually respond within 24 hours (and are usually faster than that).

**How much does the plugin cost?**

It's free, as is the Contact form template available on the http://www.FormSpammerTrap.com site. But there is a place to donate there, if you are so inclined. (And we will appreciate that inclination!)

**What else do you do?**

We do lots of WordPress sites: implementation, customization, and more. You can find more info at our business site at www.CellarWeb.com .


== Screenshots ==

1. Shows the FormSpammerTrap for Comments settings screen, found on the Settings, 'FormSpammerTrap for Comments Settings' screen. (assets/screenshot-1.jpg)
1. The Comment form, using the settings as shown on screenshot 1. (assets/screenshot-2.jpg)

== Changelog ==

= 1.02 (17 Feb 2015) = 
* Sometimes code cleanup introduces a new problem (sigh). Fixed that new problem.

= 1.01 (16 Feb 2015) =

* Fixed bug causing submit button to be misplaced away from the comment area with threaded comments due to an errant closing div.

* added additional explanatory text to all fields on the settings page.

* added a new checkbox to put the 'required' text on a separate line in the label area next to the input fields. Some themes have a narrow label area which would not display the full 'required' text in the field label, so you would enable this new checkbox. Note that not all themes put a label next to the comment form input fields.

* added a new checkbox to change the 'Reply' link text to 'Reply to <author-name>', where <author-name> is the name associated with the comment. So a comment from 'Rick H.' will have a reply link of 'Reply to Rick H.' (it uses the full name from the author field of the logged in user). Leave blank to use the standard 'Reply' link text. Note that some themes many not support this option.

* some code cleanup and efficiencies.

* format tweaking for the readme.txt file to make it a bit more readable (and to comply with best practices).

= 1.0 =
* Initial release (1 Feb 2015)


== Upgrade Notice ==

= 1.01 = 
Released 16 Feb 2015 - Fixed a display bug with threaded comments, added explanatory text on the settings page, added additional features.

= 1.0 =
Initial release (1 Feb 2015)


