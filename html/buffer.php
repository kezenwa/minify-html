<?php

// Subpackage namespace
namespace LittleBizzy\MinifyHTML\Html;

// Aliased namespaces
use \LittleBizzy\MinifyHTML\Helpers;

/**
 * Buffer class
 *
 * @package Minify HTML
 * @subpackage HTML
 */
class Buffer extends Helpers\Singleton {



	/**
	 * Parsing arguments
	 */
	private $args;



	/**
	 * Start buffering
	 */
	public function start($args) {
		$this->args = $args;
		@ob_start([$this, 'output']);
	}



	/**
	 * Ouput buffer operations
	 * Strongly inspired in Minify HTML plugin
	 * https://wordpress.org/plugins/minify-html-markup/
	 */
	public function output($buffer) {

		// XML test
		$test = strtolower(substr(ltrim($buffer), 0, 5));
		if ($test == '<?xml') {
			return $buffer;
		}

	 	// Performs the HTML parsing
		$buffer = $this->plugin->factory->parser($this->args)->parse($buffer);

		// Done
		return $buffer;
	}



}