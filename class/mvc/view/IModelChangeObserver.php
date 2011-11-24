<?php
namespace jc\mvc\view ;

use jc\mvc\view\View;

interface IModelChangeObserver {
    public function onModelChanging(View $aView);
}

?>
