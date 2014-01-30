<?php

namespace Jacere;

class XMLCodeHandler extends BaseCodeHandler {
	
	public function GetReplacements() {
		return [
			[
				'pattern' => '`(&lt;/?)([^&\s!][^&\s]*+)(.*?)(&gt;)`',
				'wrapper' => [
					NULL,
					'xml-tag',
					NULL,
					NULL,
				],
				'handler' => [
					'3:' => [
						'pattern' => '/(\s++)([^=]++)(=)(\s*"[^"]*+")/',
						'wrapper' => [
							NULL,
							'xml-att',
							NULL,
							'xml-val',
						]
					]
				]
			],
		];
	}
}

?>