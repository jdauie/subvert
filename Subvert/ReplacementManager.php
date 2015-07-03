<?php

namespace Jacere\Subvert;

class ReplacementManager {
	
	private $m_map;
	private $m_replacements;
	private $m_index;
	
	public function __construct() {
		$this->m_map = [];
		$this->m_replacements = [];
		$this->m_index = 0;
	}
	
	public function Replace($text) {
		$text = strtr($text, $this->m_replacements);
		$this->m_replacements = [];
		return $text;
	}
	
	public function Reconstitute($text) {
		$text = strtr($text, $this->m_map);
		return $text;
	}
	
	public function Add($str, $replacement, $prefix = NULL) {
		$code = '';
		if ($replacement !== '') {
			$code = "\x1A".'$'.($prefix ? sprintf('_%s_', $prefix) : '').$this->m_index;
			$this->m_map[$code] = $replacement;
			$this->m_index++;
		}
		$this->m_replacements[$str] = $code;
	}
}

?>