<?php

namespace App\Domains\Scrapers\Exceptions;

use RuntimeException;

class RateLimitExceededException extends RuntimeException
{
    // Custom exception thrown when rate-limiting rules for a domain are hit.
}
