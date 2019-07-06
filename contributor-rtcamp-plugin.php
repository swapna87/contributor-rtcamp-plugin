<?php
/**
 * Plugin Name: Contributors rtcamp Plugin
 * Description: This plugin allows you to display more than one author-name on a post.
 * Version:     1.0
 * Author: Swapnita Jadhav
 * License:     GPLv2
 *
 * @package Rtcamp Contributors
 */

/* Prevent Direct Access to File (Only WordPress Can Access) */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/* Include Admin Panel File */
require_once 'includes/class-contributor-add-meta-box.php';

/* Include Frontend View File */
require_once 'includes/class-contributors-display.php';
