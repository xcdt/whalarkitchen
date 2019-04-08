<?php
/**
 * Created by PhpStorm.
 * User: Francisco.fernandez
 * Date: 4/4/2019
 * Time: 8:56 PM
 */

    namespace Kitchen\Factories;

use Kitchen\Models\ChickenModel;
use Kitchen\Models\CowModel;
use Kitchen\Models\PenModel;

class PenModelFactory
{
    public function __invoke()
    {
        $cow = new CowModel();
        $chicken = new ChickenModel();
        return new PenModel($cow, $chicken);
    }

}
