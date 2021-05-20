<?php
    if ( isset($_GET['steam'] ) )
    {
        try
        {
			require_once( '/home/arcengames/public_html/dev/plugins/GoogleOauth/pages/openid.php' );
			require 'SteamConfig.php';
			require_once( '../../core.php' ); // This is in a plugin, so this is where core.php is found for me
			require_api( 'authentication_api.php' ); 
			$openid = new LightOpenID($steamauth['domainname'] );

            if ( !$openid->mode) 
			{
				$openid->identity = 'https://steamcommunity.com/openid';
                header( 'Location: '. $openid->authUrl() );
            }
            elseif($openid->mode == 'cancel' ) 
			{
                plugin_log_event('User has canceled authentication!');
            } else
            {
				if ($openid->validate()) 
				{ 
					$id = $openid->identity;
					$ptn = "/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
					preg_match($ptn, $id, $matches);
					$_SESSION['steamid'] = $matches[1];
					$url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$steamauth['apikey']."&steamids=".$_SESSION['steamid'];
					$context = stream_context_create( array( 'http' => array( 'header'=>'Connection: close\r\n' ) ) );
					$json_source = file_get_contents( $url. "&format=json",false, $context );
					$json_output = json_decode($json_source, false );
					$steamid = $_SESSION['steamid'];
					foreach ($json_output->response->players as $player)
					{
						$regOptions = array(
							'username' => 'steamuser-'.$player->personaname,
							'email' => 'steamuser-' . $player->steamid . '@' . "steamcommunity.com",
							'member_name' => 'steamuser-' . $player->steamid,
							'real_name' => $player->personaname,
						);
						var_dump($regOptions);
						$t_username = $regOptions['username'];
						$t_email = $regOptions['email'];
						$t_user_id_email = empty($t_email) ? false : user_get_id_by_email( $t_email );
						$t_realname = $regOptions['real_name'];
						if (!$t_user_id_email) 
						{
							if (!empty($t_username)) 
							{
								user_create($t_username, auth_generate_random_password(), $t_email, auth_signup_access_level(), false, true, $t_realname);
								plugin_log_event("User creation has run.");
								$t_user_id = user_get_id_by_email( $t_email );
								auth_login_user( $t_user_id );
								header("Location:".__FILE__);
								return;
							}
							plugin_log_event("Either no username was passed, or autocreation is turned off.");
							return;
						}
						else 
						{
							if ($t_user_id_email && auth_get_current_user_id() !== $t_user_id_email)
							{
								plugin_log_event("User found from email, login now");
								auth_login_user( $t_user_id_email );
								header("Location:".__FILE__);
								return;
							}
							else
								plugin_log_event("User already logged in!");
						}
					}
				}
			}
		}
        catch ( ErrorException $e) 
		{
            echo $e->getMessage();
        }
    }