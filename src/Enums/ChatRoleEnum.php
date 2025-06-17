<?php

namespace LA87\AIPromptBuilder\Enums;

enum ChatRoleEnum: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';
    case Tool = 'tool';
}
