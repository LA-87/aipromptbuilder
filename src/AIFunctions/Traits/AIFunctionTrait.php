<?php

namespace LA87\AIPromptBuilder\AIFunctions\Traits;

trait AIFunctionTrait
{
    public bool $required = false;

    public function getName(): string
    {
        $reflectionClass = new \ReflectionClass($this);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodName = null;

        foreach ($methods as $method) {
            $docComment = $method->getDocComment();

            if ($this->hasAiFunctionAnnotation($docComment)) {
                $methodName = $method->getName();
            }
        }

        if (!$methodName) {
            throw new \Exception('Missing main method');
        }

        return $methodName;
    }

    public function getSchema(): array
    {
        $methodName = $this->getName();

        $params = $this->getParams();

        $requiredParamNames = array_keys(
            array_filter(
                $params,
                fn($param) => $param['required'] === true // Only include required params
            )
        );

        foreach ($params as $key => &$param) {
            if (isset($param['required'])) {
                unset($param['required']);
            }
        }

        return ['type' => 'function',
            'function' => [
                'name' => $methodName,
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => $params,
                    'required' => $requiredParamNames,
                    'additionalProperties' => false
                ],
            ]
        ];
    }

    public function getSchemaForToolChoice(): array
    {
        $methodName = $this->getName();

        return [
            'type' => 'function',
            'function' => [
                'name' => $methodName,
            ]
        ];
    }

    private function hasAiFunctionAnnotation(?string $docComment): bool
    {
        return $docComment !== false && strpos(mb_strtolower($docComment), '@aifunction') !== false;
    }
}
