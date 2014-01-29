<?php

namespace Jacere;

interface ICodeHandler {
	public function Handle($code);
}

class SyntaxHighlighter {
	
	const REGEX_COMMENT_C89 = '|/\*.*?\*/|s'; // multi-line comment
	const REGEX_COMMENT_C99 = '|//.*$|m'; // single-line comment (C++ style)
	
	const REGEX_STRING_DOUBLE = '/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/s'; // double-quoted string
	const REGEX_STRING_SINGLE = "/'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/s"; // single-quoted string
	const REGEX_STRING_QUOTES = '/([\'"])(?:(?!(\1|[\\\\])).)*(?:\\\\.(?:(?!(\1|[\\\\])).)*)*\1/sx';
	
	private static $c_handlers;

	public static function Execute($code, $handlers) {
		if (self::$c_handlers === NULL) {
			self::$c_handlers = [
				'xml'    => 'XMLCodeHandler',
				'html'   => 'XMLCodeHandler',
				'skhema' => 'SkhemaCodeHandler',
				'php'    => 'PHPCodeHandler',
				'phps'   => 'PHPSerializedCodeHandler',
				'csharp' => 'CSharpCodeHandler',
			];
		}
		
		if (is_array($handlers)) {
			foreach ($handlers as $name => $options) {
				if (isset(self::$c_handlers[$name])) {
					$class_name = self::$c_handlers[$name];
					$class_name_qualified = sprintf('Jacere\%s', $class_name);
					$file_path = sprintf('%s/handlers/%s.php', __DIR__, $class_name);
					if (file_exists($file_path)) {
						require_once($file_path);
						$instance = new $class_name_qualified();
						$code = $instance->Handle($code, $options);
					}
				}
			}
		}
		return $code;
	}
}

?>