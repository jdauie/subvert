<?php

namespace Jacere;

require_once(__dir__.'/../BaseCodeHandler.php');

class CSharpCodeHandler extends BaseCodeHandler {
	
	private $m_keywords_pattern;
	
	public function __construct() {
		// keywords can be escaped with '@', but I don't care
		// second line is context-dependent, but I don't care
		$keywords = [
			'abstract', 'as', 'base', 'bool', 'break', 'byte', 'case', 'catch', 'char', 'checked', 'class', 'const', 'continue', 'decimal', 'default', 'delegate', 'do', 'double', 'else', 'enum', 'event', 'explicit', 'extern', 'false', 'finally', 'fixed', 'float', 'for', 'foreach', 'goto', 'if', 'implicit', 'in', 'int', 'interface', 'internal', 'is', 'lock', 'long', 'namespace', 'new', 'null', 'object', 'operator', 'out', 'override', 'params', 'private', 'protected', 'public', 'readonly', 'ref', 'return', 'sbyte', 'sealed', 'short', 'sizeof', 'stackalloc', 'static', 'string', 'struct', 'switch', 'this', 'throw', 'true', 'try', 'typeof', 'uint', 'ulong', 'unchecked', 'unsafe', 'ushort', 'using', 'virtual', 'void', 'volatile', 'while',
			'add', 'alias', 'ascending', 'async', 'await', 'descending', 'dynamic', 'from', 'get', 'global', 'group', 'into', 'join', 'let', 'orderby', 'partial', 'remove', 'select', 'set', 'value', 'var', 'where', 'yield'
		];
		
		$this->m_keywords_pattern = sprintf('/\b(%s)\b/', implode('|', $keywords));
	}
	
	public function GetPatterns() {
		return [
			new BasicCodePattern(
				[
					SyntaxHighlighter::REGEX_COMMENT_C99,
					SyntaxHighlighter::REGEX_COMMENT_C89,
				],
				'comment'
			),
			new BasicCodePattern(
				SyntaxHighlighter::REGEX_STRING_QUOTES,
				'php-str'
			),
			new BasicCodePattern(
				$this->m_keywords_pattern,
				'keyword'
			),
		];
	}
}

?>