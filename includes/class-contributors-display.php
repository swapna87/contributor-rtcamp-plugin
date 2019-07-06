<?php
/**
 * Class Contributors_Display
 *
 * @package Rtcamp Contributer.
 */

/**
 * Class for contributor display for posts
 */
class Contributors_Display {
	/**
	 * Contributors_Display constructor
	 */
	public function __construct() {
		// Add Filter to Display Contributor Box in Frontend.
		add_filter( 'the_content', array( $this, 'contributors_list' ) );

		// Enqueue stylesheet.
		add_action( 'wp_enqueue_scripts', array( $this, 'contributors_list_styles' ) );

		// Modify author archive query.
		add_action( 'pre_get_posts', array( $this, 'contributors_archive_meta_query' ), 1 );
	}

	/**
	 * Display Contributor Box at the end of the post.
	 *
	 * @param string $content Content of post.
	 */
	public function contributors_list( $content ) {
		if ( ! is_single() && ! is_author() && ! is_category() && ! is_tag() ) {
			return;
		}
		// Get Custom Fields for the Post.
		$post_custom = get_post_custom( get_the_ID() );

		// Explode the list of users to array for search purpose.
		$user_ids = explode( ',', $post_custom['rtcamp_contributors_list'][0] );

		// If the users are not set then return content to hide contributors box.
		if ( empty( $user_ids[0] ) ) {
			return $content;
		}

		$contributor_box = '
		<div class="rtcamp_contributors_box">
			<h5>Contributors</h5>
			';

		foreach ( $user_ids as $user ) {

			// Get User Data from User ID.
			$userdata = get_userdata( $user );

			// If user with given ID exists.
			if ( false !== $userdata ) {
				$contributor_box .= '<a href="' . get_author_posts_url( $user ) . '"><div class="rtcamp_contributors">' . get_avatar( $user, 40 ) . $userdata->display_name . '</div></a>';
			}
		}

		$contributor_box .= '</div>';
		return $content . $contributor_box;
	}

	/**
	 * Enqueue contributor style
	 */
	public function contributors_list_styles() {
		// Register Style Sheet.
		wp_register_style( 'rtcamp_contributors_css', plugins_url( 'contributor-rtcamp-plugin/css/rtcamp-style.css' ), '', '1.0.0', false );
		wp_enqueue_style( 'rtcamp_contributors_css' );
	}

	/**
	 * Change Query to List Post Where user is Contributor or Author
	 *
	 * @param object $query post query object.
	 */
	public function contributors_archive_meta_query( $query ) {

		// If it is author page.
		if ( $query->is_author ) {

			// Initialize global wpdb variable.
			global $wpdb;

			// Get author data.
			$author_data = get_user_by( 'slug', get_query_var( 'author_name' ) );

			// Get author id.
			$author_id = $author_data->ID;

			// Find IDs of posts where user is author or contributor.
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='rtcamp_contributors_list' AND FIND_IN_SET( %d , meta_value)", $author_id ) );

			// Initialize empty array to store IDs of posts.
			$post_ids = array();

			// Initialize count to 0.
			$cnt = 0;

			// Store post ids one by one.
			foreach ( $results as $row ) {
				array_push( $post_ids, $row->post_id );
				$cnt++;
			}

			// Change author name to blank to list all the posts from the database.
			$query->query_vars['author_name'] = '';

			if ( 0 === $cnt ) {
				// If there are no post.
				$query->query_vars['post__in'] = array( '' );
			} else {
				// List the specified post ids.
				$query->query_vars['post__in'] = $post_ids;
			}

			// Add action to change author archives title.
			add_filter( 'get_the_archive_title', array( $this, 'contributors_archive_meta_title' ) );
		}
	}

	/**
	 * Change author archive title.
	 *
	 * @param string $title post title.
	 */
	public function contributors_archive_meta_title( $title ) {
		// Initialize global wp_query variable.
		global $wp_query;

		// Get Author Info.
		$author_data = get_user_by( 'login', $wp_query->query['author_name'] );

		// Return the new archive page title.
		return 'Author/Contributor : ' . $author_data->display_name;
	}
}

new Contributors_Display();
