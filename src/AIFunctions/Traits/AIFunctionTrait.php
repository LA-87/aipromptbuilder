<?php

namespace LA87\AIPromptBuilder\AIFunctions\Traits;

trait AIFunctionTrait
{
    public bool $required = false;

    public function getName(): string
    {
        $reflectionClass = new \ReflectionClass($this);

        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $docComment = $method->getDocComment();

            if ($this->hasAiFunctionAnnotation($docComment)) {
                return $method->getName();
            }
        }

        throw new \Exception('No method with @aiFunction annotation found');
    }

    public function getSchema(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $functionsSchema = [];

        foreach ($methods as $method) {
            $docComment = $method->getDocComment();
            $methodName = $method->getName();
            $methodDescription = $this->extractMethodDescription($docComment);
            $isAiFunction = $this->hasAiFunctionAnnotation($docComment);

            if (!$isAiFunction) continue;

            $parameters = $method->getParameters();
            $properties = [];
            $requiredParameters = [];

            foreach ($parameters as $parameter) {
                $paramName = $parameter->getName();
                $paramType = $parameter->getType();
                $typeName = $paramType ? $paramType->getName() : 'string';

                $isNullable = $paramType && $paramType->allowsNull();
                $paramDescription = $this->extractParameterDescription($docComment, $paramName);

                $parameterSchema = [
                    'type' => $this->mapTypeToSchema($typeName),
                    'description' => $paramDescription
                ];

                if ($isNullable) {
                    $parameterSchema['type'] = [$parameterSchema['type'], 'null'];
                }

                if (!$parameter->isOptional()) {
                    $requiredParameters[] = $paramName;
                }

                $properties[$paramName] = $parameterSchema;
            }

            $functionsSchema[] = [
                'type' => 'function',
                'function' => [
                    'name' => $methodName,
                    'description' => $methodDescription,
                    'parameters' => [
                        'type' => 'object',
                        'properties' => $properties,
                        'required' => $requiredParameters,
                        'additionalProperties' => false
                    ],
                ]
            ];
        }

        if (count($functionsSchema) > 1) {
            throw new \Exception('Multiple AIFunctions found');
        }

        return $functionsSchema[0] ?? [];
    }

    public function getSchemaForToolChoice(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $functionsSchema = [];

        foreach ($methods as $method) {
            $docComment = $method->getDocComment();
            $methodName = $method->getName();
            $isAiFunction = $this->hasAiFunctionAnnotation($docComment);

            if (!$isAiFunction) continue;

            $functionsSchema[] = [
                'type' => 'function',
                'function' => [
                    'name' => $methodName,
                ]
            ];
        }

        return $functionsSchema[0] ?? [];
    }

    private function extractMethodDescription(?string $docComment): string
    {
        if ($docComment === false) return '';
        // Match the @description directive for easier parsing
        preg_match('/@description\s+(.+)/', $docComment, $matches);
        return $matches[1] ?? '';
    }

    private function extractParameterDescription(?string $docComment, string $paramName): string
    {
        if ($docComment === false) return '';
        preg_match('/@param\s+\S+\s+\$' . $paramName . '\s+(.+)/', $docComment, $matches);
        return $matches[1] ?? "No description available for $paramName";
    }

    private function hasAiFunctionAnnotation(?string $docComment): bool
    {
        return $docComment !== false && strpos(mb_strtolower($docComment), '@aifunction') !== false;
    }

    private function mapTypeToSchema(string $typeName): string
    {
        return match ($typeName) {
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            'string' => 'string',
            default => 'string'
        };
    }
}
