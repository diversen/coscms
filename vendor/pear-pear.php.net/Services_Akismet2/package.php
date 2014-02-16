<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This is the package.xml generator for Services_Akismet2
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2008 silverorange
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
 * @link      http://pear.php.net/package/Services_Akismet2
 */

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version     = '0.3.0';
$api_state       = 'alpha';

$release_version = '0.3.0';
$release_state   = 'alpha';
$release_notes   =
    " * Fixed Bug #15567: Exception is thrown when referrer is not set in " .
    "comment.\n" .
    " * Fixed Req #15569: Ability to set a custom user-agent.\n" .
    " * Fixed Bug #15571: Don't always include server-related fields. There " .
    "is a new parameter on the isSpam() method to auto-set server-related " .
    "fields.\n" .
    " * API doc cleanups.\n" .
    " * Wrote end-user documentation (PEAR manual is not up-to-date yet, but " .
    "source files are in CVS).\n" .
    " * More complete unit tests.\n" .
    " * Fix bug in 0.2.0 that caused incorrect host to be used to requests " .
    "requiring an API key.\n" .
    " * Allow specifying comment as an array rather than an object.\n" .
    " * Make \$fields property of comment class protected for easier " .
    "sub-classing of the comment class.\n" .
    " * PHPCS cleanups.\n";

$description =
    "This package provides an object-oriented interface to the Akismet REST " .
    "API. The Akismet API is used to detect and to filter spam comments " .
    "posted on weblogs.\n\n" .
    "There are several anti-spam service providers that use the Akismet API. " .
    "To use the API, you will need an API key from such a provider. Example " .
    "providers include Wordpress (http://wordpress.com) and TypePad " .
    "(http://antispam.typepad.com).\n\n" .
    "Most services are free for personal or low-volume use, and offer " .
    "licensing for commercial or high-volume applications.\n\n" .
    "This package is derived from the miPHP Akismet class written by Bret " .
    "Kuhns for use in PHP 4. This package requires PHP 5.2.1.";

$package = new PEAR_PackageFileManager2();

$package->setOptions(array(
    'filelistgenerator'     => 'svn',
    'simpleoutput'          => true,
    'baseinstalldir'        => '/',
    'packagedirectory'      => './',
    'dir_roles'             => array(
        'Services'          => 'php',
        'Services/Akismet2' => 'php',
        'tests'             => 'test'
    ),
    'ignore'                => array(
        'package.php',
    ),
));

$package->setPackage('Services_Akismet2');
$package->setExtends('Services_Akismet');
$package->setSummary('PHP client for the Akismet REST API');
$package->setDescription($description);
$package->setChannel('pear.php.net');
$package->setPackageType('php');
$package->setLicense(
    'MIT',
    'http://www.opensource.org/licenses/mit-license.html'
);

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer(
    'lead',
    'gauthierm',
    'Mike Gauthier',
    'mike@silverorange.com'
);

$package->addReplacement(
    'Services/Akismet.php',
    'package-info',
    '@api-version@',
    'api-version'
);

$package->addReplacement(
    'Services/Akismet.php',
    'package-info',
    '@name@',
    'name'
);

$package->setPhpDep('5.2.1');

$package->addPackageDepWithChannel(
    'required',
    'HTTP_Request2',
    'pear.php.net',
    '0.1.0'
);

$package->setPearinstallerDep('1.4.0');
$package->generateContents();

if (   isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

?>
