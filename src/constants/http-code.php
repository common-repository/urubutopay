<?php
if (!defined('ABSPATH')) {
    exit;
}

define('URUBUTOPAY_HTTP_CODE', array(
    'BAD_REQUEST' => 400,
    'OK' => 200,
    'CREATED' => 201,
    'UNAUTHORIZED' => 401,
    'FORBIDDEN' => 403,
    'NOT_FOUND' => 404,
    'CONFLICT' => 409,
    'SERVICE_UNAVAILABLE' => 503,
    'INTERNAL_SERVER_ERROR' => 500
));
