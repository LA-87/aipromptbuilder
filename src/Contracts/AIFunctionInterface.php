<?php

namespace LA87\AIPromptBuilder\Contracts;

use stdClass;

interface AIFunctionInterface
{
    public function getSchema(): array;
    public function getSchemaForToolChoice(): array;
}
