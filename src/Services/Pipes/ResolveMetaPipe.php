<?php
namespace LA87\AIPromptBuilder\Services\Pipes;

use Closure;
use LA87\AIPromptBuilder\DTOs\PromptConfigDTO;

class ResolveMetaPipe
{
    public function handle(PromptConfigDTO $config, Closure $next)
    {
        $config->prompt = $this->resolveMeta($config->prompt, $config->meta);
        $config->role = $this->resolveMeta($config->role, $config->meta);

        return $next($config);
    }

    private function resolveMeta(string $str, array $meta): string
    {
        $placeholders = array_map(fn($key) => "{{{$key}}}", array_keys($meta));
        $values = array_values($meta);

        return str_replace($placeholders, $values, $str);
    }
}
