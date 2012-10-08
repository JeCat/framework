<?php
namespace org\jecat\framework\dataupgrade;

interface IDataUpgrader{
	public function upgrade(array &$arrServiceSettings);
}
