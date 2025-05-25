<?php

namespace LA87\AIPromptBuilder\Services;

use LA87\AIPromptBuilder\DTOs\BatchRequestDTO;
use OpenAI;

class AiBatchService
{
    protected OpenAI\Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = OpenAI::client($apiKey);
    }

    public function createBatchFile(array $requests, string $fileName = null): string
    {
        $fileName = $fileName ?? 'batch_' . uniqid() . '.jsonl';
        $filePath = storage_path("app/$fileName");
        $handle = fopen($filePath, 'w');

        foreach ($requests as $request) {
            if (! $request instanceof BatchRequestDTO) {
                throw new \Exception("Each request must be instance of BatchRequestDTO");
            }
            fwrite($handle, $request->toJson() . "\n");
        }
        fclose($handle);

        return $filePath;
    }

    public function uploadBatchFile(string $localPath): string
    {
        $file = $this->client->files()->upload([
            'file' => fopen($localPath, 'r'),
            'purpose' => 'batch',
        ]);
        return $file->id;
    }

    public function createBatch(string $fileId, string $endpoint = '/v1/chat/completions')
    {
        return $this->client->batches()->create([
            'input_file_id' => $fileId,
            'endpoint' => $endpoint,
            'completion_window' => '24h',
        ]);
    }

    public function retrieveBatch(string $batchId)
    {
        return $this->client->batches()->retrieve($batchId);
    }

    public function cancelBatch(string $batchId)
    {
        return $this->client->batches()->cancel($batchId);
    }

    public function listBatches(): array
    {
        $batches = [];
        foreach ($this->client->batches()->list() as $batch) {
            $batches[] = $batch;
        }
        return $batches;
    }

    /**
     * Downloads and parses a batch result file, returning an array keyed by custom_id.
     *
     * @param string $fileId
     * @return array [custom_id => result]
     * @throws \Exception
     */
    public function getBatchResultsByCustomId(string $fileId): array
    {
        $fileContent = $this->downloadBatchFileContent($fileId);
        return static::parseBatchResultsByCustomId($fileContent);
    }

    /**
     * Downloads the raw content of a batch file by ID.
     * (Separated for clarity, but can be merged.)
     */
    public function downloadBatchFileContent(string $fileId): string
    {
        $fileObject = $this->client->files()->download($fileId);
        // This may be a stream or content; adapt if necessary
        if (is_string($fileObject)) {
            return $fileObject;
        }
        // Some SDKs return a Guzzle stream or response object
        if (is_resource($fileObject)) {
            return stream_get_contents($fileObject);
        }
        if (isset($fileObject['url'])) {
            return file_get_contents($fileObject['url']);
        }
        throw new \Exception("Could not retrieve file content for $fileId");
    }

    /**
     * Parses batch results file and returns an array keyed by custom_id.
     *
     * @param string $fileContent
     * @return array [custom_id => result]
     */
    public static function parseBatchResultsByCustomId(string $fileContent): array
    {
        $results = [];
        $lines = explode("\n", $fileContent);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;

            $data = json_decode($line, true);
            if (!isset($data['custom_id'])) {
                continue;
            }

            $body = $data['response']['body'] ?? $data['response'] ?? null;

            // Embedding result: "object" => "list", "data"[0]["object"] => "embedding"
            if (
                is_array($body)
                && isset($body['object'], $body['data'][0]['object'])
                && $body['object'] === 'list'
                && $body['data'][0]['object'] === 'embedding'
            ) {
                $embeddingData = $body['data'][0];
                $results[$data['custom_id']] = [
                    'vector' => $embeddingData['embedding'] ?? null,
                    'model' => $body['model'] ?? null,
                    'usage' => $body['usage'] ?? null,
                ];
            } else {
                // Fallback: return body as-is for other response types
                $results[$data['custom_id']] = $body;
            }
        }
        return $results;
    }

}
