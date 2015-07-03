<?php

namespace Jacere\Subvert;

abstract class BaseCodeHandler {
	
	private $m_patterns;
	
	public function Handle($code, $options = NULL) {
		
		if ($this->m_patterns === NULL) {
			$this->m_patterns = $this->GetPatterns();
		}
		
		$rm = new ReplacementManager2();
		$code = $rm->ProcessPatterns($this->m_patterns, $code);
		return $code;
	}
	
	public function Probe($code) {
		return true;
	}

	public abstract function GetPatterns();
}

?>