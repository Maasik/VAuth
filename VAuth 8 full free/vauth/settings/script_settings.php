<?PHP

	// ** ���������� ���������, ����������� ��� ������ ������
	
	if (!defined('DATALIFEENGINE')) 	define	(	'DATALIFEENGINE',	true	);
	if (!defined('ROOT_DIR')) 			define	(	'ROOT_DIR',			substr(dirname (__FILE__),0,strpos( dirname ( __FILE__ ),"engine" )-1) );
	if (!defined('ENGINE_DIR')) 		define	(	'ENGINE_DIR',		ROOT_DIR . '/engine'	);

	#��������� �������
	
		#������������ �������������� ������ �������
		$func_path = ENGINE_DIR . '/modules/vauth/functions/'; //����� �������
		$lang_path = ENGINE_DIR . '/modules/vauth/langfiles/'; //����� ������
		
		
		// ** ���������, ��� �� ���� � �������, � ���������� ��:

		if ( file_exists( ENGINE_DIR . '/api/api.class.php' ) )					include_once( ENGINE_DIR . '/api/api.class.php' );					else die('��� ����� DLE API ������� ������ � ����� ' . ENGINE_DIR . '/api/' );
		if ( file_exists( $func_path . '/vauth_functions.php' ) )				require_once( $func_path . '/vauth_functions.php' );				else die('��� ����� �������� ������� ������ � ����� ' . $func_path );
		if ( file_exists( dirname (__FILE__) . '/user_settings.php' ) )			require_once( dirname (__FILE__) . '/user_settings.php' );			else die('��� ����� ���������������� �������� ������ - ' . dirname (__FILE__) . '/user_settings.php' );	
		if ( file_exists( $lang_path . $vauth_config['language'] . '.php') )	include_once( $lang_path . $vauth_config['language'] . '.php' );	elseif (file_exists( $lang_path.'russian.php')) include_once( $lang_path.'russian.php'); else die('��� ��������� �������� ������ � ����� ' . $lang_path);
		if ( file_exists( ENGINE_DIR . '/modules/vauth/styles/styles.php') )	include_once( ENGINE_DIR . '/modules/vauth/styles/styles.php' );	else die('��� ����� ������ ������ � ����� '.ENGINE_DIR . '/modules/vauth/styles/' );
		
		// ** ����� ����� ����� � ���� ��� �����������
		
		$auth_site = @$_GET['auth_site'];
		$site_url = trim(mb_strtolower($vauth_config['site_url']));
		if (substr($site_url, -1) == '/') $site_url = substr($site_url, 0, -1);
		if (strpos($site_url,'http:') === false) $site_url = 'http://'.$site_url;
		if (empty($site_url) and empty($_GET['mod'])) die('��� ���������� ������ ������ VAuth ���������� ������� ����� ����� �� ������� �� �������� <br/> ��� ����� ������� � �����-������.');
		
		// ** ���� � ��� ������ ������ �����������
		if ($auth_site) {
		
			$vauth_api = $vauth_api->load_oauth_modules($auth_site);
			$oauth = $vauth_api->oauth_data();
		
		} else $vauth_api = new VAuthFunctions ();
		
		// ** ������ ��������� ������
			
			// ** ���� ��� ��������� � ���������� ������� ������������� � �� ������ (������ � �������������, � �� ������������ �� ����� ����������������)	
			$userhash_pass = 'n3WioTye94u39djee'; // ** ��� ����� ���� ������ ������������ �� ������ ����������������
			$userhash_salt = 'nUE0pQbiY3MuqKEbAl5xwodihwe8do33qdw'; // ** ��� ����� ���� ������ ������������ �� ������ ����������������
			// ** ���� ��� ��������� � ���������� ������� ������������� � �� ������ (������ � �������������, � �� ������������ �� ����� ����������������)
			
			// ** ��������� ����������� ������������ ����� ������ (1 - ��, 0 - ���)
			$vauth_config['allow_register'] = 1;
			
			// ** ��� ������� ������
			$admin_php_name = 'admin.php';
			
			// ** ����� URL ���������� ��������� (���� ������ ���, �� � � engine.php ���� ����� ��������)
			$ac_url	=	'/index.php?do=account_connect';

	
		#��� ��������� �������
		
		// ** ������������� ����������
		$ac_connect = @$_SESSION['ac_connect'];
		
		// ** ������ �����!
		$auth_code = htmlentities(@$_GET['code']);
		
		// ** ������ �����2
		$cancel = @$_GET['cancel'];
		
		if ( empty( $_SESSION['referrer'] ) ) $auth_url = $site_url; else $auth_url = $_SESSION['referrer'];
		if ( !empty( $cancel ) ) header('Location: ' . $site_url);

		
		// ** ������� ������, ��� ������ ����� �������� ����� ������� ������
		
		// ** ��������� �� ����������������� ����������� ��� ������ ������ ������� PHP
		if ($vauth_api->function_enabled('file_get_contents')) {
		
			$get_contents = 1;
			
		} else $get_contents = 0;
	
		
		if ($vauth_api->function_enabled('getimagesize')) {
		
			if ($vauth_api->function_enabled('exif_imagetype')) {

				if ($vauth_api->function_enabled('imagecreatefromjpeg')) {
					
					$get_image = 1;
				
				} else $get_image = 0;
			
			} else $get_image = 0;
		
		} else $get_image = 0;
		// ** �� ���� ������������ ������ ���������
?>