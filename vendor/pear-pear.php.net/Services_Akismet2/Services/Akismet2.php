<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Akismet2 is a package to use Akismet spam-filtering API from PHP
 *
 * This package provides an object-oriented interface to the Akismet REST
 * API. The Akismet API is used to detect and to filter spam comments posted on
 * weblogs.
 *
 * There are several anti-spam service providers that use the Akismet API. To
 * use the API, you will need an API key from such a provider. Example
 * providers include {@link http://wordpress.com Wordpress} and
 * {@link http://antispam.typepad.com/ TypePad}.
 *
 * Most services are free for personal or low-volume use, and offer licensing
 * for commercial or high-volume applications.
 *
 * This package is derived from the miPHP Akismet class written by Bret Kuhns
 * for use in PHP 4. This package requires PHP 5.2.1.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2008 Bret Kuhns, silverorange
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   CVS: $Id: Akismet2.php 273499 2009-01-14 04:21:21Z gauthierm $
 * @link      http://pear.php.net/package/Services_Akismet2
 * @link      http://akismet.com/
 * @link      http://akismet.com/development/api/
 * @link      http://www.miphp.net/blog/view/php4_akismet_class
 */

/**
 * Comment class definition.
 */
require_once 'Services/Akismet2/Comment.php';

/**
 * Exception thrown when an invalid API key is used.
 */
require_once 'Services/Akismet2/InvalidApiKeyException.php';

/**
 * Exception thrown when an invalid API key is used.
 */
require_once 'Services/Akismet2/HttpException.php';

/**
 * HTTP request object
 */
require_once 'HTTP/Request2.php';

// {{{ class Services_Akismet2

/**
 * Class to use Akismet API from PHP
 *
 * Example usage:
 * <code>
 *
 * /*
 *  * Handling user-posted comments
 *  {@*}
 *
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
 *     if ($akismet->isSpam($comment)) {
 *         // rather than simply ignoring the spam comment, it is recommended
 *         // to save the comment and mark it as spam in case the comment is a
 *         // false positive.
 *     } else {
 *         // save comment as normal comment
 *     }
 * } catch (Services_Akismet2_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet2_HttpException $httpException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $httpException->getMessage();
 * } catch (Services_Akismet2_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /*
 *  * Submitting a comment as known spam
 *  {@*}
 *
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
 *     $akismet->submitSpam($comment);
 * } catch (Services_Akismet2_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet2_HttpException $httpException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $httpException->getMessage();
 * } catch (Services_Akismet2_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /*
 *  * Submitting a comment as a false positive
 *  {@*}
 *
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
 *     $akismet->submitFalsePositive($comment);
 * } catch (Services_Akismet2_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet2_HttpException $httpException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $httpException->getMessage();
 * } catch (Services_Akismet2_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * </code>
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2
{
    // {{{ protected properties

    /**
     * The port to use to connect to the Akismet API server
     *
     * Defaults to 80.
     *
     * @var integer
     *
     * @see Services_Akismet2::setConfig()
     */
    protected $apiPort = 80;

    /**
     * The Akismet API server name
     *
     * Defaults to 'rest.akismet.com'.
     *
     * @var string
     *
     * @see Services_Akismet2::setConfig()
     */
    protected $apiServer = 'rest.akismet.com';

    /**
     * The Akismet API version to use
     *
     * Defaults to '1.1'.
     *
     * @var string
     *
     * @see Services_Akismet2::setConfig()
     */
    protected $apiVersion = '1.1';

    /**
     * The URI of the webblog for which Akismet services will be used
     *
     * @var string
     *
     * @see Services_Akismet2::__construct()
     */
    protected $blogUri = '';

    /**
     * The API key to use to access Akismet services
     *
     * @var string
     *
     * @see Services_Akismet2::__construct()
     */
    protected $apiKey = '';

    /**
     * @var HTTP_Request2
     *
     * @see Services_Akismet2::setRequest()
     */
    protected $request = null;

    /**
     * Whether or not the API key is valid
     *
     * @var boolean
     *
     * @see Services_Akismet2::isApiKeyValid()
     */
    protected $apiKeyIsValid = null;

    /**
     * The HTTP user-agent to use
     *
     * If this is an empty string, a default user-agent string is generated and
     * used.
     *
     * @see Services_Akismet2::setConfig()
     * @see Services_Akismet2::getUserAgent()
     */
    protected $userAgent = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new Akismet object
     *
     * @param string        $blogUri the URI of the webblog homepage.
     * @param string        $apiKey  the API key to use for Akismet services.
     * @param array         $config  optional. An associative array of
     *                               configuration options. See
     *                               {@link Services_Akismet2::setConfig()}.
     * @param HTTP_Request2 $request optional. The HTTP request object to use.
     *                               If not specified, a HTTP request object is
     *                               created automatically.
     *
     * @see Services_Akismet2::setConfig()
     */
    public function __construct($blogUri, $apiKey, array $config = array(),
        HTTP_Request2 $request = null
    ) {
        $this->blogUri = $blogUri;
        $this->apiKey  = $apiKey;

        // set http request object
        if ($request === null) {
            $request = new HTTP_Request2();
        }

        $this->setRequest($request);

        // set options
        $this->setConfig($config);

    }

    // }}}
    // {{{ setConfig()

    /**
     * Sets one or more configuration values
     *
     * Configuration values are:
     *
     * - <kbd>apiServer</kbd>  - the API server to use. By default, the Akismet
     *                           API server (owned by Wordpress.com) is used.
     *                           Set this to use an alternate Akismet API
     *                           service provider.
     * - <kbd>apiPort</kbd>    - the HTTP port to use on the API server.
     * - <kbd>apiVersion</kbd> - the API version to use.
     * - <kbd>userAgent</kbd>  - the HTTP user-agent to use. By default, the
     *                           user-agent <kbd>@name@/@api-version@ |
     *                           Akismet/1.1</kbd> is used.
     *
     * Example usage:
     * <code>
     * // sets config using an associative array
     * $akismet->setConfig(array(
     *     'apiServer' => 'rest.akismet.com',
     *     'apiPort'   => 80
     * ));
     *
     * // sets config using fluent interface
     * $akismet->setConfig('apiServer', 'rest.akismet.com')
     *         ->setConfig('apiPort', 80);
     * </code>
     *
     * @param string|array $name  config name or an associative array containing
     *                            configuration name-value pairs.
     * @param string|null  $value config value. Ignored if <kbd>$name</kbd> is
     *                            an array.
     *
     * @return Services_Akismet2 the Akismet API object.
     */
    public function setConfig($name, $value = null)
    {
        if (is_array($name)) {
            $options = $name;
        } else {
            $options = array($name => $value);
        }

        if (array_key_exists('apiServer', $options)) {
            $this->apiServer = strval($options['apiServer']);
        }

        if (array_key_exists('apiPort', $options)) {
            $this->apiPort = strval($options['apiPort']);
        }

        if (array_key_exists('apiVersion', $options)) {
            $this->apiVersion = strval($options['apiVersion']);
        }

        if (array_key_exists('userAgent', $options)) {
            $this->userAgent = strval($options['userAgent']);
        }

        return $this;
    }

    // }}}
    // {{{ isSpam()

    /**
     * Checks whether or not a comment is spam
     *
     * When checking if a comment is spam, it is possible to set the required
     * fields, and several other server-related fields automatically. When
     * the server-related fields are set automatically, usually only the
     * content-related fields of the comment need to be specified manually.
     *
     *
     * Note: Only auto-set server-related fields on comments checked in
     * real-time. If you check comments using an external system, you run the
     * risk of submitting your own server information as spam. Instead, save
     * the server-related fields in the database and set them on the comment
     * using {@link Services_Akismet2_Comment::setField()}.
     *
     * @param Services_Akismet2_Comment|array $comment             the comment
     *        to check.
     * @param boolean                         $autoSetServerFields whether or
     *        not to automatically set server-related fields. Defaults to false.
     *
     * @return boolean true if the comment is spam and false if it is not.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet2_InvalidCommentException if the specified
     *         comment is missing required fields.
     *
     * @throws Services_Akismet2_InvalidApiKeyException if the provided
     *         API key is not valid.
     *
     * @throws InvalidArgumentException if the provided comment is neither an
     *         array not an instanceof Services_Akismet2_Comment.
     */
    public function isSpam($comment, $autoSetServerFields = false)
    {
        if (is_array($comment)) {
            $comment = new Services_Akismet2_Comment($comment);
        }

        if (!($comment instanceof Services_Akismet2_Comment)) {
            throw new InvalidArgumentException('Comment must be either an ' .
                'array or an instance of Services_Akismet2_Comment.');
        }

        $this->validateApiKey();

        $params         = $comment->getPostParameters($autoSetServerFields);
        $params['blog'] = $this->blogUri;

        $response = $this->sendRequest('comment-check', $params, $this->apiKey);

        return ($response == 'true');
    }

    // }}}
    // {{{ submitSpam()

    /**
     * Submits a comment as an unchecked spam to the Akismet server
     *
     * Use this method to submit comments that are spam but are not detected
     * by Akismet.
     *
     * @param Services_Akismet2_Comment|array $comment the comment to submit
     *                                                 as spam.
     *
     * @return Services_Akismet2 the Akismet API object.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet2_InvalidCommentException if the specified
     *         comment is missing required fields.
     *
     * @throws Services_Akismet2_InvalidApiKeyException if the provided
     *         API key is not valid.
     *
     * @throws InvalidArgumentException if the provided comment is neither an
     *         array not an instanceof Services_Akismet2_Comment.
     */
    public function submitSpam($comment)
    {
        if (is_array($comment)) {
            $comment = new Services_Akismet2_Comment($comment);
        }

        if (!($comment instanceof Services_Akismet2_Comment)) {
            throw new InvalidArgumentException('Comment must be either an ' .
                'array or an instance of Services_Akismet2_Comment.');
        }

        $this->validateApiKey();

        $params         = $comment->getPostParameters();
        $params['blog'] = $this->blogUri;

        $this->sendRequest('submit-spam', $params, $this->apiKey);

        return $this;
    }

    // }}}
    // {{{ submitFalsePositive()

    /**
     * Submits a false-positive comment to the Akismet server
     *
     * Use this method to submit comments that are detected as spam but are not
     * actually spam.
     *
     * @param Services_Akismet2_Comment|array $comment the comment that is
     *                                                 <em>not</em> spam.
     *
     * @return Services_Akismet2 the Akismet API object.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet2_InvalidCommentException if the specified
     *         comment is missing required fields.
     *
     * @throws Services_Akismet2_InvalidApiKeyException if the provided
     *         API key is not valid.
     *
     * @throws InvalidArgumentException if the provided comment is neither an
     *         array not an instanceof Services_Akismet2_Comment.
     */
    public function submitFalsePositive($comment)
    {
        if (is_array($comment)) {
            $comment = new Services_Akismet2_Comment($comment);
        }

        if (!($comment instanceof Services_Akismet2_Comment)) {
            throw new InvalidArgumentException('Comment must be either an ' .
                'array or an instance of Services_Akismet2_Comment.');
        }

        $this->validateApiKey();

        $params         = $comment->getPostParameters();
        $params['blog'] = $this->blogUri;

        $this->sendRequest('submit-ham', $params, $this->apiKey);

        return $this;
    }

    // }}}
    // {{{ setRequest()

    /**
     * Sets the HTTP request object to use
     *
     * @param HTTP_Request2 $request the HTTP request object to use.
     *
     * @return Services_Akismet2 the Akismet API object.
     */
    public function setRequest(HTTP_Request2 $request)
    {
        $this->request = $request;

        return $this;
    }

    // }}}
    // {{{ sendRequest()

    /**
     * Calls a method on the Akismet API server using a HTTP POST request
     *
     * @param string $methodName the name of the Akismet method to call.
     * @param array  $params     optional. Array of request parameters for the
     *                           Akismet call.
     * @param string $apiKey     optional. The API key to use. Not required if
     *                           verifying an API key. Required for all other
     *                           request types.
     *
     * @return string the HTTP response content.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     */
    protected function sendRequest($methodName, array $params = array(),
        $apiKey = ''
    ) {
        if ($apiKey == '') {
            $host = $this->apiServer;
        } else {
            $host = $apiKey . '.' . $this->apiServer;
        }

        $url = sprintf(
            'http://%s:%s/%s/%s',
            $host,
            $this->apiPort,
            $this->apiVersion,
            $methodName
        );

        try {
            /*
             * Note: The request object is only used as a template to create
             * other request objects. This prevents one API call from affecting
             * the state of the HTTP request object for subsequent API calls.
             */
            $request = clone $this->request;
            $request->setUrl($url);
            $request->setHeader('User-Agent', $this->getUserAgent());
            $request->setMethod(HTTP_Request2::METHOD_POST);
            $request->addPostParameter($params);

            $response = $request->send();
        } catch (HTTP_Request2_Exception $e) {
            $message = 'Error in request to Akismet API server "' .
                $this->apiServer . '": ' . $e->getMessage();

            throw new Services_Akismet2_HttpException($message, $e->getCode(),
                $request);
        }

        return $response->getBody();
    }

    // }}}
    // {{{ validateApiKey()

    /**
     * Validates the API key before performing a request
     *
     * @return void
     *
     * @throws Services_Akismet2_InvalidApiKeyException if the provided
     *         API key is not valid.
     */
    protected function validateApiKey()
    {
        // only check if the key is valid once
        if ($this->apiKeyIsValid === null) {
            $this->apiKeyIsValid = $this->isApiKeyValid($this->apiKey);
        }

        // make sure the API key is valid
        if (!$this->apiKeyIsValid) {
            throw new Services_Akismet2_InvalidApiKeyException('The specified ' .
                'API key is not valid. Key used was: "' .
                $this->apiKey . '".', 0, $this->apiKey);
        }
    }

    // }}}
    // {{{ isApiKeyValid()

    /**
     * Checks with the Akismet server to determine if an API key is
     * valid
     *
     * @param string $key the API key to check.
     *
     * @return boolean true if the key is valid and false if it is not valid.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     */
    protected function isApiKeyValid($key)
    {
        $params = array(
            'key'  => $key,
            'blog' => $this->blogUri
        );

        $response = $this->sendRequest('verify-key', $params);
        return ($response == 'valid');
    }

    // }}}
    // {{{ getUserAgent()

    /**
     * Gets the HTTP user-agent used to make Akismet requests
     *
     * @return string the HTTP user-agent used to make Akismet requests.
     *
     * @see Services_Akismet2::$userAgent
     * @see Services_Akismet2::setConfig()
     */
    protected function getUserAgent()
    {
        if ($this->userAgent == '') {
            $userAgent = sprintf(
                '@name@/@api-version@ | Akismet/%s',
                $this->apiVersion
            );
        } else {
            $userAgent = $this->userAgent;
        }

        return $userAgent;
    }

    // }}}
}

// }}}

?>
