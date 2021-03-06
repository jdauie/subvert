<?php

namespace Jacere\Subvert\Handlers;

use Jacere\Subvert\BaseCodeHandler;
use Jacere\Subvert\CodePattern;

class SkhemaCodeHandler extends BaseCodeHandler {
	
	public function GetPatterns() {
		return [
			new CodePattern(
				'`(?<start>{)((?<type>[@$#^\.?/])(?<token>[^:}]*+)|(?<evaltype>%))(?<eval>[^}]*+)(?<end>})`',
				['start', 'type', 'evaltype', 'token', 'eval', 'end'],
				[
					NULL       => 'skh-del',
					'type'     => 'skh-sym',
					'evaltype' => 'skh-sym',
					'token'    => 'skh-tok',
				],
				[
					'eval' => new CodePattern(
						'`(?<delim>:)?(?<name>[^:[]++)(?<options>\[[^]]*+\])?`',
						['delim', 'name', 'options'],
						[
							'delim'   => 'skh-sym',
							'name'    => 'keyword',
							'options' => 'xml-att',
						]
					)
				]
			),
		];
	}
}

?>