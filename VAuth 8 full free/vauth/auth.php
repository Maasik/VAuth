<?php
	
	#header('Access-Control-Allow-Origin: *');
	// ** ��������� ������ ** //
	session_start();
	
	if (!empty($_GET['ref'])) $_SESSION['referrer'] = $_GET['ref'];
	
	// ** �������� ��������� ������� ** //
	include_once("settings/script_settings.php");
	
	$new_user = '';
	
	if (empty($_POST['regform'])) {
	
		// ** �������� ��� ����� ��� ����������� ** //
		$vauth_api->go_auth($auth_site);
		
		// ** ����� � ���������� �������� ������������ ** //
		$new_user = $vauth_api->go_login($auth_site,$ac_connect);
	
	}
	

	// ** ����������� ������ ������������ ** //
	$vauth_api->go_register($auth_site,$new_user);
	
?>