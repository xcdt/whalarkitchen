<?php
/**
 * Created by PhpStorm.
 * User: Francisco.fernandez
 * Date: 4/6/2019
 * Time: 11:53 AM
 */

namespace Kitchen\Models;

use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\FullText\QueryStringQuery;
use ONGR\ElasticsearchDSL\Search;

class Cookbook extends ElasticSearch {

    public function __construct($settings) {
        parent::__construct($settings);
    }

	public function CreateRecipe($data){
        $params = array("body" => array());
        $params["body"][] = array("index" => array("_index" => $this->index, "_type" => $this->type));
        $params["body"][] = $data;

		$response = $this->bulkIndexing($params, 'index');
	}

    /**
     * @param $id
     * @param $tag
     */
    public function updateDocTag($id, $tag) {
        $response = $this->client->indices()->getMapping(["index" => $this->index, "type" => $this->type]);
        $properties = array_column($response, $this->index);
        $params =[
            "index" => $this->index,
            "type" => $this->type,
            "body" => [
                [
                    "update" => [
                        //   ^^^^^^ Here I change from index to update
                        "_index" => $this->index,
                        "_type" => $this->type,
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
     * @param $id
     * @param $tag
     */
    public function updateDocIngredients($id, $tag) {
        $response = $this->client->indices()->getMapping(["index" => $this->index, "type" => $this->type]);
        $properties = array_column($response, $this->index);
        $params =[
            "index" => $this->index,
            "type" => $this->type,
            "body" => [
                [
                    "update" => [
                        //   ^^^^^^ Here I change from index to update
                        "_index" => $this->index,
                        "_type" => $this->type,
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
     * @param $id
     * @return array
     */
    public function DeleteRecipe($id): array
    {
        $bodyJSON = array(
            "query" => array(
                "match" => array(
                    "_id" => $id
                )
            )
        );
        return $this->removeDocumentsByQuery($bodyJSON);

    }

    /**
     * @param null $input
     * @param null $field
     * @return array
     */
    public function Search($input=null, $field=null): array
    {

        $search = new Search();


        if(is_null($field) && !is_null($input)){
            //Search in all fields in the index
            $conf=['all_fields' => 'true'];
            $ES_query = new QueryStringQuery($input, $conf);

        }
        else{
            $ES_query = new MatchQuery($field, $input);
        }

        $search->addQuery($ES_query);

        if(is_null($input) && is_null($field)){
            $params = [
                'index' => 'cookbook',
                'type' => 'recipe'
            ];
        }
        else {
            $params = [
                'index' => 'cookbook',
                'type' => 'recipe',
                'body' => $search->toArray()
            ];
        }

        $elastic = new ElasticSearch($this->settings);

		$results = $elastic ->search($params);
		$total_rows = (isset($results['hits']['total']) ? $results['hits']['total'] : 0);
        $result=[];
		if ($total_rows > 0) {

            foreach ($results['hits']['hits'] as $item) {
                $item['_source']['_id'] = $item['_id'];
                array_push($result, $item['_source']);
            }
		}
		return $result;
    }
}
