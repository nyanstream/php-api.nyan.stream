<?php
	date_default_timezone_set('Europe/Moscow');
	error_reporting(0);

	include '🤫/config.php';

	/*
	 * Прокси для картинок с VK, используемых в виджете с новостями
	 */

	$imgProxy = array('https://' => 'https://images.weserv.nl/?url=ssl:');

	function get_vk_token() {
		$APIep =   $GLOBALS['APIep'];
		$vkData =  $GLOBALS['vkData'];

		if ($_GET['state'] === 'vk-get-code') {
			$vkGetT = file_get_contents($APIep['vk_ac'] . '?client_id=' . $vkData['id'] . '&client_secret=' . $vkData['secret'] . '&redirect_uri=https://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '&code=' . $_GET['code']);
			$vkGetTj = json_decode($vkGetT);
		} else { return false; }

		if (!$vkGetTj->access_token) { return false; }

		switch ($vkGetTj->user_id) {
			case 115127361:
			case 307130237:
				file_put_contents(dirname(__FILE__) . '/🤫/vk-token.txt', $vkGetTj->access_token);
				echo '<html><body><p>Токен получен!</p></body></html>';
			default:
				return false;
		}
	}

	get_vk_token();

	function get_vk_info() {
		$APIep =     $GLOBALS['APIep'];
		$vkData =    $GLOBALS['vkData'];
		$imgProxy =  $GLOBALS['imgProxy'];

		$vkFile = 'vk-news.json';

		if (filemtime($vkFile) < time() - 10) {
			$vk_vid_ac =  file_get_contents('🤫/vk-token.txt');
			$url =        $APIep['vk'] . '/wall.get?owner_id=-' . $vkData['comID'] . '&count=6&extended=1&v=' . $vkData['api-version'] . '&access_token=' . $vk_vid_ac;

			$vk = file_get_contents($url);
			if (!$vk) { return false; }

			$vkAPI = json_decode($vk)->response;

			$vkAPI_Group = $vkAPI->groups[0];
			$vkAPI_Wall = $vkAPI->items;

			$vkPubInfo = [
				'id' => $vkAPI_Group->id,
				'url' => $vkAPI_Group->screen_name,
				'pic' => $vkAPI_Group->photo_100
			];

			foreach ($vkAPI_Wall as $i=>&$post) {
				$postType = 'post';
				$postPin = 0;

				if ($post->copy_history) { $postType = 'copy'; }
				if ($post->is_pinned) { $postPin = 1; }

				$vkWall[$i] = [
					'id' => $post->id,
					'time' => $post->date,
					'type' => $postType,
					'pin' => $postPin,
					//'ad' => $post->marked_as_ads,
					'text' => $post->text
				];

				if ($post->attachments[0]->photo) {
					$vkWall[$i] += [
						'pic' => [
							'small' => strtr($post->attachments[0]->photo->photo_130, $imgProxy),
							'big' => $post->attachments[0]->photo->photo_604
						]
					];
				}

				if ($post->attachments[0]->video->photo_130) {
					$vkWall[$i] += [
						'pic' => [
							'small' => strtr($post->attachments[0]->video->photo_130, $imgProxy),
							'big' => $post->attachments[0]->video->photo_640
						]
					];
				}
			};

			$vkInfo = [
				'com' => $vkPubInfo,
				'posts' => $vkWall
			];

			file_put_contents(dirname(__FILE__) . '/api/' . $vkFile, json_encode($vkInfo, JSON_UNESCAPED_UNICODE));
		}
	}

	get_vk_info();

	function get_vk_stream_link() {
		$APIep =   $GLOBALS['APIep'];
		$vkData =  $GLOBALS['vkData'];

		$vkFile = 'vk-stream.json';

		if (filemtime($vkFile) < time() - 10) {
			$vk_vid_ac = file_get_contents('🤫/vk-token.txt');

			if (!$vk_vid_ac) { return false; }

			$vk_vid = file_get_contents($APIep['vk'] . '/video.get?owner_id=-' . $vkData['comID'] . '&count=1&v=' . $vkData['api-version'] . '&access_token=' . $vk_vid_ac);

			if (!$vk_vid) { return false; }
			$vkAPIs = json_decode($vk_vid)->response->items[0];

			if (!$vkAPIs->width || !$vkAPIs->height) {
				$strQuality = null;
			} else { $strQuality = $vkAPIs->width . 'x' . $vkAPIs->height; }

			if ($vkAPIs->live_status === "started") {
				$vkIsLive = '&autoplay=1';
			} else { $vkIsLive = ''; }

			$vkStreamData = [
				'url' => $vkAPIs->player . $vkIsLive,
				'pic' => $vkAPIs->photo_800,
				'viewers' => $vkAPIs->spectators,
				'quality' => $strQuality,
				'timestamp' => time()
			];

			file_put_contents(dirname(__FILE__) . '/api/' . $vkFile, json_encode($vkStreamData, JSON_UNESCAPED_UNICODE));
		}
	}

	//get_vk_stream_link();
?>
