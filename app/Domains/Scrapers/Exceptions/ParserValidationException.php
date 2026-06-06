<?php

namespace App\Domains\Scrapers\Exceptions;

use RuntimeException;

class ParserValidationException extends RuntimeException
{
    // Custom exception thrown when scraper selectors fail to extract data
}
