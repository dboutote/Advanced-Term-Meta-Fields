<?php

// No direct access
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * WP Term Toolbox Main Class
 *
 * @version 1.0.0
 *
 * @since 0.1.0
 */

abstract class WP_Term_Toolbox {

	protected $version = '0.0.0';

	protected $db_version = 201601010001;

	protected $db_version_key = '';

	protected $meta_key = '';

	protected $no_meta_value = '&#8212;';

	protected $custom_column = '';

	protected $data_type = '';

	protected $taxonomies = array();

	protected $file = '';

	protected $url = '';

	protected $path = '';

	protected $basename = '';

	protected $labels = array(
		'singular'    => '',
		'plural'      => '',
		'description' => ''
	);


	abstract function set_labels();


	public function __construct( $file = '' )
	{
		$this->file           = $file;
		$this->url            = plugin_dir_url( $this->file );
		$this->path           = plugin_dir_path( $this->file );
		$this->basename       = plugin_basename( $this->file );
		$this->custom_column  = $this->get_custom_column_name();
		$this->taxonomies     = $this->get_taxonomies();
		$this->db_version_key = $this->get_db_version_key();
		$this->set_labels();
		#$this->register_meta();
	}

	
	public function register_meta() 
	{
		register_meta(
			'term',
			$this->meta_key,
			array( $this, 'sanitize_callback' ),
			array( $this, 'auth_callback'     )
		);
	}

	
	public function sanitize_callback( $data = '' ) 
	{
		return $data;
	}

public function auth_callback( $allowed = false, $meta_key = '', $post_id = 0, $user_id = 0, $cap = '', $caps = array() ) 
{
	if ( $meta_key !== $this->meta_key ) {
		return $allowed;
	}
	return $allowed;
}
	
	
	public function hook_into_terms( $taxonomies = array() )
	{
		if ( ! empty($taxonomies) ) :
			foreach ( $taxonomies as $tax_name ) {
				add_filter( "manage_edit-{$tax_name}_columns",          array( $this, 'add_column_header' ) );
				add_filter( "manage_{$tax_name}_custom_column",         array( $this, 'add_column_value'  ), 10, 3 );
				add_filter( "manage_edit-{$tax_name}_sortable_columns", array( $this, 'sortable_columns'  ) );

				add_action( "{$tax_name}_add_form_fields",  array( $this, 'add_form_field'  ) );
				add_action( "{$tax_name}_edit_form_fields", array( $this, 'edit_form_field' ) );
			}
		endif;
	}


	public function add_column_header( $columns = array() )
	{
		$columns[$this->custom_column] = $this->labels['singular'];

		return $columns;
	}


	public function add_column_value( $empty = '', $custom_column = '', $term_id = 0 )
	{
		if ( empty( $_REQUEST['taxonomy'] ) || ( $this->custom_column !== $custom_column ) || ! empty( $empty ) ) {
			return;
		}

		$return_value = $this->no_meta_value;
		$meta_value = $this->get_meta( $term_id );

		if ( ! empty( $meta_value ) ) {
			$return_value = $this->format_column_output( $meta_value );
		}

		echo $return_value;
	}


	public function sortable_columns( $columns = array() )
	{
		$columns[$this->meta_key] = $this->meta_key;

		return $columns;
	}


	public function get_meta( $term_id = 0 ) 
	{
		return get_term_meta( $term_id, $this->meta_key, true );
	}


	public function get_db_version_key()
	{
		return "wp_term_toolbox_{$this->data_type}s_version";
	}


	public function get_taxonomies( $args = array(), $meta_key = '' )
	{
		return WP_Term_Toolbox_Utils::get_taxonomies( $args = array(), $this->meta_key );
	}


	/**
	 * Build column name for meta field
	 *
	 * Note: Relying on $meta_key alone throws an error with 'dashicons-picker' script
	 */
	public function get_custom_column_name()
	{
		return $this->meta_key . '-col';
	}





}