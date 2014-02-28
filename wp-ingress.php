<?php
/*
Plugin Name:   WP-Ingress
Description:   Adds numerous Ingress-specific features to a Wordpress specifc
Version:       0.1
Author:        John Luetke
Author URI:    http://johnluetke.net
License:       GPLv3
GitHub Plugin URI: johnluetke/WP-Ingress
GitHub Branch: master
*/
if(!class_exists("WP_Ingress")) {

	define('INGRESS_OPTIONS', 'wp-ingress-options');
	define('INGRESS_OPTION_CYCLE_PAGE','cycle_page');

	define('INGRESS_CYCLE_REWRITE', 'index.php?pagename=cycle&cycle=$matches[2]');
	define('INGRESS_CYCLE_REGEX',   'cycle(/?([0-9]{4}\.[0-9]{1,2})?)$');

	define('INGRESS_CYCLE_PAGETITLE', 'Cycle');

	$wp_ingress_dir = dirname(__FILE__) . "/";
	$wp_ingress_uri = plugins_url() . "/" . basename($wp_ingress_dir) . "/";

	set_include_path(get_include_path() . PATH_SEPARATOR . $wp_ingress_dir . "lib");
	require("Ingress/Cycle/Cycle.class.php");

	class WP_Ingress {

		static $options = null;

		public static function activate() {
			global $wp_rewrite, $wp_ingress_uri;

			$cyclePageID = wp_insert_post(array(
				'post_title' => INGRESS_CYCLE_PAGETITLE,
				'post_name' => "cycle",
				'post_status' => "publish",
				'post_content' => '{INGRESS_CYCLE}',
				'post_type' => "page",
				'ping_status' => "closed"
			));

			self::saveOption(INGRESS_OPTION_CYCLE_PAGE, $cyclePageID);

			$wp_rewrite->add_rule(INGRESS_CYCLE_REGEX, INGRESS_CYCLE_REWRITE, 'top');
			$wp_rewrite->flush_rules();
		}

		/**
		 * Called when the plugin is deactivated.
		 *
		 * Should delete all rewrite rules, pages, and options created by activate
		 *
		 * @see WP-Ingress::activate()
		 */
		public static function deactivate() {
			wp_delete_post(self::getOption(INGRESS_OPTION_CYCLE_PAGE), true);

			delete_option(INGRESS_OPTIONS);
		}

		public static function getOption($optionName) {
			if (self::$options == null) {
				self::$options = get_option(INGRESS_OPTIONS);
			}

			return self::$options[$optionName];
		}

		public static function saveOption($option_name, $option_value) {
			$old = self::getOption($option_name);
			if ($old != $option_value) {
				self::$options[$option_name] = $option_value;
				update_option(INGRESS_OPTIONS, self::$options);
			}
		}

		public static function registerQueryVars($vars) {
			array_push($vars, 'cycle');
			return $vars;
		}

		public static function debug() {
			global $wp_rewrite;			
			echo "<!--";
			print_r(get_option("rewrite_rules"));
			echo "-->";
		}

		public static function pageTitle($title, $id = -1) {
			if (strpos($title, INGRESS_CYCLE_PAGETITLE) !== false) {
				$cycle = trim(get_query_var("cycle"));
				$cycle = empty($cycle) ? \Ingress\Cycle\Cycle::getCurrentCycle() : \Ingress\Cycle\Cycle::fromIdentifier($cycle);
				$title .= " " . $cycle->getName();
			}
			else {
			}

			return $title;
		}

		public static function pageContent($content) {
			if (preg_match("/\{INGRESS_CYCLE\}/", $content)) {
				$cycle = trim(get_query_var("cycle"));
				$cycle = empty($cycle) ? \Ingress\Cycle\Cycle::getCurrentCycle() : \Ingress\Cycle\Cycle::fromIdentifier($cycle);
				ob_start();
				require("page/cycle.php");
				$content = ob_get_contents();
				ob_end_clean();
			}
			return $content;
		}
	}

	register_activation_hook( __FILE__, array( 'WP_Ingress', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'WP_Ingress', 'deactivate' ) );

	//add_action('wp_loaded', array( 'Ingress', 'debug' ) );

	add_filter('query_vars', array('WP_Ingress', 'registerQueryVars'));
	add_filter('the_title', array('WP_Ingress', 'pageTitle'));
	add_filter('wp_title', array('WP_Ingress', 'pageTitle'));
	add_filter('the_content', array('WP_Ingress', 'pageContent'));

	wp_register_style("ingress", $wp_ingress_uri . "wp-ingress.css", array(), time());
	wp_enqueue_style("ingress");
}
?>
