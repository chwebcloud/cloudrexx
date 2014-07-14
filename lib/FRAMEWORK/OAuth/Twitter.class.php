<?php

/**
 * Twitter
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  lib_oauth
 */

namespace Cx\Lib\OAuth;

global $cl;
$cl->loadFile(ASCMS_LIBRARY_PATH . '/services/Twitter/tmhOAuth.php');
$cl->loadFile(ASCMS_LIBRARY_PATH . '/services/Twitter/tmhUtilities.php');

/**
 * OAuth class for twitter authentication
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Ueli Kramer <ueli.kramer@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  lib_oauth
 */
class Twitter extends OAuth
{
    /**
     * @var the object of the third party library
     */
    private static $twitter;

    /**
     * @var the user data of the logged in social media user
     */
    protected static $userdata;

    const OAUTH_PROVIDER = 'twitter';

    /**
     * Login to facebook and get the associated contrexx user.
     */
    public function login()
    {
        // fixing timestamp issue with twitter
        // it is necessary that the twitter server has the same time as our system
        date_default_timezone_set('UTC');
        $tmhOAuth = new \tmhOAuth(array(
            'consumer_key' => $this->applicationData[0],
            'consumer_secret' => $this->applicationData[1],
        ));

        // set the timestamp
        $tmhOAuth->config['force_timestamp'] = true;
        $tmhOAuth->config['timestamp'] = time();

        if (isset($_GET['oauth_verifier'])) {
            $tmhOAuth->config['user_token'] = $_SESSION['oauth']['oauth_token'];
            $tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

            $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
                'oauth_verifier' => $_GET['oauth_verifier'],
                'x_auth_access_type' => 'read',
            ));

            $access_token = $tmhOAuth->extract_params($tmhOAuth->response['response']);
            $tmhOAuth->config['user_token'] = $access_token['oauth_token'];
            $tmhOAuth->config['user_secret'] = $access_token['oauth_token_secret'];

            $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials'));
            $resp = json_decode($tmhOAuth->response['response']);

            unset($_SESSION['oauth']);

            $name = explode(' ', $resp->name);
            self::$userdata = array(
                'first_name' => $name[0],
                'last_name' => $name[1],
                'email' => $resp->screen_name . '@twitter.com',
            );
            $this->getContrexxUser($resp->id);
        } else {
            $tmhOAuth->request('POST',$tmhOAuth->url('oauth/request_token',""),array('oauth_callback' => \Cx\Lib\SocialLogin::getLoginUrl(self::OAUTH_PROVIDER)));
            $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
            $url = 'https://api.twitter.com/oauth/authenticate?oauth_token='.$_SESSION['oauth']['oauth_token'];
            \Cx\Core\Csrf\Controller\ComponentController::header("Location: ". $url);
            exit;
        }
    }
}
