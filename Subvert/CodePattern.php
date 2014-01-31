<?php

namespace Jacere;

class CodePattern {
	
	private $m_pattern;
	private $m_components;
	private $m_classes;
	private $m_handlers;
	
	public function __construct($pattern, array $components = NULL, $classes = NULL, $handlers = NULL) {
		$this->m_pattern = is_string($pattern) ? [$pattern] : $pattern;
		$this->m_components = $components;
		$this->m_classes = $classes;
		$this->m_handlers = $handlers;
	}
	
	public function Stuff() {
		
	}
}

?>