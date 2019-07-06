<?php
/**
 * Class SampleTest
 *
 * @package Contributor_Rtcamp_Plugin
 */

/**
 * Sample test case.
 */
class Test_Contributors_Display extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	public function test_construct() {
		// Replace this with some actual testing code.
		$display_contributors = new Contributors_Display();

		$content_hooked      = has_action( 'the_content', [ $display_contributors, 'contributors_list' ] );
		$wp_enqueue_hooked   = has_action( 'wp_enqueue_scripts', [ $display_contributors, 'contributors_list_styles' ] );
		$query_action_hooked = has_action( 'pre_get_posts', [ $display_contributors, 'contributors_archive_meta_query' ] );

		$actions_registered = ( 10 === $content_hooked && 10 === $wp_enqueue_hooked && 1 === $query_action_hooked ) ? 'registered' : 'not registered';

		$this->assertTrue( 'registered' === $actions_registered );
	}

	/**
	 * Test for Display Contributor Box at the end of the post.
	 */
	public function test_contributors_list() {
		global $wp_query;

		// Set the $content value to a dummy content and initialize the class'WPCO_Filter_Post_Content'.
		$content              = 'Test Content Swap';
		$display_contributors = new Contributors_Display();

		// Create a dummy post using the 'WP_UnitTest_Factory_For_Post' class.
		$post_id = $this->factory->post->create(
			[
				'post_status'  => 'publish',
				'post_title'   => 'Test 1',
				'post_content' => 'Test Content',
			]
		);

		// Create two Dummy user ids.
		$user_ids = $this->factory->user->create_many( 2 );

		// Call the update_post_meta to store the array of two user ids created above into 'wpco_post_contributor_ids' post meta key.
		update_post_meta( $post_id, 'rtcamp_contributors_list', $user_ids );

		// Reset the $wp_query global post variable and create a new WP Query.
		$wp_query = new WP_Query(
			[
				'post__in'       => [ $post_id ],
				'posts_per_page' => 1,
			]
		);

		// Run the WordPress loop through this query and call our wpco_display_contributors() to add the $content to each post content.
		if ( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();

				$wp_query->is_single = true;

				$filtered_output = $display_contributors->contributors_list( $content );

				/**
				 * Check if the 'wpco_avatar-username' ( which a classname we used while creating the content )
				 * is present in the $filtered_output returned by the above function.
				 * If the strpos() returns a position, which means our content was added, in which case our test is successful.
				 */
				$string_found = strpos( $filtered_output, 'rtcamp_contributors' );
				$this->assertTrue( false !== $string_found );

			}
		}
	}

	/**
	 * Test for contributors_list_styles()
	 */
	public function test_contributors_list_styles() {
		$enqueue_style = new Contributors_Display();
		$enqueue_style->contributors_list_styles();

		// Check if the stylesheet is enqueued, wp_style_is will return true if its enqueued.
		$enqueued_post_meta_css = wp_style_is( 'rtcamp_contributors_css' );

		$this->assertTrue( $enqueued_post_meta_css );
	}
}
