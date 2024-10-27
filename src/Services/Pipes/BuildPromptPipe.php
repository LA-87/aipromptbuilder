<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;

class BuildPromptPipe
{
    public function handle(PromptConfigDTO $config, Closure $next)
    {
//        return $next($user);
    }
}
