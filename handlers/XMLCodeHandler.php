<?php

namespace Jacere;

require_once(__dir__.'/../BaseCodeHandler.php');

class XMLCodeHandler extends BaseCodeHandler {
	
	public function GetPatterns() {
		return [
			new CodePattern(
				'`(?<start>&lt;/?)(?<name>[^&\s!][^&\s]*+)(?<attributes>.*?)(?<end>&gt;)`',
				['start', 'name', 'attributes', 'end'],
				[
					'name' => 'xml-tag',
				],
				[
					'attributes' => new CodePattern(
						'`(?<name>(\s++)([^=]++))(?<equals>=)(?<value>\s*"[^"]*+")`',
						['name', 'equals', 'value'],
						[
							'name'  => 'xml-att',
							'value' => 'xml-val',
						]
					)
				]
			),
		];
	}
}

?>