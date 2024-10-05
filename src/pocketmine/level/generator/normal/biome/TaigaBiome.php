<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\Sapling;
use pocketmine\level\generator\populator\DoubleTallGrass;
use pocketmine\level\generator\populator\MossStone;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\type\DoublePlantType;
use pocketmine\type\GrassType;

class TaigaBiome extends SnowyBiome {

	/**
	 * TaigaBiome constructor.
	 */
	public function __construct(){
		parent::__construct();

		$trees = new Tree(Sapling::SPRUCE);
		$trees->setBaseAmount(3);
		$this->addPopulator($trees);

        $doubleFern = new DoubleTallGrass();
        $doubleFern->setPlantType(DoublePlantType::TYPE_FERN);
        $doubleFern->setBaseAmount(5);
        $this->addPopulator($doubleFern);

        $fernGrass = new TallGrass();
        $fernGrass->setGrassType(GrassType::TYPE_FERN);
        $fernGrass->setBaseAmount(5);
        $this->addPopulator($fernGrass);

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(1);
		$this->addPopulator($tallGrass);

		$this->setElevation(63, 81);

		$this->temperature = 0.05;
		$this->rainfall = 0.8;

		$this->setGroundCover([
			Block::get(Block::GRASS, 0),
			Block::get(Block::DIRT, 0),
			Block::get(Block::DIRT, 0),
			Block::get(Block::DIRT, 0)
		]);
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Taiga";
	}
}
