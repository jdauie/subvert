<?php

namespace Jacere;

require_once(__dir__.'/SQLCodeHandler.php');

class PHPCodeHandler extends BaseCodeHandler {
	
	// single-quotes around nowdoc identifier
	const REGEX_STRING_NOWDOC = "/(&lt;){3}'([a-z_][a-z0-9_]*+)'$(.*?)(^\\2;?$)/ims";
	// optional double-quotes around heredoc identifier
	const REGEX_STRING_HEREDOC = '/(&lt;){3}("?)([a-z_][a-z0-9_]*+)\2$(.*?)(^\3;?$)/ims';
	// sprintf('%1$s%2$s%3$s%2$s%4$s%5$s', $match[1], $match[2], $match[3], $match[4], $match[5]);
	
	// parsing in double-quoted & heredoc strings
	// $name
	// $foo->foo
	// {$foo->bar[1]}
	// {$arr['foo'][3]}
	// {$const(DB_OBJ_TYPE_PAGE)} // this works, but it might be incorrect
	// {${$name}}
	// {${getName()}}
	
	
	
	private $m_keywords;
	
	public function __construct() {
		$this->m_keywords = [
			'break', 'clone', 'endswitch', 'final', 'global', 'include_once', 'private', 'return', 'try', 'xor', 'abstract', 'callable', 'const', 'do', 'enddeclare', 'endwhile', 'finally', 'goto', 'instanceof', 'namespace', 'protected', 'static', 'yield', 'and', 'case', 'continue', 'echo', 'endfor', 'for', 'if', 'insteadof', 'new', 'public', 'switch', 'use', 'catch', 'declare', 'else', 'endforeach', 'foreach', 'implements', 'interface', 'or', 'require', 'throw', 'var', 'as', 'class', 'default', 'elseif', 'endif', 'extends', 'function', 'include', 'print', 'require_once', 'trait', 'while',
			'__CLASS__', '__DIR__', '__FILE__', '__FUNCTION__', '__LINE__', '__METHOD__', '__NAMESPACE__', '__TRAIT__',
			'__halt_compiler', 'die', 'empty', 'list', 'unset', 'unset', 'eval', 'array', 'exit', 'isset'
		];
	}
	
	public function GetReplacements() {
		return [
			[
				'pattern' => [
					// this will be incorrect if "//" or "/*" is inside a string
					SyntaxHighlighter::REGEX_COMMENT_C99,
					SyntaxHighlighter::REGEX_COMMENT_C89,
				],
				'wrapper' => 'comment',
			],
			[
				'pattern' => [
					self::REGEX_STRING_NOWDOC,
					self::REGEX_STRING_HEREDOC,
					SyntaxHighlighter::REGEX_STRING_QUOTES,
				],
				'wrapper' => 'php-str',
				'handler' => [
					SQLCodeHandler::REGEX_PROBE => 'sql'
				]
			],
			[
				'pattern' => '|\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*|',
				'wrapper' => 'php-var',
			],
			[
				'pattern' => sprintf('/\b(%s)\b/', implode('|', $this->m_keywords)),
				'wrapper' => 'keyword',
			],
		];
	}
}

?>