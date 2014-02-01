<?php

namespace Jacere;

require_once(__dir__.'/../BaseCodeHandler.php');

class PHPCodeHandler extends BaseCodeHandler {
	
	// single-quotes around nowdoc identifier
	//const REGEX_STRING_NOWDOC = "/(&lt;){3}'([a-z_][a-z0-9_]*+)'$(.*?)(^\\2;?$)/ims";
	// optional double-quotes around heredoc identifier
	//const REGEX_STRING_HEREDOC = '/(?<start>(&lt;){3}("?)([a-z_][a-z0-9_]*+)\3)$(?<value>.*?)^(?<end>\4;?)$/ims';
	const REGEX_STRING_HEREDOC = '/(?<start>(&lt;){3}(?<quote>["\']?)([a-z_][a-z0-9_]*+)\3)$(?<value>.*?)^(?<end>\4;?)$/ims';
	
	// parsing in double-quoted & heredoc strings
	// $name
	// $foo->foo
	// {$foo->bar[1]}
	// {$arr['foo'][3]}
	// {$const(DB_OBJ_TYPE_PAGE)} // this works, but it might be incorrect
	// {${$name}}
	// {${getName()}}
	
	
	
	private $m_keywords_pattern;
	
	public function __construct() {
		$keywords = [
			'break', 'clone', 'endswitch', 'final', 'global', 'include_once', 'private', 'return', 'try', 'xor', 'abstract', 'callable', 'const', 'do', 'enddeclare', 'endwhile', 'finally', 'goto', 'instanceof', 'namespace', 'protected', 'static', 'yield', 'and', 'case', 'continue', 'echo', 'endfor', 'for', 'if', 'insteadof', 'new', 'public', 'switch', 'use', 'catch', 'declare', 'else', 'endforeach', 'foreach', 'implements', 'interface', 'or', 'require', 'throw', 'var', 'as', 'class', 'default', 'elseif', 'endif', 'extends', 'function', 'include', 'print', 'require_once', 'trait', 'while',
			'__CLASS__', '__DIR__', '__FILE__', '__FUNCTION__', '__LINE__', '__METHOD__', '__NAMESPACE__', '__TRAIT__',
			'__halt_compiler', 'die', 'empty', 'list', 'unset', 'unset', 'eval', 'array', 'exit', 'isset'
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
			new DelimitedCodePattern(
				self::REGEX_STRING_HEREDOC,
				[
					'start' => 'skh-sym',
					'value' => 'php-str',
					'end'   => 'skh-sym',
				],
				[
					'value' => 'sql',
				]
			),
			new DelimitedCodePattern(
				[
					//self::REGEX_STRING_HEREDOC,
					SyntaxHighlighter::REGEX_STRING_QUOTES,
				],
				'php-str',
				[
					'value' => 'sql',
				]
			),
			new BasicCodePattern(
				'|\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*|',
				'php-var'
			),
			new BasicCodePattern(
				$this->m_keywords_pattern,
				'keyword'
			),
		];
	}
}

?>