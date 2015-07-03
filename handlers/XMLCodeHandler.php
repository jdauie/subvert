<?php

namespace Jacere\Subvert\Handlers;

use Jacere\Subvert\BaseCodeHandler;
use Jacere\Subvert\CodePattern;

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