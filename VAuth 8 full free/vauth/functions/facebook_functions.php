<?php

if( ! class_exists( 'FbFunctions' ) )	{

	class FbFunctions extends VAuthFunctions {
	
		function oauth_data() {
		
			global $site_url;
			global $vauth_config;
			
			$oauth = array();
			$oauth['needfriends'] = 'yes';
			$oauth['needhash'] = 'yes';
			$oauth['prefix'] = 'fb';
			$oauth['prefix2'] = 'facebook';
			$oauth['disconnect_str'] 	=	"updtime='', ".$oauth['prefix']."_user_friends='', ".$oauth['prefix']."_connected='0', ".$oauth['prefix']."_user_id=''";
			$oauth['scope']				=	'email,user_status,offline_access,user_birthday';
			$oauth['auth_url']			=	'https://www.facebook.com/dialog/oauth?client_id=';
			$oauth['app_id']			=	$vauth_config['facebook_app_id'];
			$oauth['app_secret']		=	$vauth_config['facebook_app_secret'];
			$oauth['redirect_url']		=	$site_url . '/engine/modules/vauth/auth.php?auth_site=facebook';
			$oauth['token_request']		=	'https://graph.facebook.com/oauth/access_token?client_id=';
			$oauth['site_url']			=	$site_url;
			$oauth['group']				=	$vauth_config['facebook_user_group'];

			if (empty($oauth['group'])) $oauth['group'] = 4;
			if (empty($oauth['app_id'])) die('�� ������ ������������� ���������� Facebook');
			if (empty($oauth['app_secret'])) die('�� ������ ��������� ��� ���������� Facebook');
			
			return $oauth;
			
		}		
	
		// ** ������� ��������� ������ �� facebook
		function get_oauth_friends($oauth) {
			
			$oauth_friendlist='';
			
			$oauth['friends']	=	json_decode($this->vauth_get_contents('https://api.facebook.com/method/friends.getAppUsers?access_token='.$oauth['access_token'].'&format=json'),FALSE);
			
			foreach($oauth['friends'] as $k=>$v) {
				if (is_numeric($v)) {
					$v = sprintf("%.0f",$v);
					$oauth_friendlist	= $oauth_friendlist.'&'.$v;
				}
			}
		
			$oauth['friends']	= substr($oauth_friendlist,1);
			
			return $oauth['friends'];

		}	
	
		// ** ������� ����������� � Facebook
		function vauth_auth($oauth) {
		
			global $auth_code;
		
			$_SESSION['auth_from']	=	'facebook';
		
			if 	( empty($oauth['access_token']) and empty($auth_code) ) {
				header('Location: '.$oauth['auth_url'].$oauth['app_id'].'&redirect_uri='.$oauth['redirect_url'].'&scope='.$oauth['scope']);
				die;
			}
			
			if ( !empty($auth_code) ) {
				
				parse_str($this->vauth_get_contents($oauth['token_request'].$oauth['app_id'].'&redirect_uri='.$oauth['redirect_url'].'&client_secret='.$oauth['app_secret'].'&code='.$auth_code));
				$oauth['access_token'] = $access_token;		
				if (!empty($oauth['access_token'])) $_SESSION['facebook_access_token']	=	$oauth['access_token']; else { die($my_little_url);header('Location: '.$oauth['site_url']); die(); }
			
			}
			
			return $oauth;
			
		}
	
		// ** ������� ��������� ���������� ������������ �� Facebook
		function get_oauth_info($oauth) {
		
			global $vauth_text;
			global $db;
			global $site_url;
			
			$oauth_info		=	json_decode($this->vauth_get_contents('https://graph.facebook.com/me?access_token='.$oauth['access_token']), FALSE); //�������� ��������� � ������������
			
			$oauth['uid']		=	$this->conv_it($oauth_info->id);
			if (!is_numeric($oauth['uid'])) { header('Location: '.$site_url); die(); }
			$oauth['name']		=	$this->conv_it($oauth_info->name); //��� � �������
			$oauth['nick']		=	$this->conv_it($oauth_info->username); //�����	
			$oauth['birthday'] =	$this->conv_it($oauth_info->birthday); //�����	
			$oauth['birthday'] =	str_replace('/','.',$oauth['birthday']);
			
			if ( !empty($oauth['birthday']) ) {
			
				$bdate = explode('.',$oauth['birthday']);
				$oauth['birthday'] = $bdate[1].'.'.$bdate[0].'.'.$bdate[2];
			
			}
			
			if (empty($oauth['nick'])) $oauth['nick'] = $oauth['name'];
			if (empty($oauth['name'])) $oauth['name'] = $oauth['uid'];

			$avatar 			=	$this->get_curl_headers('https://graph.facebook.com/'.$oauth['uid'].'/picture?type=large');
			
			
			
			preg_match("!https://(.*?).(?:jpe?g|png|gif)!Ui",$avatar,$avatar);
			
			$oauth['avatar'] 		=	$avatar[0];
			$oauth['avatar'] 		=	$oauth['avatar'];
			
			$oauth['bio']			=	$this->conv_it($oauth_info->bio); //���
			$oauth['bio']			=		str_replace("\r\n","<br/>",$oauth['bio']);
			$oauth['bio']			=		'<br/>'.$oauth['bio'];
			$oauth['url']			=	$this->conv_it($oauth_info->link); //����� ��������
			$oauth['bplace']		=	$this->conv_it($oauth_info->hometown->name); //����� ��������
			$oauth['quotes']		=	$this->conv_it($oauth_info->quotes); //������
			$oauth['gender']		=	$this->conv_it($oauth_info->gender); //���
			
			switch(	$oauth['gender']	) {
			
				case 'male'	: $oauth['gender'] = $vauth_text[4];	break;
				case 'female'	: $oauth['gender'] = $vauth_text[5];	break;
			
			}
			
			$oauth['location']		=	$this->conv_it($oauth_info->location->name); //��������������
			$oauth['last_name']	=	$this->conv_it($oauth_info->last_name); //�������
			$oauth['first_name']	=	$this->conv_it($oauth_info->first_name); //���
			$oauth['email']		=	$this->conv_it($oauth_info->email); //����
						
			$oauth['username']	=	$oauth['first_name']  . ' ' .  $oauth['last_name'];
			$oauth['fullname']	=	$oauth['username'];
			$oauth['bio']		=	$db->safesql( trim( htmlspecialchars( strip_tags( $oauth['bio'] ) ) ) );	
			$oauth['name']		=	$db->safesql( trim( htmlspecialchars( strip_tags( $oauth['name'] ) ) ) );	
			$oauth['nick']		=	$db->safesql( trim( htmlspecialchars( strip_tags( $oauth['nick'] ) ) ) );	
			$oauth['quotes']	=	$db->safesql( trim( htmlspecialchars( strip_tags( $oauth['quotes'] ) ) ) );	
			$oauth['location']	=	$db->safesql( trim( htmlspecialchars( strip_tags( $oauth['location'] ) ) ) );
			
			$oauth['update_time']	=	time();	
			
			return $oauth;
		}
	}
}

$vauth_api = new FbFunctions ();			
		
?>