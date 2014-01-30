<?php

namespace Jacere;

class SkhemaCodeHandler extends BaseCodeHandler {
	
	public function GetReplacements() {
		return [
			[
				'pattern' => '`({)(.)([^}]*+)(})`',
				'wrapper' => [
					'skh-del',
					'skh-sym',
					'skh-tok',
					'skh-del',
				],
				'handler' => [
					'3:' => [
						'pattern' => '`(:)([^[]++)(\[[^]]*+\])?`',
						'wrapper' => [
							'skh-sym',
							'keyword',
							'xml-att',
						]
					]
				]
			],
		];
	}
}

?>