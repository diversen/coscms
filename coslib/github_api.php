<?php

/**
 * contains simple class for using oauth with github
 * @package githubapi
 */

/**
 * Simple class for using the github oauth api
 * @package githubapi
 */
class githubApi {
    /**
     *
     * @var array $errors holding errors
     */
    public $errors = array ();
    
    /**
     * oauth stars with getting a login url from configuration
     * @param array $config e.g. $access_config = array (
     *                              'redirect_uri' => 'http://cos/test/callback.php',
     *                              'client_id' => 'app id',
     *                              'state' =>  md5(uniqid()),
     *                              'scope' => 'user'
     *                           );
     * If you don't set use you can only get users basic profile info,
     * but you can use it as atuhentication anyway
     * @return boolean $res true on success and false on failure
     */
    public function getAccessUrl ($config) {

        $_SESSION['state'] = $config['state'];     
        $url = 'https://github.com/login/oauth/authorize';
        $query =  http_build_query($config);
        $url.= '?' . $query;    
        return $url;

    }

    /**
     * sets the access token in a session variable, which
     * then can be used when calling the api
     * @param array $post e.g. $post = array (
                                    'redirect_uri' => 'http://cos/test/callback.php',
                                    'client_id' => 'app_id',
                                    'client_secret' => 'app_secret',
                               );
     * @return boolean $res true on success and false on failure
     */
    public function setAccessToken ($post) {
        if (isset($_GET['error'])) {
            $this->errors[] = $_GET['error'];
            return false;
        }
        
        if (isset($_GET['code'])) {
            $c = new mycurl('https://github.com/login/oauth/access_token');
            $post['code'] = $_GET['code'];
            $post['state'] = $_SESSION['state'];

            $c->setPost($post);
            $c->createCurl();
            $resp = $c->getWebPage();
            
            parse_str($resp, $ary);
            
            if (isset($ary['access_token']) && isset($ary['token_type']) && $ary['token_type'] == 'bearer') {
                echo $_SESSION['access_token'] = $ary['access_token'];
                return true;
            } else {
                $this->errors[] = "No access token returned";
                return false;
            }
        }
        return false;
    }

    /**
     * when we have set the access token we can make calls to the api
     * @param string $command e.g. /user
     * @return array $res array with the reult of the call
     */
    public function apiCall ($command) {

        $end_point = 'https://api.github.com';
        $command = $end_point . "$command";

        $command.= "?access_token=$_SESSION[access_token]";

        $c = new mycurl($command);
        $c->createCurl();
        $resp = $c->getWebPage();
        $ary = json_decode($resp, true);  
        return $ary;
    }
}
