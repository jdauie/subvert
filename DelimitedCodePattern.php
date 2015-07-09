<?php

namespace Jacere\Subvert;

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
