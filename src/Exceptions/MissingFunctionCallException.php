<?php

namespace LA87\AIPromptBuilder\Exceptions;

use Exception;

class MissingFunctionCallException extends Exception
{
    protected $message = 'Missing function call in response';
}
