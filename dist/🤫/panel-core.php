<?php
	include 'config.php';

	date_default_timezone_set('Europe/Moscow');

	$file = [
		'sched' =>  'api/sched.json',
		'noti' =>   'api/noti.json'
	];

	$path = getcwd();

	$schedAnime =  file_get_contents($file['sched']);
	$noti =        file_get_contents($file['noti']);

	$schedAnime_data =  json_decode($schedAnime, true);
	$noti_data =        json_decode($noti, true);

	/*
	 * Создаются временные массивы без "секретных эфиров"
	 * Важно понимать: счётчик эфиров и последний эфир берутся именно из этого, временного, а не из настоящего массива.
	 */

	$schedAnime_data_tmp = [];

	foreach ($schedAnime_data as &$item) {
		if ($item['secret'] == true) { continue; }
		array_push($schedAnime_data_tmp, $item);
	}

	$schedAnime_count  = count($schedAnime_data_tmp);
	$schedAnime_latest = $schedAnime_data_tmp[$schedAnime_count-1];

	/*
	 * Компановка нового элемента расписания
	 */

	function schedNew($_time, $_duration, $_title, $_link, $_is_secret, $_is_backup) {
		$time_start = strtotime($_time);

		$duration = $_duration
			? $_duration * 60
			: 3600;

		$time_end = $time_start + $duration;

		$_title = trim(preg_replace('/\s+/', ' ', $_title));

		$title = addslashes($_title);
		$link = addslashes($_link);

		$new = [
			's' => $time_start,	'e' => $time_end,
			'title' => json_decode('"' . $title . '"')
		];

		if ($_link != null) {
			$new['link'] = json_decode('"' . $link . '"');
		}

		if ($_is_secret == 'on') { $new['secret'] = true; }
		if ($_is_backup == 'on') { $new['backup'] = true; }

		return $new;
	}

	/*
	 * Сортировка расписания
	 */

	function shedSort($_what) {
		usort($_what, function($a, $b) { return $a['s'] - $b['s']; });
		return $_what;
	}

	/*
	 * Добавление элемента в расписание
	 */

	function addShedData($_file, $_where, $_what) {
		global $path;

		array_push($_where, $_what);
		$_where = shedSort($_where);

		if ($_what['title'] != null) {
			file_put_contents($path . '/' . $_file, json_encode($_where, JSON_UNESCAPED_UNICODE));
		}
	}

	/*
	 * Удаление элемента из расписания
	 */

	function rmShedData($_file, $_where, $_item) {
		global $path;

		$_where_tmp = [];

		foreach($_where as $item) {
			if ($_item == $item) {
				unset($item); continue;
			}

			array_push($_where_tmp, $item);
		}

		$_where = shedSort($_where_tmp);

		file_put_contents($path . '/' . $_file, json_encode($_where, JSON_UNESCAPED_UNICODE));
	}

	if (isset($_POST['add_air'])) {
		$newAir = schedNew(
			$_POST['time_start'],
			$_POST['duration'],
			$_POST['air_name'],
			$_POST['air_link'],
			$_POST['air_is_secret'],
			$_POST['air_is_backup']
		);

		addShedData($file['sched'], $schedAnime_data, $newAir);
	}

	/*
	 * Отправка пуша в PushAll
	 */

	// if (isset($_POST['send_push'])) {
	// 	curl_setopt_array($pushCurl = curl_init(), [
	// 		CURLOPT_URL => $APIep['push'],
	// 		CURLOPT_POSTFIELDS => [
	// 			'type' => 'broadcast',
	// 			'id' => $pushData['id'],
	// 			'key' => $pushData['key'],
	// 			'text' => 'Тестовое сообщение',
	// 			'title' => 'Заголовок'
	// 		],
	// 		CURLOPT_SAFE_UPLOAD => true,
	// 		CURLOPT_RETURNTRANSFER => true
	// 	]);
	//
	// 	curl_close($pushCurl);
	// }

	if (isset($_POST['rm_air'])) {
		rmShedData($file['sched'], $schedAnime_data, $schedAnime_latest);
	}

	/*
	 * Удаление элементов расписания с просроченным таймштампом
	 * (плюс счётчик этих элементов, нужен для вывода на страницу)
	 */

	function countExprs($_where) {
		$count = 0;

		foreach ($_where as $item) {
			if ($item['e'] < time()) $count++;
		}

		return $count;
	}

	function rmExprs($_file, $_where, $_time) {
		global $path;

		$_where_tmp = [];

		foreach ($_where as $item) {
			if ($item['e'] < $_time) {
				unset($item); continue;
			}

			array_push($_where_tmp, $item);
		}

		$_where = shedSort($_where_tmp);

		file_put_contents($path . '/' . $_file, json_encode($_where, JSON_UNESCAPED_UNICODE));
	}

	if (isset($_POST['expired_clear']) && $USER['isAdmin'] == true) {
		rmExprs($file['sched'], $schedAnime_data, time());
	}

	/*
	 * Манипулятор оповещений
	 */

	function notiNew($_text, $_color) {
		$text = addslashes($_text);

		$new = [
			'enabled' => true,
			'time' => time(),
			'text' => json_decode('"' . $text . '"')
		];

		if ($_color != '#ffffff') {
			$new['color'] = json_decode('"' . $_color . '"');
		}

		return $new;
	}

	if (isset($_POST['noti'])) {
		$noti_content = !isset($_POST['noti_rm'])
			? notiNew($_POST['noti_text'], $_POST['noti_color'])
			: ['enabled' => false];

		file_put_contents($path . '/' . $file['noti'], json_encode($noti_content, JSON_UNESCAPED_UNICODE));
	}
?>
