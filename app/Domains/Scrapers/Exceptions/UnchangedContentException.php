<?php

namespace App\Domains\Scrapers\Exceptions;

use RuntimeException;

class UnchangedContentException extends RuntimeException
{
    // Custom exception thrown when a conditional GET/HEAD request returns 304 Not Modified.
}
