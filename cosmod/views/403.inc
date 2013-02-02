<?php

/**
 * controller file for error/display/404
 *
 * @package    error
 */
header("HTTP/1.1 403 Forbidden");
template::setTitle(lang::translate('Error 403: Access Denied'));
echo '<p>' . lang::translate('Error 403: Access Denied') . '</p>';
