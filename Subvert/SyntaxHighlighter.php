<?php

namespace Jacere;

require_once(__dir__.'/ReplacementManager2.php');

class SyntaxHighlighter {
	
	// these will be incorrect if "//" or "/*" is inside a string
	const REGEX_COMMENT_C89 = '|/\*.*?\*/|s'; // multi-line comment
	const REGEX_COMMENT_C99 = '|//.*$|m'; // single-line comment (C++ style)
	
	const REGEX_STRING_DOUBLE = '/(?<start>")(?<value>[^"\\\\]*(?:\\\\.[^"\\\\]*)*)(?<end>")/s'; // double-quoted string
	const REGEX_STRING_SINGLE = "/(?<start>')(?<value>[^'\\\\]*(?:\\\\.[^'\\\\]*)*)(?<end>')/s"; // single-quoted string
	
	const REGEX_STRING_QUOTES = '/(?<start>[\'"])(?<value>(?:(?!(\1|[\\\\])).)*(?:\\\\.(?:(?!(\1|[\\\\])).)*)*)(?<end>\1)/sx';
	
	private static $c_handlers;
	private static $c_instances;

	public static function Execute($code, $handlers) {
		if (self::$c_handlers === NULL) {
			self::$c_handlers = [
				'xml'    => 'XMLCodeHandler',
				'html'   => 'XMLCodeHandler',
				'skhema' => 'SkhemaCodeHandler',
				'php'    => 'PHPCodeHandler',
				'phps'   => 'PHPSerializedCodeHandler',
				'csharp' => 'CSharpCodeHandler',
				'sql'    => 'SQLCodeHandler',
			];
			self::$c_instances = [];
		}
		
		if (is_array($handlers)) {
			foreach ($handlers as $name => $options) {
				if (isset(self::$c_handlers[$name])) {
					if (isset(self::$c_instances[$name])) {
						$instance = self::$c_instances[$name];
					}
					else {
						$class_name = self::$c_handlers[$name];
						$class_name_qualified = sprintf('Jacere\%s', $class_name);
						$file_path = sprintf('%s/handlers/%s.php', __DIR__, $class_name);
						if (file_exists($file_path)) {
							require_once($file_path);
							$instance = new $class_name_qualified();
							self::$c_instances[$name] = $instance;
						}
					}
					if (!isset($options['__probe']) || $instance->Probe($code)) {
						$code = $instance->Handle($code, $options);
					}
				}
			}
		}
		return $code;
	}
}

?>