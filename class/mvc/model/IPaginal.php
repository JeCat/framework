<?php
namespace jc\mvc\model ;

use jc\mvc\view\widget\Paginator;

interface IPaginal
{
    public function totalCount();
    public function setPagination($iPerPage,$iPageNum);
}

?>
