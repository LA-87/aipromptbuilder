<?php

namespace LA87\AIPromptBuilder\Contracts;

interface AIFunctionInterface
{
    public function getDescription(): string;
    public function getParams(): array;
    public function getSchema(): array;
    public function getSchemaForToolChoice(): array;
}
