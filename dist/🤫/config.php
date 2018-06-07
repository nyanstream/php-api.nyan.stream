<?php
	/*
	 * URL (эндпоинты) различных API
	 */

	$APIep = [
		'vk' =>     'https://api.vk.com/method',
		'vk_ac' =>  'https://oauth.vk.com/access_token',
		'vk_au' =>  'https://oauth.vk.com/authorize',
		'push' =>   'https://pushall.ru/api.php'
	];

	/*
	 * Данные для API VK
	 */

	$vkData = [
		'comID' =>        0,     // ID сообщества
		'id' =>           0,     // ID связанного приложения (vk.com/apps?act=manage)
		'api-version' =>  5.78,  // версия VK API
		'secret' =>       '',    // защищённый ключ из связанного приложения
		'service' =>      ''     // сервисный ключ доступа из связанного приложения
	];

	/*
	 * Данные для PushAll API
	 */

	$pushData = [
		'id' =>   0,
		'key' =>  ''
	];

	/*
	 * Перечень пользователей Basic Auth, имеющих доступ к административным функциям
	 */

	$_adminUsers = [''];

	$_phpUserName = $_SERVER['PHP_AUTH_USER'];

	$USER = [
		'name' =>     $_phpUserName ? $_phpUserName : 'Имярек',
		'isAdmin' =>  in_array($_phpUserName, $_adminUsers)
	];
?>
