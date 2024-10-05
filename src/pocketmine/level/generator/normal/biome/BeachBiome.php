<?php

namespace pocketmine\level\generator\normal\biome;

use pocketmine\level\generator\populator\DeadBush;

class BeachBiome extends SandyBiome {

	public function __construct(){
		parent::__construct();

		$this->removePopulator(DeadBush::class);
		$this->setElevation(62, 65);
	}


	public function getName() : string{
		return "Beach";
	}
} 