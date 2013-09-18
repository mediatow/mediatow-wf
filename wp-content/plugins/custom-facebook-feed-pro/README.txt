=== Custom Facebook Feed PRO ===
Author: Smash Balloon
Support Website: http://smashballoon/custom-facebook-feed/
Requires at least: 3.0
Tested up to: 3.6.1
Version: 1.2.9
License: Non-distributable, Not for resale

The Custom Facebook Feed allows you to display a customizable Facebook feed of any public Facebook page on your website.

== Description ==

Display a **customizable**, **responsive** and **search engine crawlable** version of your Facebook feed on your WordPress site.

Other Facebook plugins use iframes to show your feed which don't allow you to customize how they look, aren't responsive and are not crawlable by search engines. The Custom Facebook Feed inherits your theme's style to display a feed which is responsive, crawlable and seamlessly matches the look and feel of your site.

* **Completely Customizable** - by default inherits your theme's styles
* **Feed content is crawlable by search engines adding SEO value to your site** - other Facebook plugins embed the feed using iframes which are not crawlable
* **Completely responsive and mobile optimized** - works on any screen size
* Use the shortcode to display the feed in a page, post or widget anywhere on your site
* Embed YouTube and Vimeo videos right into your feed
* Show the number of likes, comments and shares beneath each post
* Each post links to it's Facebook equivalent to allow people to join in the conversation
* Limit the number of posts to be displayed
* Set a maximum length for both the post title and body text
* Use the shortcode to display feeds from multiple Facebook pages anywhere on your site

== Installation ==

1. Install the Custom Facebook Feed either via the WordPress plugin directory, or by uploading the `custom-facebook-feed` folder to your web server (in the `/wp-content/plugins/` directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Custom Facebook Feed' plugin settings page to configure your feed.
4. Use the shortcode `[custom-facebook-feed]` in your page, post or widget to display your feed.
5. You can display multiple feeds of different Facebook pages by specifying a Page ID directly in the shortcode: `[custom-facebook-feed id=SarahsBakery show=5]`.
6. You can limit the length of the title and body text by using 'textlength=100' and 'desclength=150' (for example) in the shortcode.

== Changelog ==

= 1.2.9 =
* New: Added a 'See More' link to expand any text which is longer than the character limit defined
* New: Choose to show posts by other people in your feed
* New: Option to show the post author's profile picture and name above each post
* New: Specify the format of the Event date
* Tweak: Default date format is less specific and better mimics Facebook's - credit Mark Bebbington
* Fix: When a photo album is shared it now links to the album itself and not just the cover photo
* Fix: Fixed issue with hyperlinks in post text which don't have a space before them not being converted to links
* Minor fixes

= 1.2.8 =
* Tweak: Added links to statuses which link to the Facebook page
* Tweak: Added classes to event date, location and description to allow custom styling
* Tweak: Removed 'Where' and 'When' text from events and made bold instead
* Tweak: Added custom stripos function for users who aren't running PHP5+

= 1.2.7 =
* Fix: Fixes the ability to hide the 'View on Facebook/View Link' text displayed with posts

= 1.2.6 =
* Fix: Prevents the WordPress wpautop bug from breaking some of the post layouts
* Fix: Event timezone fix when timezone migration is enabled

= 1.2.5 =
* Tweak: Replaced jQuery 'on' function with jQuery 'click' function to allow for compatibilty with older jQuery versions
* Minor bug fix regarding hyperlinking the post text

= 1.2.4 =
* New: Added a ton more shortcode options
* New: Added options to customize and format the date
* New: Add your own text before and after the date and in place of the 'View on Facebook' and 'View Link' links
* New: If there are no comments on a post then choose whether to hide the comment box or use your own custom text
* Tweak: Separated the video/photo descriptions and link descriptions into separate checkboxes in the Post Layout section
* Tweak: Changed the layout of the Typography section to allow for the additional options
* Tweak: Added a System Info section to the Settings page to allow for simpler debugging of issues related to PHP settings

= 1.2.3 =
* New: Choose to only show certain types of posts (eg. events, photos, videos, links)
* New: Add your own custom CSS to allow for even deeper customization
* New: Optionally link your post text to the Facebook post
* New: Optionally link your event title to the Facebook event page
* Fix: Only show the name of a photo or video if there is no accompanying text
* Some minor modifications

= 1.2.2 =
* Fix: Set all parts of the feed to display by default

= 1.2.1 =
* Select whether to hide or show certain parts of the posts
* Minor bug fixes

= 1.2.0 =
* Major Update!
* New: Loads of customization, layout and styling options for your feed
* New: Define feed width, height, padding and background color
* New: Choose from 3 preset post layouts; thumbnail, half-width, and full-width
* New: Change the font-size, font-weight and color of the post text, description, date, links and event details
* New: Style the comments text and background color
* New: Choose from light or dark icons
* New: Select whether the Like box is shown at the top of bottom of the feed
* New: Choose Like box background color
* New: Define the height of the video (if required)


= 1.1.1 =
* New: Shared events now display event details (name, location, date/time, description) directly in the feed

= 1.1.0 =
* New: Added embedded video support for youtu.be URLs
* New: Email addresses within the post text are now hyperlinked
* Fix: Links beginning with 'www' are now also hyperlinked

= 1.0.9 =
* Bug fixes

= 1.0.8 =
* New: Most recent comments are displayed directly below each post using the 'View Comments' button
* New: Added support for events - display the event details (name, location, date/time, description) directly in the feed
* Fix: Links within the post text are now hyperlinked

= 1.0.7 =
* Fix: Fixed issue with certain statuses not displaying correctly
* Fix: Now using the built-in WordPress HTTP API to get retrieve the Facebook data

= 1.0.6 =
* Fix: Now using cURL instead of file_get_contents to prevent issues with php.ini configuration on some web servers

= 1.0.5 =
* Fix: Fixed bug caused in previous update when specifying the number of posts to display

= 1.0.4 =
* Tweak: Prevented likes and comments by the page author showing up in the feed

= 1.0.3 =
* Tweak: Open links to Facebook in a new tab/window by default
* Fix: Added clear fix
* Fix: CSS image sizing fix

= 1.0.2 =
* New: Added ability to set a maximum length on both title and body text either on the plugin settings screen or directly in the shortcode

= 1.0.1 =
* Fix: Minor bug fixes.

= 1.0 =
* Launch!