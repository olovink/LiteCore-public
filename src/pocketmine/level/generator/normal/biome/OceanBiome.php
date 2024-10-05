<?php


namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\TallGrass;

class OceanBiome extends NormalBiome{

	/**
	 * OceanBiome constructor.
	 */
	public function __construct(){
		$this->setGroundCover([
			Block::get(BlockIds::GRAVEL),
			Block::get(BlockIds::GRAVEL),
			Block::get(BlockIds::GRAVEL),
			Block::get(BlockIds::GRAVEL),
			Block::get(BlockIds::GRAVEL)
		]);

		$sugarcane = new Sugarcane();
		$sugarcane->setBaseAmount(6);

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(5);

		$this->addPopulator($sugarcane);
		$this->addPopulator($tallGrass);

		$this->setElevation(46, 58);

		$this->temperature = 0.5;
		$this->rainfall = 0.5;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Ocean";
	}
}
