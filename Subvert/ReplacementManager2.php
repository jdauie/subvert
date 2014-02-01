<?php

namespace Jacere;

class ReplacementManager2 {
	
	private $m_map;
	private $m_replacements;
	private $m_index;
	
	public function __construct() {
		$this->m_map = [];
		$this->m_replacements = [];
		$this->m_index = 0;
	}
	
	public function Reconstitute($text) {
		$text = strtr($text, $this->m_map);
		return $text;
	}
	
	public function AddReplacedValue($replacement) {
		if ($replacement === '') {
			return '';
		}
		if (isset($this->m_replacements[$replacement])) {
			return $this->m_replacements[$replacement];
		}
		
		$code = "\x1A".'$'.$this->m_index;
		$this->m_map[$code] = $replacement;
		$this->m_index++;
		$this->m_replacements[$replacement] = $code;
		
		return $code;
	}
	
	private function WrapCodeClass($text, $class) {
		// wrap the replacement pattern around each line
		$lines = explode("\n", $text);
		$output = [];
		foreach ($lines as $line) {
			$output[] = sprintf('<code class="%s">%s</code>', $class, $line);
		}
		return implode("\n", $output);
	}
	
	public function AddRegexMatchesBasic($pattern, $text) {
		
		$text = preg_replace_callback($pattern->GetPatterns(), function ($match) use ($pattern) {
			
			$classes = $pattern->GetClasses();
			$handlers = $pattern->GetHandlers();
			$output = '';
			
			// reassemble components
			foreach ($pattern->GetComponents() as $name) {
				if (isset($match[$name])) {
					$region = $match[$name];
					
					// check handlers
					if (isset($handlers[$name])) {
						$handler_set = $handlers[$name];
						if (!is_array($handler_set)) {
							$handler_set = [$handler_set];
						}
						// probe handlers
						foreach ($handler_set as $handler) {
							if (is_string($handler)) {
								$region_new = SyntaxHighlighter::Execute($region, [$handler => ['__probe' => true]]);
							}
							else {
								//$region_new = $this->AddRegexMatchesBasic($handler, $region);
								//$region_new = $this->ProcessPatterns([$handler], $region);
								$rm = new self();
								$region_new = $rm->ProcessPatterns([$handler], $region);
							}
							// only use the first successful probe
							if ($region_new !== $region) {
								$region = $region_new;
								break;
							}
						}
					}
					if (isset($classes[$name])) {
						$region = $this->WrapCodeClass($region, $classes[$name]);
					}
					$output .= $region;
				}
			}
			if (isset($classes[NULL])) {
				$output = $this->WrapCodeClass($output, $classes[NULL]);
			}
			return $this->AddReplacedValue($output);
			
		}, $text);
		return $text;
	}
	
	public function ProcessPatterns(array $handlers, $text) {
		
		foreach ($handlers as $key => $handler) {
			$text = $this->AddRegexMatchesBasic($handler, $text);
		}
		
		return $this->Reconstitute($text);
	}
}

?>