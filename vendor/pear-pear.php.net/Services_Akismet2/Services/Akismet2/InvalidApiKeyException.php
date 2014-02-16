<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class definition for exception thrown when an invalid Wordpress API key is
 * used
 *
 * Services_Akismet2 is a package to use Akismet spam-filtering from PHP
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2008 silverorange
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
 * @copyright 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   CVS: $Id: InvalidApiKeyException.php 272137 2008-12-28 20:26:58Z gauthierm $
 * @link      http://pear.php.net/package/Services_Akismet2
 */

/**
 * PEAR Exception handler and base class
 */
require_once 'PEAR/Exception.php';

// {{{ class Services_Akismet2_InvalidApiKeyException

/**
 * This exception is thrown when an invalid API key is used
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2_InvalidApiKeyException extends PEAR_Exception
{
    // {{{ private class properties

    /**
     * The invalid API key
     *
     * @var string
     */
    private $_apiKey = '';

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid API key exception
     *
     * @param string $message an error message.
     * @param int    $code    a user defined error code.
     * @param string $apiKey  the invalid API key.
     */
    public function __construct($message, $code = 0, $apiKey = '')
    {
        $this->_apiKey = $apiKey;
        parent::__construct($message, $code);
    }

    // }}}
    // {{{ getApiKey()

    /**
     * Returns the invalid API key
     *
     * @return string the invalid API key.
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    // }}}
}

// }}}

?>
