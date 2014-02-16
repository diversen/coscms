<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class definition for exception thrown when an invalid comment is used
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
 * @version   CVS: $Id: InvalidCommentException.php 273499 2009-01-14 04:21:21Z gauthierm $
 * @link      http://pear.php.net/package/Services_Akismet2
 */

/**
 * PEAR Exception handler and base class
 */
require_once 'PEAR/Exception.php';

/**
 * Akismet comment class
 */
require_once 'Services/Akismet2/Comment.php';

// {{{ class Services_Akismet2_InvalidApiKeyException

/**
 * This exception is thrown when an invalid comment is used
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 * @see       Services_Akismet2_Comment
 */
class Services_Akismet2_InvalidCommentException extends PEAR_Exception
{
    // {{{ private class properties

    /**
     * The invalid comment
     *
     * @var Services_Akismet2_Comment
     */
    private $_comment;

    // }}}
    // {{{ __construct()

    /**
     * Creates a new invalid comment exception
     *
     * @param string                    $message the error message.
     * @param integer                   $code    the error code.
     * @param Services_Akismet2_Comment $comment the invalid comment.
     */
    public function __construct($message, $code,
        Services_Akismet2_Comment $comment
    ) {
        parent::__construct($message, $code);
        $this->_comment = $comment;
    }

    // }}}
    // {{{ public function getComment()

    /**
     * Gets the invalid comment
     *
     * @return Services_Akismet2_Comment the invalid comment.
     */
    public function getComment()
    {
        return $this->_comment;
    }

    // }}}
}

// }}}

?>
