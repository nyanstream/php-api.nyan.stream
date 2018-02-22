<?php
	/*
	 * URL разлиных API
	 */

	$APIep = [
		'vk' =>     'https://api.vk.com/method',
		'vk_ac' =>  'https://oauth.vk.com/access_token',
		'vk_au' =>  'https://oauth.vk.com/authorize',
	];

	/*
	 * Данные для API VK
	 */

	$vkData = [
		'comID' => 0, // ID сообщества
		'id' => 0, // ID связанного приложения (vk.com/apps?act=manage)
		'api-version' => 5.73, // версия VK API
		'secret' => '', // защищённый ключ из связанного приложения
		'service' => '' // сервисный ключ доступа из связанного приложения
	];
?>
