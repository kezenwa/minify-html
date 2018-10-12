<?php
/*
Plugin Name: Minify HTML
Plugin URI: https://www.littlebizzy.com/plugins/minify-html
Description: Tactfully minifies HTML output and markup to remove line breaks, whitespace, comments, and other code bloat to cleanup source code and improve speed.
Version: 1.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Prefix: MNFHTM
*/

// Plugin namespace
namespace LittleBizzy\MinifyHTML;

// Aliased namespaces
use LittleBizzy\MinifyHTML\Notices;

// Block direct calls
if (!function_exists('add_action')) {
	die;
}

// Plugin constants
const FILE = __FILE__;
const PREFIX = 'mnfhtm';
const VERSION = '1.0.0';

// Loader
require_once dirname(FILE).'/helpers/loader.php';

// Admin Notices
Notices\Admin_Notices::instance(__FILE__);

/**
 * Admin Notices Multisite check
 * Uncomment "return;" to disable this plugin on Multisite installs
 */
if (false !== Notices\Admin_Notices_MS::instance(__FILE__)) { /* return; */ }

// Run the main class
Helpers\Runner::start('Core\Core', 'instance');