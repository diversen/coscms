<?php

/**
 * controller file for error/display/404
 *
 * @package    error
 */
header("HTTP/1.1 404 Not Found");
template::setTitle(lang::translate('Error 404: Page was not found'));
echo '<p>' . lang::translate('Error 404: Page was not found') . '</p>';
