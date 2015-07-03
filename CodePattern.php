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

class BasicCodePattern extends CodePattern {
	
	public function __construct($patterns, $class = NULL, $handlers = NULL) {
		if ($class === NULL && $handlers === NULL) {
			throw new \Exception(sprintf('ArgumentException %s::%s[class,handlers]', get_class(), __METHOD__));
		}
		if ($class !== NULL && !is_string($class)) {
			throw new \Exception(sprintf('ArgumentException %s::%s[class]', get_class(), __METHOD__));
		}
		parent::__construct($patterns, NULL, $class, $handlers);
	}
}

class DelimitedCodePattern extends CodePattern {
	
	public function __construct($patterns, $classes = NULL, $handlers = NULL) {
		$components = [
			'start',
			'value',
			'end',
		];
		parent::__construct($patterns, $components, $classes, $handlers);
	}
}

?>