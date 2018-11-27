<?php

	$PHP_data = [
		'timezone' => date_default_timezone_get(),

		'sched' => [
			'count' => $schedAnime_count,
			'countExpr' => countExprs($schedAnime_data_tmp),
			'latest' => [
				'is' =>     $schedAnime_latest ? true                         : false,
				'title' =>  $schedAnime_latest ? $schedAnime_latest['title']  : '',
				'start' =>  $schedAnime_latest ? $schedAnime_latest['s']      : null,
				'end' =>    $schedAnime_latest ? $schedAnime_latest['e']      : null,
			]
		],

		'ts' => [
			'sched' =>    filemtime($file['sched']),
			'noti' =>     filemtime($file['noti']),
			'current' =>  time()
		],

	];

	if ($USER['isAdmin'] == true) {
		$PHP_data['noti'] = [
			'enabled' =>  $noti_data['enabled'],
			'text' =>     $noti_data['text'] ? $noti_data['text'] : '',
			'color' =>    isset($noti_data['color']) ? $noti_data['color'] : false
		];

		$PHP_data['vk'] = [
			'URL' => $APIep['vk_au'],
			'appID' => $vkData['id'],
			'api' => 'api-backend-vk.php'
		];

		$PHP_data['server'] = $_SERVER['SERVER_NAME'];
	}

?>
