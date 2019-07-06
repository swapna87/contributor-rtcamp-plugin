<?php
/**
 * Class Contributor_Add_Meta_Box.
 *
 * @package Rtcamp Contributer.
 */

/**
 * Class for contributor meta box
 */
class Contributor_Add_Meta_Box {
	/**
	 * Contributor_Add_Meta_Box constructor
	 */
	public function __construct() {
		// Add Action to Display Contributor Meta Box in Admin.
		add_action( 'add_meta_boxes', array( $this, 'contributors_metabox' ) );

		// Add Action to Save Contributor Meta Box.
		add_action( 'save_post', array( $this, 'save_contributors_meta' ) );
	}

	/**
	 * Add meta box to the post editor screen.
	 */
	public function contributors_metabox() {
		add_meta_box( 'contributors', 'Contributors', array( $this, 'contributors_meta_content' ), 'post', 'normal', 'high' );
	}

	/**
	 * Contributor Meta Box Render Function.
	 *
	 * @param {obj} $post Post variable.
	 */
	public function contributors_meta_content( $post ) {

		// Get Custom Fields for the Post.
		$post_custom = get_post_custom( $post->ID );

		// Explode the list of users to array for search purpose.
		$user_ids = explode( ',', $post_custom['rtcamp_contributors_list'][0] );

		// Get list of WordPress Users who are not subscribers.
		$blogusers = get_users(
			array(
				'orderby' => 'registered',
				'order'   => 'DESC',
				'who'     => 'authors',
			)
		);

		// Generate Nonce for Additonal Security as per WordPress Standards.
		wp_nonce_field( plugin_basename( __FILE__ ), 'rtcamp_contributors_nonce' );

		// Add Scrolling Capability.
		echo '<div style="overflow-y:scroll; height:150px">';

		foreach ( $blogusers as $user ) {

			// Initialize string to user login.
			$userdetails = $user->user_login;

			// If First Name of User is Available.
			if ( ! empty( $user->first_name ) ) {

				$userdetails = $userdetails . ' (' . $user->first_name;

				// If Last Name of User is Available.
				if ( ! empty( $user->last_name ) ) {
					$userdetails = $userdetails . ' ' . $user->last_name;
				}

				$userdetails = $userdetails . ')';
			}

			if ( wp_get_current_user()->ID === $user->ID ) {
				// For post author.
				echo '<input type="checkbox" checked disabled class="user_checkboxes"><span class="user-name">' . esc_html( $user->user_login ) . '</span><br/>';
			} elseif ( in_array( $user->ID, $user_ids ) ) {
				// If user checked as contributor.
				echo '<input type="checkbox" class="user_checkboxes" name="rtcamp_contributors_list[]" value="' . esc_html( $user->ID ) . '" checked><span class="user-name">' . esc_html( $userdetails ) . '</span><br/>';
			} else {
				echo '<input class="user_checkboxes" type="checkbox" name="rtcamp_contributors_list[]" value="' . esc_html( $user->ID ) . '"><span class="user-name">' . esc_html( $userdetails ) . '</span><br/>';
			}
		}

		echo '</div>';
	}

	/**
	 * Save Function for Contributor Meta Box.
	 *
	 * @param {obj} $post_id Post ID.
	 */
	public function save_contributors_meta( $post_id ) {

		/**
		 * When the post is saved or updated we get $_POST available.
		 * Check if the current user is authorised to do this action.
		 */
		if ( isset( $_POST['post_type'] ) && 'post' === $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Nonce Verification.
		if ( ! isset( $_POST['rtcamp_contributors_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rtcamp_contributors_nonce'] ) ), plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Check Permission for Current User.
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		// If Contributors are Selected.
		if ( isset( $_POST['rtcamp_contributors_list'] ) ) {
			$custom_meta = wp_get_current_user()->ID . ',' . implode( ',', array_map( 'esc_attr', wp_unslash( $_POST['rtcamp_contributors_list'] ) ) );
		} else {
			// By default add the user creating the post.
			$custom_meta = wp_get_current_user()->ID;
		}

		// Save the Contributor Meta Box data.
		update_post_meta( $post_id, 'rtcamp_contributors_list', $custom_meta );

	}
}

new Contributor_Add_Meta_Box();
