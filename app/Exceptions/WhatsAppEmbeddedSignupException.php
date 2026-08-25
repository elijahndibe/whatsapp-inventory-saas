<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The message on this exception is always safe to show directly to the
 * store owner — see WhatsAppCloudApiService::throwFor(), which logs the
 * raw Graph API response server-side and only ever throws a pre-written,
 * user-friendly string.
 */
class WhatsAppEmbeddedSignupException extends RuntimeException {}
