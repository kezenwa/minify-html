<?php

// Subpackage namespace
namespace LittleBizzy\MinifyHTML\Html;

/**
 * Parser class
 *
 * @package Minify HTML
 * @subpackage HTML
 */
class Parser {



	/**
	 * Input args
	 */
	private $args;



	/**
	 * Constants for tag delimitation
	 */
	const TAG_INI = '370748f2338fd94c291b227346333735';
	const TAG_END = '4c79af2f7f3a90cc0c7fed9fd42172d9';



	/**
	 * Default args
	 */
	private $defaults = [
		'utf8Support' 		=> true,
		'spacing'			=> false,
		'lineBreaks'		=> false,
		'comments'			=> false,
		'styles'			=> false,
		'stylesComments'	=> false,
		'scripts'			=> false,
		'scriptsComments'	=> false,
		'conditionals'		=> false,
		'selfClosing'		=> false,
	];



	/**
	 * Constructor
	 */
	public function __construct($args) {
		$this->args = array_merge($this->defaults, $args);
	}



	/**
	 * Parse HTML using the provided options
	 * Strongly inspired in Minify HTML plugin
	 * https://wordpress.org/plugins/minify-html-markup/
	 */
	public function parse($html) {


		/* Prepare input */

		// Get vars
		extract($this->args);

		// Check regexp pattern modifier
		$pm = $utf8Support? 'u' : '';

		// Evaluates self-closing tags
		if ($selfClosing) {
			$test = strtolower(substr(ltrim($html), 0, 15));
			$selfClosing = ($test == '<!doctype html>');
		}


		/* Early replacements */

		/*
		 * Removes conditional tags
		 */
		if ($conditionals) {
			$html = preg_replace('/<!--\[[^\]]*(?:](?!-->)[^\]]*)*]-->/U'.$pm, '', $html);
		}


		/* Transformations */

		// Sensitive tags
		$tags_src = [];
		$tags_new = [];
		$tags = ['style', 'script', 'textarea', 'pre'];

		// Prepare delimiters
		foreach ($tags as $tag) {

			// Tag
			$ini = '<'.$tag;
			$end = '/'.$tag.'>';

			// Source and new tag
			$tags_src[] = $ini;
			$tags_src[] = $end;
			$tags_new[] = self::TAG_INI.$ini;
			$tags_new[] = $end.self::TAG_END;
		}

		// Splits the content
		$parts = str_ireplace($tags_src, $tags_new, $html);
		$parts = explode(self::TAG_END, $parts);

		// Init output
		$minified = '';

		// Enum parts
		foreach ($parts as $part) {

			// Init
			$insideComments = false;

			// Find tag start
			$pos = stripos($part, self::TAG_INI);
			if (false === $pos) {
				$before = $part;
				$inside = '';

			/**
			 * Process sensitive tags
			 * Note: tags textarea and pre will remain intact (but pre tag could lose the html comments)
			 */
			} else {

				// Detect before and inside content
				$before = substr($part, 0, $pos);
				$inside = substr($part, $pos + 32);

				// Process styles
				if ('<style' == strtolower(substr($inside, 0, 6))) {

					// Check transformation
					if ($styles) {

						// Remove left, right and extra espaces with UTF8 support
						$inside = preg_replace(['/\>[^\S ]+/'.$pm, '/[^\S ]+\</'.$pm, '/\s+/'.$pm], ['>', '<', ' '], $inside);

						// Remove CSS comments
						if ($styleComments) {
							$inside = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $inside);
						}

						// Safe minification
						$inside = str_replace([chr(10), ' {', '{ ', ' }', '} ', '( ', ' )', ' :', ': ', ' ;', '; ', ' ,', ', ', ';}'],
											  ['', 		'{',  '{',  '}',  '}',  '(',  ')',  ':',  ':',  ';',  ';',  ',',  ',',  '}'], $inside);
					}

				// Process scripts
				} elseif ('<script' == strtolower(substr($inside, 0, 7))) {

					// Check transformation
					if ($scripts) {

						// Check closing tags
						$pos1 = strpos($inside, '>');
						$pos2 = strrpos($inside, '<');
						if ($pos1 && $pos2 && $pos2 > $pos1) {

							// Split in lines
							$code = trim(substr($inside, $pos1 + 1, $pos2 - $pos1 - 1));
							if ('' !== $code) {

								// Split in lines
								$code = str_replace(chr(13).chr(10), chr(10), $code);
								$code = explode(chr(10), $code);

								// Enumeration
								$lines = [];
								foreach ($code as $line) {

									// Check line
									$line = trim($line);
									if ('' === $line) {
										continue;
									}

									// Remove extra characters
									$line = preg_replace('/;+/', ';', $line);
									$line = preg_replace('/\s+/', ' ', $line);

									// Added
									$lines[] = trim($line);
								}

								// Minify it
								$code = implode('', $lines);

								/**
								 * Remove Javascript comments
								 * https://stackoverflow.com/questions/19509863/how-to-remove-js-comments-using-php
								 */
								if ($scriptsComments) {
									$pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|\")\/\/.*))/';
									$code = preg_replace($pattern, '', $code);
								}

								// Done?
								// Needs to remove line breaks before and after
								$inside = substr($inside, 0, $pos1 + 1).$code.substr($inside, $pos2);
							}
						}
					}

				// Process pre tag
				} elseif ('<pre' == strtolower(substr($inside, 0, 4))) {
					$insideComments = true;
				}
			}

			// Remove HTML comments
			if ($comments) {

				// Prepare regexp
				$pattern = '/<!--(?!<!)[^\[>].*?-->/s'.$pm;

				// Remove in before HTML
				$before = preg_replace($pattern, '', $before);

				// Check inside tag
				if ($insideComments) {
					$inside = preg_replace($pattern, '', $inside);
				}
			}

			// Removes self-closing markup for HTML5 documents
			if ($selfClosing) {
				$before = str_replace('/>', '>', $before);
			}

			// Remove line breaks
			if ($lineBreaks) {
				$before = str_replace(chr(13).chr(10), chr(10), $before);
				$before = str_replace(chr(10), '', $before);
			}

			// Remove tabs and extra spacing
			if ($spacing) {
				$before = preg_replace('/\x9/', ' ', $before);
				$before = preg_replace('/\x20+/', ' ', trim($before, ' \0\x0B'));
			}

			// Add chunk
			$minified .= $before.$inside;
		}

		// Done
		return $minified;
	}



}