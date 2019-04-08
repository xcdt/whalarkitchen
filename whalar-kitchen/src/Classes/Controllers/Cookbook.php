<?php
/**
 * Created by PhpStorm.
 * User: Francisco.fernandez
 * Date: 4/4/2019
 * Time: 9:01 PM
 */

namespace Kitchen\Controllers;

class Cookbook
{

    protected $container;

    /**
     * Cookbook constructor.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param  $request
     * @param  $response
     * @param  null     $all ($all=1 retrieve all available recipes (for home page))
     * @return mixed
     * Search for documents. Usage example:
     * title:query
     * description:query
     * ingredients:query
     * directions:query
     * prep_time_min:query
     * cook_time_min:query
     * servinges:query
     * tags:query
     * author:query
     * query (without filter) try to match in any field
     */
    public function Search($request, $response, $all=null)
    {
        $field=$input=null;

        //Retrieve all the documents
        if (is_null($all)) {
            $search = $request->getParsedBodyParam('name');
            $keys = explode(':', $search);
            $field=$keys[0];
            $input=$keys[1]??null;

            //Search without filter
            if (is_null($input)) {
                $input=$field;
                $field=null;
            }

            //Search with filter
            else{
                //We check if author was introduced.
                if ($field=="author") {
                    $field.=".name";
                }
            }

            $input=strtolower($input);
        }

        $cookbook = $this->container->get('cookbook');
        return $cookbook->Search($input, $field);
    }

    /**
     * @param  $request
     * @param  $response
     * @return mixed
     */
    public function GetRecipe($request, $response)
    {
        $search = $request->getAttribute('name');
        $cookbook = $this->container->get('cookbook');
        return $cookbook->Search($search, '_id');
    }

    /**
     * @param $request
     * @param $response
     * Creating a sample recipe
     */

    public function CreateRecipe($request, $response)
    {
        $cookbook = $this->container->get('cookbook');


        //We imagine that we got the fields from a POST form (don't have much time, sorry!)
        $recipe  = array(
            "title" => "Tortilla de patatas",
            "description" => "Rica tortilla española pa volverse loco",
            "ingredients" => ["3 Patatas", "4 Huevos", "1/2 Cebolla", "150ml Aceite de Oliva", "Sal"],
            "directions" => ["Precalentar Aceite", "Echar todo ahí pa que se fría", "No tirarla al darle la vuelta", "Dársela al perro si la tiras", "Culpar a la sarten si se pega"],
            "prep_time_min" => "15",
            "cook_time_min" => "20",
            "servings" => "3",
            "tags" => ["Tortilla", "Spanish Verrrry Good Dish"],
            "author" => ["name" => "Fran Trujillo", "url" => "www.urlejemplo.com"],
            "source_url" => "www.blablablablba.com"
        );

        return $cookbook->CreateRecipe($recipe);
    }

    /**
     * @param $request
     * @param $response
     * Update a recipe by id. I've set two methods as an example to update the tags and the ingredients.
     * The main idea would be to do partial updates for single sections instead of the whole recipe
     * every time we want to change something
     */
    public function UpdateRecipe($request, $response)
    {
        $id = $request->getParsedBodyParam('_id');
        $new_tag = $request->getParsedBodyParam('new_tag') ?? null;
        $new_ingredient = $request->getParsedBodyParam('new_ingredient') ?? null;
        $cookbook = $this->container->get('cookbook');

        if (!is_null($new_tag)) {
            if (!$cookbook->updateDocTag($id, $new_tag)) {
                return false;
            }
        }
        if (!is_null($new_ingredient)) {
            if (!$cookbook->updateDocIngredients($id, $new_ingredient)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  $request
     * @param  $response
     * @return mixed
     * Delete a recipe by id
     */
    public function DeleteRecipe($request, $response)
    {
        $id = $request->getParsedBodyParam('_id');
        $cookbook = $this->container->get('cookbook');

        return $cookbook->DeleteRecipe($id);
    }
}
