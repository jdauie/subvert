<?php

namespace Jacere\Subvert;

class CodePattern {
	
	private $m_patterns;
	private $m_components;
	private $m_classes;
	private $m_handlers;
	
	public function __construct($patterns, array $components = NULL, $classes = NULL, $handlers = NULL) {
		$this->m_patterns = is_string($patterns) ? [NULL => $patterns] : $patterns;
		$this->m_components = is_array($components) ? $components : [0];
		$this->m_classes = is_string($classes) ? [NULL => $classes] : $classes;
		$this->m_handlers = $handlers;
	}
	
	public function GetPatterns() {
		return $this->m_patterns;
	}
	
	public function GetComponents() {
		return $this->m_components;
	}
	
	public function GetClasses() {
		return $this->m_classes;
	}
	
	public function GetHandlers() {
		return $this->m_handlers;
	}
}
