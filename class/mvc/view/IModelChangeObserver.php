<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\mvc\view\View;

interface IModelChangeObserver {
    public function onModelChanging(View $aView);
}

?>
