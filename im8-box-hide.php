<?php
/**
 * Plugin Name: IM8 Box Hide
 * Plugin URI: http://wordpress.org/plugins/im8-box-hide/
 * Description: Hide meta boxes based on roles.
 * Version: 2.4.1
 * Author: intermedi8
 * Author URI: http://intermedi8.de
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: im8-box-hide
 * Domain Path: /languages
 */


// Exit on direct access
if (! defined('ABSPATH'))
	exit;


if (! class_exists('IM8BoxHide')) :


/**
 * Main (and only) class.
 */
class IM8BoxHide {

	/**
	 * Plugin instance.
	 *
	 * @type	object
	 */
	protected static $instance = null;


	/**
	 * Plugin version.
	 *
	 * @type	string
	 */
	protected $version = '2.4.1';


	/**
	 * basename() of global $pagenow.
	 *
	 * @type	string
	 */
	protected static $page_base;


	/**
	 * Plugin textdomain.
	 *
	 * @type	string
	 */
	protected $textdomain = 'im8-box-hide';


	/**
	 * Plugin nonce.
	 *
	 * @type	string
	 */
	protected $nonce = 'im8_box_hide_nonce';


	/**
	 * Plugin option name.
	 *
	 * @type	string
	 */
	protected $option_name = 'im8_box_hide';


	/**
	 * Plugin settings page name.
	 *
	 * @type	string
	 */
	protected $settings_page_name = 'im8-box-hide';


	/**
	 * Plugin settings page.
	 *
	 * @type	string
	 */
	protected $settings_page;


	/**
	 * Plugin repository.
	 *
	 * @type	string
	 */
	protected $repository = 'im8-box-hide';


	/**
	 * Standard meta box contexts.
	 *
	 * @type	array
	 */
	protected $contexts = array(
		'normal',
		'advanced',
		'side',
	);


	/**
	 * Standard meta box priorities.
	 *
	 * @type	array
	 */
	protected $priorities = array(
		'high',
		'core',
		'default',
		'low',
	);


	/**
	 * Constructor. Register activation routine.
	 *
	 * @see		get_instance()
	 * @return	void
	 */
	public function __construct() {
		register_activation_hook(__FILE__, array(__CLASS__, 'activation'));
	} // function __construct


	/**
	 * Get plugin instance.
	 *
	 * @hook	plugins_loaded
	 * @return	object IM8BoxHide
	 */
	public static function get_instance() {
		if (null === self::$instance)
			self::$instance = new self;

		return self::$instance;
	} // function get_instance


