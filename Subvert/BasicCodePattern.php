<?php

namespace Jacere\Subvert;

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
