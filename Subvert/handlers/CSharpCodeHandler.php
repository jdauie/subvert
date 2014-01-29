<?php

namespace Jacere;

class CSharpCodeHandler implements ICodeHandler {
	
	private $m_keywords;
	
	public function __construct() {
		// keywords can be escaped with '@', but I don't care
		// second line is context-dependent, but I don't care
		$this->m_keywords = [
			'abstract', 'as', 'base', 'bool', 'break', 'byte', 'case', 'catch', 'char', 'checked', 'class', 'const', 'continue', 'decimal', 'default', 'delegate', 'do', 'double', 'else', 'enum', 'event', 'explicit', 'extern', 'false', 'finally', 'fixed', 'float', 'for', 'foreach', 'goto', 'if', 'implicit', 'in', 'int', 'interface', 'internal', 'is', 'lock', 'long', 'namespace', 'new', 'null', 'object', 'operator', 'out', 'override', 'params', 'private', 'protected', 'public', 'readonly', 'ref', 'return', 'sbyte', 'sealed', 'short', 'sizeof', 'stackalloc', 'static', 'string', 'struct', 'switch', 'this', 'throw', 'true', 'try', 'typeof', 'uint', 'ulong', 'unchecked', 'unsafe', 'ushort', 'using', 'virtual', 'void', 'volatile', 'while',
			'add', 'alias', 'ascending', 'async', 'await', 'descending', 'dynamic', 'from', 'get', 'global', 'group', 'into', 'join', 'let', 'orderby', 'partial', 'remove', 'select', 'set', 'value', 'var', 'where', 'yield'
		];
	}
	
	public function Handle($code) {
		$rm = new ReplacementManager2();
		
		// comments
		$code = $rm->AddRegexMatchesBasic(
			[
				// this will be incorrect if "//" or "/*" is inside a string
				SyntaxHighlighter::REGEX_COMMENT_C99,
				SyntaxHighlighter::REGEX_COMMENT_C89,
			],
			'comment',
			$code
		);
		
		// strings/chars
		$code = $rm->AddRegexMatchesBasic(
			SyntaxHighlighter::REGEX_STRING_QUOTES,
			'php-str',
			$code
		);
		
		$code = $rm->AddRegexMatchesBasic(
			sprintf('/\b(%s)\b/', implode('|', $this->m_keywords)),
			'keyword',
			$code
		);
		
		$code = $rm->Reconstitute($code);
		
		return $code;
	}
}

?>