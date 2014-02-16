<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains a class representing a comment on a weblog post
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
 * @version   CVS: $Id: Comment.php 273554 2009-01-14 19:27:56Z gauthierm $
 * @link      http://pear.php.net/package/Services_Akismet2
 */

/**
 * Exception thrown if an invalid comment is used.
 */
require_once 'Services/Akismet2/InvalidCommentException.php';

// {{{ class Services_Akismet2_Comment

/**
 * Akismet comment
 *
 * Example usage using initial array of values:
 *
 * <code>
 * $comment = new Services_Akismet2_Comment(array(
 *     'comment_author'       => 'Test Author',
 *     'comment_author_email' => 'test@example.com',
 *     'comment_author_url'   => 'http://example.com/',
 *     'comment_content'      => 'Hello, World!'
 * ));
 *
 * echo $comment;
 * </code>
 *
 * Example usage using fluent interface:
 *
 * <code>
 * $comment = new Services_Akismet2_Comment();
 * $comment->setAuthor('Test Author')
 *         ->setAuthorEmail('test@example.com')
 *         ->setAuthorUri('http://example.com/')
 *         ->setContent('Hello, World!');
 * ));
 *
 * echo $comment;
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
class Services_Akismet2_Comment
{
    // {{{ protected properties

    /**
     * Fields of this comment
     *
     * @var array
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    protected $fields = array();

    // }}}
    // {{{ private properties

    /**
     * Whitelist of allowed $_SERVER variables to send to Akismet
     *
     * A whitelist is used to ensure the privacy of people submitting comments.
     * Akismet recommends as many $_SERVER variables as possible be sent;
     * however, many $_SERVER variables contain sensitive data, and are not
     * relevant for spam checks. This subset of fields does not contain
     * sensitive information but does contain enough information to identify
     * a unique client/server sending spam.
     *
     * The $_SERVER variables are taken from the current request.
     *
     * @var array
     */
    private static $_allowedServerVars = array(
        'SCRIPT_URI',
        'HTTP_HOST',
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_CHARSET',
        'HTTP_KEEP_ALIVE',
        'HTTP_CONNECTION',
        'HTTP_CACHE_CONTROL',
        'HTTP_PRAGMA',
        'HTTP_DATE',
        'HTTP_EXPECT',
        'HTTP_MAX_FORWARDS',
        'HTTP_RANGE',
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'SERVER_SIGNATURE',
        'SERVER_SOFTWARE',
        'SERVER_NAME',
        'SERVER_ADDR',
        'SERVER_PORT',
        'REMOTE_PORT',
        'GATEWAY_INTERFACE',
        'SERVER_PROTOCOL',
        'REQUEST_METHOD',
        'QUERY_STRING',
        'REQUEST_URI',
        'SCRIPT_NAME',
        'REQUEST_TIME'
    );

    /**
     * Fields that are required for a comment
     *
     * @var array
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    private static $_requiredFields = array(
        'user_ip',
        'user_agent'
    );

    // }}}
    // {{{ __construct()

    /**
     * Creates a new comment
     *
     * Comments can be initialized from an array of named values. Available
     * names are:
     *
     * - <kbd>string comment_author</kbd>       - the name of the comment
     *                                            author.
     * - <kbd>string comment_author_email</kbd> - the email address of the
     *                                            comment author.
     * - <kbd>string comment_author_url</kbd>   - a link provided by the
     *                                            comment author.
     * - <kbd>string comment_content</kbd>      - the content of the comment.
     * - <kbd>string comment_type</kbd>         - the comment type. Either
     *                                            <kbd>comment</kbd>,
     *                                            <kbd>trackback</kbd>,
     *                                            <kbd>pingback</kbd>, or a
     *                                            a made-up value.
     * - <kbd>string permalink</kbd>            - permalink of the article to
     *                                            which the comment is being
     *                                            added.
     * - <kbd>string referrer</kbd>             - HTTP referrer. If not
     *                                            specified, the HTTP referrer
     *                                            of the current request is
     *                                            used.
     * - <kbd>string user_ip</kbd>              - IP address from which the
     *                                            comment was submitted. If not
     *                                            specified the remote IP
     *                                            address of the current
     *                                            request is used.
     * - <kbd>string user_agent</kbd>           - the HTTP user agent used to
     *                                            submit the comment. If not
     *                                            specified, the user agent of
     *                                            the current request is used.
     *
     * If not specified, the <kbd>user_ip</kbd>, <kbd>user_agent</kbd> and
     * <kbd>referrer</kbd> fields are defaulted to the current request values
     * if possible. They may be changed either by specifying them here or by
     * using the appropriate setter method.
     *
     * Field names not included in the above list are allowed. The Akismet API
     * can make use of any extra identifying information provided.
     *
     * @param array $fields optional. An array of initial fields.
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    public function __construct(array $fields = array())
    {
        // set default values from request
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $this->fields['user_ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $this->fields['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $this->fields['referrer'] = $_SERVER['HTTP_REFERER'];
        }

        // set from fields
        $this->setFields($fields);
    }

    // }}}
    // {{{ __toString()

    /**
     * Gets a string representation of this comment
     *
     * This is useful for debugging. All the set fields of this comment are
     * returned.
     *
     * @return string a string representation of this comment.
     */
    public function __toString()
    {
        $string = "Fields:\n\n";
        foreach ($this->fields as $key => $value) {
            $string .= "\t" . $key . " => " . $value . "\n";
        }

        $missingFields = array();
        foreach (self::$_requiredFields as $field) {
            if (!array_key_exists($field, $this->fields)) {
                $missingFields[] = $field;
            }
        }

        if (count($missingFields) > 0) {
            $string .= "\n\tMissing Required Fields:\n\n";
            foreach ($missingFields as $field) {
                $string .= "\t" . $field . "\n";
            }
        }

        return $string;
    }

    // }}}
    // {{{ getPostParameters()

    /**
     * Gets the fields of this comment as an array of name-value pairs for use
     * in an Akismet API method
     *
     * @param boolean $autoSetServerFields optional. Whether or not to
     *                                     automatically set server-related
     *                                     fields. Defaults to false.
     *
     * @return array the fields of this comment as an array of name-value pairs
     *                suitable for usage in an Akismet API method.
     *
     * @throws Services_Akismet2_InvalidCommentException if this comment is
     *         missing required fields.
     *
     * @see http://akismet.com/development/api/#comment-check
     */
    public function getPostParameters($autoSetServerFields = false)
    {
        $values = array();

        foreach ($this->fields as $key => $value) {
            $values[$key] = $value;
        }

        if ($autoSetServerFields) {
            foreach (self::$_allowedServerVars as $key) {
                if (array_key_exists($key, $_SERVER)) {
                    $value = $_SERVER[$key];
                    $values[$key] = $value;
                }
            }
        }

        // make sure all required fields are set
        foreach (self::$_requiredFields as $field) {
            if (!array_key_exists($field, $values)) {
                throw new Services_Akismet2_InvalidCommentException('Comment ' .
                    'is missing required field: "' . $field . '".', 0, $this);
            }
        }

        return $values;
    }

    // }}}
    // {{{ setField()

    /**
     * Sets a field of this comment
     *
     * Common fields as described in the Akismet API have setter methods
     * provided. This method may be used to set custom fields not covered by
     * the common field setter methods.
     *
     * @param string $name  the name of the field.
     * @param string $value the value of the field.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setField($name, $value)
    {
        $this->fields[strval($name)] = strval($value);

        return $this;
    }

    // }}}
    // {{{ setFields()

    /**
     * Sets multiple fields of this comment
     *
     * Note: Common fields as described in the Akismet API have setter methods
     * provided. The setter methods may be optionally used instead.
     *
     * @param array $fields an associative array of name-value pairs. The
     *                      field name is the array key and the field value is
     *                      the array value. See
     *                      {@link Services_Akismet2_Comment::__construct()}
     *                      for a list of common field names.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $name => $value) {
            $this->setField($name, $value);
        }

        return $this;
    }

    // }}}
    // {{{ setType()

    /**
     * Sets the type of this comment
     *
     * @param string $type the type of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setType($type)
    {
        return $this->setField('comment_type', $type);
    }

    // }}}
    // {{{ setAuthor()

    /**
     * Sets the author of this comment
     *
     * @param string $author the author of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setAuthor($author)
    {
        return $this->setField('comment_author', $author);
    }

    // }}}
    // {{{ setAuthorEmail()

    /**
     * Sets the email address of the author of this comment
     *
     * @param string $email the email address of the author of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setAuthorEmail($email)
    {
        return $this->setField('comment_author_email', $email);
    }

    // }}}
    // {{{ setAuthorUrl()

    /**
     * Sets the URI of the author of this comment
     *
     * @param string $url the URI of the author of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setAuthorUrl($url)
    {
        return $this->setField('comment_author_url', $url);
    }

    // }}}
    // {{{ setContent()

    /**
     * Sets the content of this comment
     *
     * @param string $content the content of this comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setContent($content)
    {
        return $this->setField('comment_content', $content);
    }

    // }}}
    // {{{ setPostPermalink()

    /**
     * Sets the permalink of the post to which this comment is being added
     *
     * A {@link http://en.wikipedia.org/wiki/Permalink permalink} is a URI that
     * points to a specific weblog post and does not change over time.
     * Permalinks are intended to prevent link rot. Akismet does not require
     * the permalink field but can use it to improve spam detection accuracy.
     *
     * @param string $url the permalink of the post to which this comment is
     *                    being added.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setPostPermalink($url)
    {
        return $this->setField('permalink', $url);
    }

    // }}}
    // {{{ setUserIp()

    /**
     * Sets the IP address of the user posting this comment
     *
     * The IP address is automatically set to the IP address from the current
     * page request when this comment is created. Use this method to set the
     * IP address to something different or if the current request does not have
     * an IP address set.
     *
     * @param string $ipAddress the IP address of the user posting this
     *                          comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setUserIp($ipAddress)
    {
        return $this->setField('user_ip', $ipAddress);
    }

    // }}}
    // {{{ setUserAgent()

    /**
     * Sets the user agent of the user posting this comment
     *
     * The user agent is automatically set to the user agent from the current
     * page request when this comment is created. Use this method to set the
     * user agent to something different or if the current request does not
     * have a user agent set.
     *
     * @param string $userAgent the user agent of the user posting this
     *                          comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setUserAgent($userAgent)
    {
        return $this->setField('user_agent', $userAgent);
    }

    // }}}
    // {{{ setHttpReferrer()

    /**
     * Sets the HTTP referer of the user posting this comment
     *
     * The HTTP referer is automatically set to the HTTP referer from the
     * current page request when this comment is created. Use this method to set
     * the HTTP referer to something different or if the current request does
     * not have a HTTP referer set.
     *
     * @param string $httpReferrer the HTTP referer of the user posting this
     *                             comment.
     *
     * @return Services_Akismet2_Comment the comment object.
     */
    public function setHttpReferrer($httpReferrer)
    {
        return $this->setField('referrer', $httpReferrer);
    }

    // }}}
}

// }}}

?>
