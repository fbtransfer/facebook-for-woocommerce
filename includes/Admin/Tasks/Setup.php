<?php
/**
 * Set up Facebook task.
 *
 * Adds a set up facebook task to the task list.
 */

namespace SkyVerge\WooCommerce\Facebook\Admin\Tasks;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists;
use Automattic\WooCommerce\Admin\Features\OnboardingTasks\Task;

/**
 * Setup Task class.
 */
class Setup extends Task {
    
    /**
	 * Get the ID of the task.
	 *
	 * @return string
	 */
    public function get_id() {
		return 'setup-facebook';
	}
    
    /**
	 * Get the title for the task.
	 *
	 * @return string
	 */
    public function get_title() {
        return esc_html__( 'Set up Facebook for WooCommerce', 'facebook-for-woocommerce' );
    } 

    /**
	 * Get the content for the task.
	 *
	 * @return string
	 */
    public function get_content() {
		return esc_html__( 'Sync your products and reach customers across Facebook, Instagram, Messenger and WhatsApp.', 'facebook-for-woocommerce' );
	}

    /**
	 * Get the time required to perform the task.
	 *
	 * @return string
	 */
    public function get_time() {
		return esc_html__( '5 minutes', 'facebook-for-woocommerce');
	}

    /**
	 * Get the action URL for the task.
	 *
	 * @return string
	 */
    public function get_action_url() {
        return \facebook_for_woocommerce()->get_settings_url();
    }

    /**
	 * Check if the task is complete.
	 *
	 * @return bool
	 */
    public function is_complete() {
        return \facebook_for_woocommerce()->get_connection_handler()->is_connected();
    }
}