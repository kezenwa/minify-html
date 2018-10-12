<?php

// Subpackage namespace
namespace LittleBizzy\MinifyHTML\Core;

// Aliased namespaces
use \LittleBizzy\MinifyHTML\Helpers;

/**
 * Core class
 *
 * @package Minify HTML
 * @subpackage Core
 */
final class Core extends Helpers\Singleton {



	/**
	 * Pseudo constructor
	 */
	protected function onConstruct() {

		// Check context
		if ($this->front()) {

			// Factory object
			$this->plugin->factory = new Factory($this->plugin);

			// WP loaded hook
			add_action('wp_loaded', [$this, 'loaded'], PHP_INT_MAX);
		}
	}



	/**
	 * Check front context
	 */
	private function front() {

		// Admin
		if (is_admin()) {
			return false;
		}

		// Avoid CRON requests
		if (defined('DOING_CRON') && DOING_CRON) {
			return false;
		}

		// XML-RPC request
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			return false;
		}

		// No WP-Cli allowed
		if (defined('WP_CLI') && WP_CLI) {
			return false;
		}

		// Allow
		return true;
	}



	/**
	 * Start buffering
	 */
	public function loaded() {
		$this->plugin->factory->buffer();
	}



}