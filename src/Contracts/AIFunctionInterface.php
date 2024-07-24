<?php

namespace LA87\AIPromptBuilder\Contracts;

use stdClass;

interface AIFunctionInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function getParametersSchema(): array;

    public function getMustBeCalled(): bool;

    public function execute(stdClass|null $arguments = null);
}