	/**
	 * Register uninstall routine.
	 *
	 * @hook	activation
	 * @return	void
	 */
	public static function activation() {
		register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));
	} // function activation


	/**
	 * Check if the plugin has to be initialized.
	 *
	 * @hook	plugins_loaded
	 * @return	boolean
	 */
	public static function init_on_demand() {
		global $pagenow;

		if (empty($pagenow))
			return;

		self::$page_base = basename($pagenow, '.php');

		// Initialize plugin for admin pages only
		if (is_admin())
			add_action('wp_loaded', array(self::$instance, 'init'));
	} // function init_on_demand


	/**
	 * Register plugin actions and filters.
	 *
	 * @hook	wp_loaded
	 * @return	void
	 */
	public function init() {
		add_action('admin_init', array($this, 'autoupdate'));
		add_action('admin_menu', array($this, 'add_settings_page'));

		if ('post-new' === self::$page_base)
			add_action('do_meta_boxes', array($this, 'get_meta_boxes'), PHP_INT_MAX);

		$pages = array(
			'post',
			'post-new',
		);
		if (in_array(self::$page_base, $pages))
			add_action('do_meta_boxes', array($this, 'remove_meta_boxes'), PHP_INT_MAX);

		if ('plugins' === self::$page_base) {
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'add_settings_link'));
			add_action('in_plugin_update_message-'.basename(dirname(__FILE__)).'/'.basename(__FILE__), array($this, 'update_message'), 10, 2);
		}

		if ('users' === self::$page_base) {
			add_action('admin_print_scripts-users_page_im8-box-hide', array($this, 'enqueue_scripts'));
			add_filter('contextual_help', array($this, 'contextual_help'), 10, 2);
		}

		if ('options' === self::$page_base)
			add_action('admin_init', array($this, 'register_setting'));
	} // function init


	/**
	 * Check for and perform necessary updates.
	 *
	 * @hook	admin_init
	 * @return	void
	 */
	public function autoupdate() {
		$options = $this->get_option();
		$update_successful = true;

		if (version_compare($options['version'], '2.0', '<')) {
			$option_name_before_2_0 = 'hide';
			$hide = get_option($option_name_before_2_0);

			$new_options = array();
			$new_options['version'] = '2.0';

			if (false !== $hide && '' !== $hide)
				$new_options['hide'] = $hide;

			if (update_option($this->option_name, $new_options)) {
				$options = $new_options;
				$update_successful &= delete_option($option_name_before_2_0);
			}
			unset($new_options);
		}

		if ($update_successful) {
			$options['version'] = $this->version;
			update_option($this->option_name, $options);
		}
	} // function autoupdate


	/**
	 * Wrapper for get_option().
	 *
	 * @param	string $key Option name.
	 * @param	mixed $default Return value for missing key.
	 * @return	mixed|$default Option value.
	 */
	protected function get_option($key = null, $default = false) {
		static $option = null;
		if (null === $option) {
			$option = get_option($this->option_name, false);
			if (false === $option)
				$option = array(
					'version' => 0,
				);
		}

		if (null === $key)
			return $option;

		if (! isset($option[$key]))
			return $default;

		return $option[$key];
	} // function get_option


	/**
	 * Add custom settings page to user settings.
	 *
	 * @hook	admin_menu
	 * @return	void
	 */
	public function add_settings_page() {
  		$this->settings_page = add_users_page('IM8 Box Hide', 'IM8 Box Hide', 'edit_users', $this->settings_page_name, array($this, 'print_settings_page'));
	} // function add_settings_page


	/**
	 * Print settings page.
	 *
	 * @see		add_settings_page()
	 * @return	void
	 */
	public function print_settings_page() {
		global $wp_meta_boxes;

		if (count($post_types = $this->get_post_types('objects')))
			foreach ($post_types as $post_type) {
				if ($wp_meta_boxes[$post_type->name])
					foreach ($wp_meta_boxes[$post_type->name] as $context)
						foreach ($context as $priority)
							foreach ($priority as $box)
								$this->meta_boxes[$post_type->name][$box['id']] = $box['title'];
			}

		$this->load_textdomain();
		?>
		<div class="wrap">
			<h2>IM8 Box Hide</h2>
			<div class="tool-box">
				<form method="post" action="<?php echo admin_url('options.php'); ?>">
					<?php
					settings_fields($this->option_name);
					if (count($post_types))
						foreach ($post_types as $post_type)
							$this->generate_table($post_type->label, $post_type->name);
					?>
					<div class="submit">
						<input type="hidden" name="<?php echo $this->option_name; ?>[im8-box-hide-active]" value="true" />
						<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
					</div>
				</form>
			</div>
		</div>
		<?php
		$this->unload_textdomain();
	} // function print_settings_page


	/**
	 * Get relevant post types.
	 *
	 * @see		print_settings_page()
	 * @param	string $output Type of output, either 'names' or 'objects'
	 * @return	array Post types.
	 */
	protected function get_post_types($output='names') {
		$args = array(
			'show_ui' => true,
		);
		if (! is_array($post_types = get_post_types($args, $output)))
			return array();

		unset($post_types['attachment']);

		return $post_types;
	} // function get_post_types


	/**
	 * Get meta boxes for current post type.
	 *
	 * @hook	do_meta_boxes
	 * @param	string $post_type Current post type
	 * @return	JSON response
	 */
	public function get_meta_boxes($post_type) {
		if (
			'im8-box-hide' == filter_input(INPUT_POST, 'action')
			&& count($post_types = $this->get_post_types())
			&& in_array($post_type, $post_types)
		) {
			check_ajax_referer($this->nonce);
			$meta_boxes = $GLOBALS['wp_meta_boxes'];
			$meta_boxes = isset($meta_boxes[$post_type]) ? $meta_boxes[$post_type] : array();

			$metaBoxes = array();

			if (! empty($meta_boxes))
				foreach ($meta_boxes as $k_context => $v_context)
					foreach ($v_context as $k_priority => $v_priority)
						foreach ($v_priority as $k_box => $v_box)
							if (isset($v_box['id']) && isset($v_box['title']))
								$metaBoxes[] = array(
									$v_box['id'],
									$v_box['title']
								);

			if (post_type_supports($post_type, 'revisions'))
				$metaBoxes[] = array(
					'revisionsdiv',
					__('Revisions')
				);

			if (post_type_supports($post_type, 'comments'))
				$metaBoxes[] = array(
					'commentsdiv',
					__('Comments')
				);

			wp_send_json(array(
				'metaBoxes' => $metaBoxes,
				'postType' => $post_type,
			));
		}
	} // function get_meta_boxes


	/**
	 * Generate table for settings page.
	 *
	 * @see		print_settings_page()
	 * @param	string $headline Settings section heading.
	 * @param	string $post_type Post type name.
	 * @return	void
	 */
	protected function generate_table($headline, $post_type) {
		global $wp_roles;
		?>
		<h3 class="title"><?php echo $headline; ?></h3>
		<table id="im8-box-hide-<?php echo $post_type; ?>" class="widefat">
			<thead>
				<tr>
					<th><img src="<?php echo includes_url('/images/wpspin.gif'); ?>" class="loading" alt="" /></th>
					<th></th>
					<?php
					foreach ($wp_roles->role_names as $role => $name) {
						?>
						<th class="num">
							<?php echo translate_user_role($name); ?>
							<br />
							<img id="<?php echo $role; ?>__<?php echo $post_type; ?>" class="js_btn" src="<?php echo plugin_dir_url(__FILE__); ?>icons/boxes.png" title="<?php _e('Alle Boxen für diese Gruppe an/aus', 'im8-box-hide'); ?>" alt="<?php _e('Alle Boxen für diese Gruppe an/aus', 'im8-box-hide'); ?>" />
						</th>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<?php
	} // function generate_table


	/**
	 * Remove meta boxes according to saved settings.
	 *
	 * @hook	do_meta_boxes
	 * @return	void
	 */
	public function remove_meta_boxes() {
		$hide = $this->get_option('hide');

		$user_role = wp_get_current_user()->roles[0];

		if (isset($hide[$user_role]))
			foreach ($hide[$user_role] as $post_type => $meta_box)
				foreach ($meta_box as $id => $value)
					foreach ($this->contexts as $context)
						foreach ($this->priorities as $priority)
							if (isset($GLOBALS['wp_meta_boxes'][$post_type][$context][$priority][$id]))
								remove_meta_box($id, $post_type, $context);
	} // function remove_meta_boxes


	/**
	 * Add settings link to the plugin list.
	 *
	 * @hook	plugin_action_links_{$file}
	 * @param	array $links Already existing links.
	 * @return	array
	 */
	public function add_settings_link($links) {
		$settings_link = array(
			'<a href="'.admin_url('users.php?page='.$this->settings_page_name).'">'.__("Settings").'</a>'
		);

		return array_merge($settings_link, $links);
	} // function add_settings_link


	/**
	 * Print update message based on current plugin version's readme file.
	 *
	 * @hook	in_plugin_update_message-{$file}
	 * @param	array $plugin_data Plugin metadata.
	 * @param	array $r Metadata about the available plugin update.
	 * @return	void
	 */
	public function update_message($plugin_data, $r) {
		if ($plugin_data['update']) {
			$readme = wp_remote_fopen('http://plugins.svn.wordpress.org/'.$this->repository.'/trunk/readme.txt');
			if (! $readme)
				return;

			$pattern = '/==\s*Changelog\s*==(.*)=\s*'.preg_quote($this->version).'\s*=/s';
			if (
				false === preg_match($pattern, $readme, $matches)
				|| ! isset($matches[1])
			)
				return;

			$changelog = (array) preg_split('/[\r\n]+/', trim($matches[1]));
			if (empty($changelog))
				return;

			$output = '<div style="margin: 8px 0 0 26px;">';
			$output .= '<ul style="margin-left: 14px; line-height: 1.5; list-style: disc outside none;">';

			$item_pattern = '/^\s*\*\s*/';
			foreach ($changelog as $line)
				if (preg_match($item_pattern, $line))
					$output .= '<li>'.preg_replace('/`([^`]*)`/', '<code>$1</code>', htmlspecialchars(preg_replace($item_pattern, '', trim($line)))).'</li>';

			$output .= '</ul>';
			$output .= '</div>';

			echo $output;
		}
	} // function update_message


	/**
	 * Enqueue necessary script files.
	 *
	 * @hook	admin_print_scripts-users_page_im8-box-hide
	 * @return	void
	 */
	public function enqueue_scripts() {
		$this->load_textdomain();
		$file = 'css/im8-box-hide.css';
		wp_enqueue_style('im8_box_hide', plugin_dir_url(__FILE__).$file, array(), filemtime(plugin_dir_path(__FILE__).$file));
		$roles = array();
		foreach ($GLOBALS['wp_roles']->roles as $role => $v)
			$roles[] = $role;
		$data = array(
			'groupIcon' => plugin_dir_url(__FILE__).'icons/groups.png',
			'groupToggle' => __('Box für alle Gruppen an/aus', 'im8-box-hide'),
			'hide' => $this->get_option('hide'),
			'nonce' => wp_create_nonce($this->nonce),
			'optionName' => $this->option_name,
			'postTypes' => array_values($this->get_post_types()),
			'postURL' => admin_url('post-new.php', 'relative'),
			'poweredBy' => __('<b>IM8 Box Hide</b> is powered by <b>intermedi8</b>', 'im8-box-hide'),
			'roles' => $roles,
		);
		$handle = 'im8-box-hide-js';
		$file = 'js/im8-box-hide.js';
		wp_enqueue_script($handle, plugin_dir_url(__FILE__).$file, array('jquery'), filemtime(plugin_dir_path(__FILE__).$file), true);
		wp_localize_script($handle, 'localizedData', $data);
		$this->unload_textdomain();
	} // function enqueue_scripts


	/**
	 * Register plugin's contextual help.
	 *
	 * @hook	contextual_help
	 * @param	string $help Contextual help.
	 * @param	mixed Screen reference (object or ID).
	 * @return	string Contextual help.
	 */
	public function contextual_help($help, $screen) {
		if (is_object($screen))
			$screen = $screen->id;

		if ($this->settings_page === $screen) {
			$this->load_textdomain();
			$help  = '<h5>'.__('Anleitung', 'im8-box-hide').'</h5>';
			$help .= '<div class="metabox-prefs">';
			$help .= __('<p>Hier kannst du Metaboxen für bestimmte Benutzergruppen deaktivieren. <b>Eine eingeschaltete Checkbox bedeutet, dass diese Box ausgeblendet wird</b>.</p>
			<p>Die Icons neben den Boxen bzw. unterhalb der Benutzergruppen schalten den Status jeweils für die entsprechende Zeile bzw. Spalte ein oder aus.</p>', 'im8-box-hide');
			$help .= '</div>'."\n";
			$help .= '<h5>'.__('Hilfe & Infos', 'im8-box-hide').'</h5>';
			$help .= '<div class="metabox-prefs">';
			$help .= '<a href="http://intermedi8.de" target="_blank">'.__('Besuche unsere Website', 'im8-box-hide').'</a>';
			$help .= '</div>'."\n";
			$this->unload_textdomain();
		}

		return $help;
	} // function contextual_help


	/**
	 * Register setting for custom options page.
	 *
	 * @hook	admin_init
	 * @return	void
	 */
	public function register_setting() {
		register_setting($this->option_name, $this->option_name, array($this, 'save_setting'));
	} // function register_setting


	/**
	 * Prepare option values before they are saved.
	 *
	 * @param	array $data Original option values.
	 * @return	array Sanitized option values.
	 */
	public function save_setting($data) {
		$sanitized_data = $this->get_option();
		if (isset($data) && ! empty($data))
			$sanitized_data['hide'] = $data;
		else
			unset($sanitized_data['hide']);

		return $sanitized_data;
	} // function save_setting


	/**
	 * Load plugin textdomain.
	 *
	 * @return	boolean
	 */
	protected function load_textdomain() {
		return load_plugin_textdomain($this->textdomain, false, plugin_basename(dirname(__FILE__)).'/languages');
	} // function load_textdomain


	/**
	 * Remove translations from memory.
	 *
	 * @return	void
	 */
	protected function unload_textdomain() {
		unset($GLOBALS['l10n'][$this->textdomain]);
	} // function unload_textdomain


	/**
	 * Delete plugin data on uninstall.
	 *
	 * @hook	uninstall
	 * @return	void
	 */
	public static function uninstall() {
		delete_option(self::get_instance()->option_name);
	} // function uninstall

} // class IM8BoxHide


add_action('plugins_loaded', array(IM8BoxHide::get_instance(), 'init_on_demand'));


endif; // if (! class_exists('IM8BoxHide'))