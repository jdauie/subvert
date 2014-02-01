<?php

namespace Jacere;

require_once(__dir__.'/../BaseCodeHandler.php');

class SkhemaCodeHandler extends BaseCodeHandler {
	
	public function GetPatterns() {
		return [
			new CodePattern(
				'`(?<start>{)(?<type>.)(?<token>[^}]*+)(?<end>})`',
				['start', 'type', 'token', 'end'],
				[
					NULL    => 'skh-del',
					'type'  => 'skh-sym',
					'token' => 'skh-tok',
				],
				[
					'token' => new CodePattern(
						'`(?<delim>:)(?<name>[^[]++)(?<options>\[[^]]*+\])?`',
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