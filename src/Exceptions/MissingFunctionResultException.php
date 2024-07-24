<?php

namespace LA87\AIPromptBuilder\Exceptions;


use Exception;

class MissingFunctionResultException extends Exception
{
    protected $message = 'Missing function result';
}
