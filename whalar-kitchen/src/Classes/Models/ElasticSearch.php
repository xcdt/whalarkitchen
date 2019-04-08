<?php
/**
 * Created by PhpStorm.
 * User: Francisco.fernandez
 * Date: 4/8/2019
 * Time: 7:44 PM
 */

namespace Kitchen\Models;

use Elasticsearch\ClientBuilder;

class ElasticSearch
{


    private $logger;

    protected $index, $type, $settings, $client;

    private $result;

    public function __construct($settings) {
        $this->settings = $settings;
        $this->initLocal();

        $this->index='cookbook';
        $this->type = 'recipe';

        //Checking if the index exists, if not we create it and put the mapping for recipe into it
        if (!$this->checkIfIndexExists($this->index)) {
            if($this->createIndex($this->index)) {
                if($this->createOrModifyType($this->index, $this->type)) {
                }
                else{
                    throw new Exception("Error creating the type");
                }
            }
            else{
                throw new Exception("Error creating the index");
            }
        }
    }

    public function initLocal() {
        $hosts = [[
            'host' => $this->settings['host'],
            'port' => $this->settings['port'],
            'scheme' => $this->settings['scheme'],
            'user' => $this->settings['user'],
            'pass' => $this->settings['pass']
        ]];

        $this->client = ClientBuilder::create()->setHosts($hosts)->setRetries(3)->build();
    }

    /**
     * Bulk indexing in Cookbook
     *
     * @param string $params
     * @return void
     */
    protected function bulkIndexing($params, $operation) {
        $response = $this->client->bulk($params);

        if (isset($response['items'])) {
            foreach ($response['items'] as $item) {
                if (!in_array($item[$operation]['result'], ["created", "updated"])) {
                    $error = 'Bulk indexing error (1)';
                    $error .= ' / ' . $item['index']['status'];
                    $error .= ' / ' . $item['index']['error']['type'];
                    $error .= ' / ' . $item['index']['error']['reason'];
                    //throw new Exception($error);
                }
                $this->result[$item[$operation]['result']]++;
            }
        } else {
            $error = 'Bulk indexing error (2)';
            throw new Exception($error);
        }

        $this->client->indices()->refresh();
        return true;
    }

    public function search($params){
        return $this->getClient()->search($params);
    }

    /**
     * Delete documents in Cookbook
     *
     * @param array $queryES Query in array format (using a query structure format of Cookbook)
     *
     * @return array Results after operation
     */
    protected function removeDocumentsByQuery($queryES): array
    {

        $params = ['index' => $this->index, 'type' => $this->type, 'body' => $queryES];

        $response = $this->client->deleteByQuery($params);
        return $response;
    }

    /**
     * @param $index
     * @return mixed
     */
    private function checkIfIndexExists($index)
    {
        return $this->client->indices()->exists(['index' => $index]);
    }

    /**
     * Check if ES type exists
     *
     * @param $string
     * @param $string
     * @return bool
     */
    private function checkIfTypeExists($index, $type): bool
    {
        return $this->client->indices()->getMapping(['index' => $index, 'type' => $type]);
    }

    /**
     * Create ES index
     *
     * @param $string
     * @return array
     */
    private function createIndex($index): array
    {
        return $this->client->indices()->create(
            [
                'index' => $index,
                'body' => [
                    'settings' => [
                        'index.max_result_window' => 1000, //The maximum value of from + size for searches to this index.
                        'analysis' => ['analyzer' => ['standard_lowercase' => ['type' => 'custom', 'tokenizer' => 'standard', 'filter' => ['lowercase']]]]
                    ]
                ]
            ]);
    }

    /**
     * Create or modify an ES TYPE, including the desired mapping in it.
     *
     * @param $string
     * @param $string
     * @return array
     */
    private function createOrModifyType($index, $type): array
    {
        $ret = false;
        if ($index == 'cookbook' && $type == 'recipe') {
            $fields = [
                'title' => ['type' => 'text', 'fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
                'description' => ['type' => 'text', 'fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
                'ingredients' => ['type' => 'text', 'fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
                'directions' => ['type' => 'text', 'fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
                'prep_time_min' => ['type' => 'integer'],
                'cook_time_min' => ['type' => 'integer'],
                'servings' => ['type' => 'integer'],
                'tags' => ['type' => 'keyword'],
                'author' => ['type' => 'text','fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
                'source_url' => ['type' => 'text', 'fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
            ];

            $params = ['index' => $index, 'type' => $type, 'body' => [$type => ['properties' => $fields]]];

            $response = $this->client->indices()->putMapping($params);
            $ret = $response['acknowledged'];

        }
        return $ret;
    }

    /**
     * Delete ES index
     *
     * @param $string
     * @return array
     */
    private function deleteIndex($index): array
    {
        return $this->client->indices()->delete(['index' => $index]);
    }

    /**
     * @return \Elasticsearch\Client
     */
    private function getClient() {
        return $this->client;
    }
}