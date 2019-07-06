<?php
/**
 * Class SampleTest
 *
 * @package Contributor_Rtcamp_Plugin
 */

/**
 * Sample test case.
 */
class Test_Contributor_Add_Meta_Box extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	public function test_construct() {
		$add_meta_box = new Contributor_Add_Meta_Box();

		// Check if both actions are registered.
		$meta_action_hooked = has_action( 'add_meta_boxes', [ $add_meta_box, 'contributors_metabox' ] );
		$post_action_hooked = has_action( 'save_post', [ $add_meta_box, 'save_contributors_meta' ] );

		$actions_registered = ( 10 === $meta_action_hooked && 10 === $post_action_hooked ) ? 'registered' : 'not registered';

		$this->assertTrue( 'registered' === $actions_registered );
	}

	/**
	 * Test function for adding meta boxes on add new post and edit post screens
	 */
	public function test_contributors_metabox() {
		global $wp_meta_boxes;

		$add_meta_box = new Contributor_Add_Meta_Box();
		$add_meta_box->contributors_metabox();

		// Check if the two meta boxes are added on default 'post' and custom post type 'book' screens.
		$add_post_screen_id = $wp_meta_boxes['post']['normal']['high']['contributors']['id'];

		$meta_boxes_added = ( 'contributors' === $add_post_screen_id );

		$this->assertTrue( $meta_boxes_added );
	}

	/**
	 * Test function for adding custom meta box html.
	 */
	public function test_contributors_meta_content() {
		global $wp_query;
		global $post;

		$add_meta_box = new Contributor_Add_Meta_Box();

		// Create two Dummy user ids.
		$user_ids = $this->factory->user->create_many( 2 );

		// Create a dummy post using the 'WP_UnitTest_Factory_For_Post' class and give the post author's user ud as 2.
		$post_id = $this->factory->post->create(
			[
				'post_status'  => 'publish',
				'post_title'   => 'Test 1',
				'post_content' => 'Test Content',
				'post_author'  => 2,
				'post_type'    => 'post',
			]
		);

		// Create a custom query for the post with the above created post id.
		$wp_query = new WP_Query(
			[
				'post__in'       => [ $post_id ],
				'posts_per_page' => 1,
			]
		);

		// Run the WordPress loop through this query to set the global $post.
		if ( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();
			}
		}

		// Set the array of user ids to post meta with meta key 'wpco_post_contributor_ids', with the above created post id.
		update_post_meta( $post_id, 'rtcamp_contributors_list', $user_ids );

		// Store the echoed value of the wpco_custom_box_html() into $custom_box_html using output buffering.
		ob_start();
		$add_meta_box->contributors_meta_content( $post );
		$custom_box_html = ob_get_clean();

		// Validate the output string contains the class names we are expecting.
		$author_string   = strpos( $custom_box_html, 'user-name' );
		$checkbox_string = strpos( $custom_box_html, 'user_checkboxes' );

		$custom_box_html_output = ( $author_string && $checkbox_string );

		$this->assertTrue( $custom_box_html_output );

		wp_reset_postdata();
	}
}
