=== IM8 Box Hide ===
Contributors: intermedi8
Donate link: http://intermedi8.de
Tags: metabox, hide, meta, box, roles, remove, admin, disable
Requires at least: 2.9.2
Tested up to: 3.8.1
Stable tag: trunk
License: MIT
License URI: http://opensource.org/licenses/MIT

Hide meta boxes based on roles.

== Description ==

**Hide meta boxes based on roles.**

* Hide core meta boxes (e.g., _Page Attributes_ and _Custom Fields_)
* Hide plugin meta boxes (e.g., _All in One SEO Pack_ and _MapPress_)
* Hide theme meta boxes
* Hide custom post type meta boxes
* Support for additional user roles (works great with plugins such as _Capability Manager_, _Capability Manager Enhanced_ and _User Role Editor_)
* Multilanguage: currently English, German, Dutch and Italian (please help us with translations if you want to see additional languages)
* Ad-free (of course, donations are welcome)

If you would like to **contribute** to this plugin, see its <a href="https://github.com/intermedi8/im8-box-hide" target="_blank">**GitHub repository**</a>.

== Installation ==

1. Upload the `im8-box-hide` folder to the `/wp-content/plugins` directory on your web server.
2. Activate the plugin through the _Plugins_ menu in WordPress.
3. Find _IM8 Box Hide_ in the _Users_ menu in WordPress.

== Screenshots ==

1. **IM8 Box Hide settings page** - Here you can individually hide meta boxes for each (custom) post type and user group.
2. **Add New Page page with hidden meta boxes** - Only the _Publish_ meta box is visible, all other meta boxes are hidden.

== Changelog ==

= 2.4.1 =
* compatible up to WordPress 3.8.1
* added some `index.php` files

= 2.4 =
* the URL for the AJAX request is now set by using the `admin_url` function
* integrated plugin update message
* added missing Comments and Revisions meta boxes for supporting post types
* corrected some DocBlocks

= 2.3 =
* fixed bug that prevented plugin from being loaded when activated network-wide

= 2.2 =
* fixed bug in `remove_meta_boxes` function that broke other plugins working with `$GLOBALS['current_user']` (such as Adminimize)

= 2.1 =
* added direct access guard
* removed trailing `?>`
* in `get_post_types` function, changed argument `'public' => true` to `'show_ui' => true`

= 2.0 =
* complete refactoring (object oriented programming)
* more usage of WordPress core functions
* compatible up to WordPress 3.8
* moved screenshot to `assets` folder
* added banner image

= 1.48 =
* pushed plugin back to active state

= 1.47 =
* added Italian translation (credits go to Francesco Canovi)

= 1.45 =
* added support for the new _Featured Image_ meta box (thanks to _hansmagnus_ for the hint)

= 1.44 =
* added Dutch translation by Marius Siroen (_grafcom_)
* fixed some meta box descriptions

= 1.42 =
* bugfix fixing renamed meta boxes in WordPress 3 (credits go to _grafcom_ for reporting the bug and providing the workaround)

= 1.3 =
* bugfix for another _invalid argument_ warning when accessing the config panel (credits go to _iamfriendly_ for reporting the bug and providing the workaround)

= 1.2 =
* bugfix for _invalid argument_ warning when accessing the config panel (credits go to _potel_ for reporting the bug, and to _straddieplastic_ for providing the workaround)

= 1.1 =
* small changes for WordPress 3.0 Beta

= 1.0 =
* initial release