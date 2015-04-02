<?php
/**
 * Add an options page. We want to define a template parts directory and a
 * file prefix that identifies template parts as such.
 */

class WDS_Page_Builder_Options {

	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'wds_page_builder_options';

	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'wds_page_builder_option_metabox';

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Page Builder Options', 'wds-simple-page-builder' );

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_init', array( $this, 'add_options_page_metabox' ) );
	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {

	}


	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page( 'options-general.php', $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2_options_page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	function add_options_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );

		// Set our CMB2 fields

		$cmb->add_field( array(
			'name' => __( 'Template Parts Directory', 'wds-simple-page-builder' ),
			'desc' => __( 'Where the template parts are located in the theme. Default is /parts', 'wds-simple-page-builder' ),
			'id'   => 'parts_dir',
			'type' => 'text_small',
			'default' => 'parts',
		) );

		$cmb->add_field( array(
			'name' => __( 'Template Parts Prefix', 'wds-simple-page-builder' ),
			'desc' => __( 'File prefix that identifies template parts. Default is part-', 'wds-simple-page-builder' ),
			'id'   => 'parts_prefix',
			'type' => 'text_small',
			'default' => 'part',
		) );

	}

	/**
	 * Public getter method for retrieving protected/private variables
	 * @since  0.1.0
	 * @param  string  $field Field to retrieve
	 * @return mixed          Field value or exception is thrown
	 */
	public function __get( $field ) {
		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

/**
 * Helper function to get/return the WDS_Page_Builder_Options object
 * @since  0.1.0
 * @return WDS_Page_Builder_Options object
 */
function WDS_Page_Builder_Options() {
	static $object = null;
	if ( is_null( $object ) ) {
		$object = new WDS_Page_Builder_Options();
	}

	return $object;
}

/**
 * Wrapper function around cmb2_get_option
 * @since  0.1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function wds_page_builder_get_option( $key = '' ) {
	return cmb2_get_option( WDS_Page_Builder_Options()->key, $key );
}

/**
 * Helper function to get the template part prefix
 */
function wds_template_part_prefix() {
	$prefix = ( wds_page_builder_get_option( 'parts_prefix' ) ) ? wds_page_builder_get_option( 'parts_prefix' ) : 'part';
	return $prefix;
}

/**
 * Helper function to return the template parts directory
 */
function wds_template_parts_dir() {
	$directory = ( wds_page_builder_get_option( 'parts_dir' ) ) ? wds_page_builder_get_option( 'parts_dir' ) : 'parts';
	return $directory;
}

/**
 * Get a list of the template parts in the current theme, return them
 * in an array.
 *
 * @return array An array of template parts
 */
function wds_page_builder_get_parts() {
	$parts        = array();
	$parts_dir    = ( wds_page_builder_get_option( 'parts_dir' ) ) ? trailingslashit( get_stylesheet_directory() ) .wds_page_builder_get_option( 'parts_dir' ) : get_stylesheet_directory() . '/parts';
	$parts_prefix = ( wds_page_builder_get_option( 'parts_prefix' ) ) ? wds_page_builder_get_option( 'parts_prefix' ) : 'part';

	// add a generic 'none' option
	$parts['none'] = __( '- No Template Parts -', 'wds-simple-page-builder' );

	foreach( glob( $parts_dir . '/' . $parts_prefix . '-*.php' ) as $part ) {
		$part_slug = str_replace( array( $parts_dir . '/' . $parts_prefix . '-', '.php' ), '', $part );
		$parts[$part_slug] = ucwords( str_replace( '-', ' ', $part_slug ) );
	}

	if ( empty( $parts ) ) {
		return __( 'No template parts found', 'wds-simple-page-builder' );
	}

	return $parts;
}

// Get it started
WDS_Page_Builder_Options();
