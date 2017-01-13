<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

class OAuth1WP extends OAuth1\OAuth1
{
	/**
	 * Ths lib does not include oauth_callback by default for whatever reason
	 *
	 * @return array
	 */
	public function baseProtocolParameters()
	{
		$default_config = parent::baseProtocolParameters();
		$default_config['oauth_callback'] = $this->config()->callbackUrl();

		return $default_config;
	}
}

$consumer_key               = ''; //This is the consumer key that WPOauth1 generates for you app
$consumer_secret            = ''; //This is the consumer secret that WPOauth1 generates for you app
$base_url 			        = ''; //This is the base url of the WordPress installation (the OAuth server)
$callback_url               = ''; //This is the callback URL of your APP (the url of this simple app, e.g: localhost:8000)

//WPOAuth1 endpoints
$request_token_endpoint 	= 'oauth1/request';
$authorize_endpoint		    = 'oauth1/authorize';
$access_endpoint		    = 'oauth1/access';

$oauth1 = new OAuth1WP( [
	'consumer_key'      	=> $consumer_key,
	'consumer_secret'   	=> $consumer_secret,
	'request_token_url' 	=> $base_url . $request_token_endpoint,
	'authorize_url'     	=> $base_url . $authorize_endpoint,
	'access_token_url'  	=> $base_url . $access_endpoint,
	'callback_url'      	=> $callback_url,
] );

try{
	/**
	 * Uncomment this line to reset the process (and comment it out again)
	 */
	//unset( $_SESSION['access_token'] );

	//step 4 - get logged in user
	if ( isset( $_SESSION['access_token'] ) ) {
		$access_token = unserialize( $_SESSION['access_token'] );
		$oauth1->setGrantedAccessToken( $access_token );

		//getting the logged in user
		$response = $oauth1->get( $base_url . 'wp-json/wp/v2/users/me' );
		header( 'Content-Type: application/json' );

		$user = json_decode( $response->getBody()->getContents() );
		echo "Hello " . $user->name . ' (#' . $user->id . ')';
	}
	//step 3 - get the access token
	else if ( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] ) ) {
		$requestToken = unserialize( $_SESSION['request-token'] );

		$access_token = $oauth1->accessToken( $requestToken, $_GET['oauth_token'], $_GET['oauth_verifier'] );

		$_SESSION['access_token'] = serialize( $access_token );

		unset( $_SESSION['request-token'] );
		//refresh the page
		header( "Location: {$_SERVER['PHP_SELF']}" );
		exit;
	}
	//step 1 and 2 - request token and authorization
	else {
		//getting request token
		$requestToken = $oauth1->requestToken();

		$_SESSION['request-token'] = serialize( $requestToken );

		$authorizationUrl = $oauth1->buildAuthorizationUrl( $requestToken );

		header( "Location: {$authorizationUrl}" );
		exit;
	}

} catch ( Exception $e ) {
	var_dump( $e->getMessage() );
}
