<?php
/**
 * Created by PhpStorm.
 * User: Francisco.fernandez
 * Date: 4/6/2019
 * Time: 11:53 AM
 */

namespace Kitchen\Models;

use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;

class Cookbook {

	private $client;

	private $logger;

	private $result;

	private $settings;

	public function __construct($settings) {
		$this->settings = $settings;
		$this->initLocal();

		//Checking if the index exists, if not we create it and put the mapping for recipe into it
		if (!$this->checkIfIndexExists('cookbook')) {
			$this->createIndex('cookbook');
			$this->createOrModifyType('cookbook', 'recipe');
		}

//        $params = array(
//            'recipe_servings' => '1',
//            'title' => 'cazon',
//            'pepe' => '2'
//        );

//        $this->insertES("cookbook", "recipe", $params );
//        $this->Search();
	}

	public function initLocal() {
		$hosts = [[
		            'host' => $this->settings['host'],
					'port' => $this->settings['port'],
					'scheme' => $this->settings['scheme'],
					'user' => $this->settings['user'],
					'pass' => $this->settings['pass']
                  ]];

		//$logger = Elasticsearch\ClientBuilder::defaultLogger($this->config->syslog->logDir . '/elasticsearch.log');

		$this->client = ClientBuilder::create()->setHosts($hosts)->setRetries(3)->build();
	}

	public function CreateRecipe($indexES, $typeES, $data){
        $params = array("body" => array());
        $params["body"][] = array("index" => array("_index" => $indexES, "_type" => $typeES));
        $params["body"][] = $data;

		$response = $this->bulkIndexing($params, 'create');
	}

    /**
     * @param $indexES
     * @param $typeES
     * @param $id
     * @param $tag
     */
    public function updateDocTags($indexES, $typeES, $id, $tag) {
        $response = $this->client->indices()->getMapping(["index" => $indexES, "type" => $typeES]);
        $properties = array_column($response, $indexES);
        $params =[
            "index" => $indexES,
            "type" => $typeES,
            "body" => [
                [
                    "update" => [
                        //   ^^^^^^ Here I change from index to update
                        "_index" => $indexES,
                        "_type" => $typeES,
                        "_id" => $id
                    ]
                ],
                [
                    "script"=> [
                        "inline" => "ctx._source.tags.add(params.tags)",
                        "params" => [
                            "tags" => $tag
                        ]
                    ]
                ]
            ]
        ];

		return $this->bulkIndexing($params, 'update');
	}

    /**
     * @param $indexES
     * @param $typeES
     * @param $id
     * @param $tag
     */
    public function updateDocIngredients($indexES, $typeES, $id, $tag) {
        $response = $this->client->indices()->getMapping(["index" => $indexES, "type" => $typeES]);
        $properties = array_column($response, $indexES);
        $params =[
            "index" => $indexES,
            "type" => $typeES,
            "body" => [
                [
                    "update" => [
                        //   ^^^^^^ Here I change from index to update
                        "_index" => $indexES,
                        "_type" => $typeES,
                        "_id" => $id
                    ]
                ],
                [
                    "script"=> [
                        "inline" => "ctx._source.ingredients.add(params.ingredients)",
                        "params" => [
                            "ingredients" => $tag
                        ]
                    ]
                ]
            ]
        ];

        return $this->bulkIndexing($params, 'update');
    }

	/**
	 * Bulk indexing in Cookbook
	 *
	 * @param string $params
	 * @return void
	 */
    private function bulkIndexing($params, $operation) {
		$response = $this->client->bulk($params);

		if (isset($response['items'])) {
			foreach ($response['items'] as $item) {
				if (!in_array($item[$operation]['result'], ['created', 'updated'])) {
					$error = 'Bulk indexing error (1)';
					$error .= ' / ' . $item['index']['status'];
					$error .= ' / ' . $item['index']['error']['type'];
					$error .= ' / ' . $item['index']['error']['reason'];
					//throw new Exception($error);
				}
				$this->result[$item['index']['result']]++;
			}
		} else {
			$error = 'Bulk indexing error (2)';
			throw new Exception($error);
		}

		$this->client->indices()->refresh();
		return true;
	}

	/**
	 * Delete documents in Cookbook
	 *
	 * @param string $indexES Index in Cookbook
	 * @param string $typeES Type in Cookbook
	 * @param array $queryES Query in array format (using a query structure format of Cookbook)
	 *
	 * @return array Results after operation
	 */
	public function removeDocumentsByQuery($indexES, $typeES, $queryES) {

		$params = ['index' => $indexES, 'type' => $typeES, 'body' => $queryES];

		$response = $this->client->deleteByQuery($params);
		return $response;
	}

    /**
     * @param $index
     * @return mixed
     */
    private function checkIfIndexExists($index) {
		return $this->client->indices()->exists(['index' => $index]);
    }

	/**
	 * Check if ES type exists
	 *
	 * @param $string
	 * @param $string
	 * @return bool
	 */
	private function checkIfTypeExists($index, $type) {
			return $this->client->indices()->getMapping(['index' => $index, 'type' => $type]);
	}

	/**
	 * Create ES index
	 *
	 * @param $string
	 * @return array
	 */
	private function createIndex($index) {
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
	private function createOrModifyType($index, $type) {
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
				'author' => ['type' => 'text', 'fielddata' => true, 'analyzer' => 'standard_lowercase', 'search_analyzer' => 'standard'],
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
	public function deleteIndex($index) {
		return $this->client->indices()->delete(['index' => $index]);
	}

	/**
	 * @return \Elasticsearch\Client
	 */
	public function getClient() {
		return $this->client;
	}


    /**
     * @param null $input
     * @param null $field
     * @return array
     */
    public function Search($input=null, $field=null){
		$ES_bool_tmp = new BoolQuery();

		$ES_bool_tmp->add(new MatchQuery($field, $input), BoolQuery::MUST);
		$search = new Search();
		$search->addQuery($ES_bool_tmp);

		if(!is_null($field)){
            $params = [
                'index' => 'cookbook',
                'type' => 'recipe',
                'body' => $search->toArray()
            ];
        }
        else{
            $params = [
                'index' => 'cookbook',
                'type' => 'recipe'
            ];
        }

		$results = $this->getClient()->search($params);
		$total_rows = (isset($results['hits']['total']) ? $results['hits']['total'] : 0);
        $result=[];
		if ($total_rows > 0) {

            foreach ($results['hits']['hits'] as $item) {
                $item['_source']['_id'] = $item['_id'];
                array_push($result, $item['_source']);
            }
		}
		}

}
