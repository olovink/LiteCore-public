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
use pocketmine\level\generator\populator\DoubleTallGrass;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\type\DoublePlantType;
use pocketmine\type\GrassType;

abstract class SnowyBiome extends NormalBiome {

	/**
	 * SnowyBiome constructor.
	 */
	public function __construct(){
        $doublePlantGrass = new DoubleTallGrass();
        $doublePlantGrass->setBaseAmount(1);
        $doublePlantGrass->setPlantType(DoublePlantType::TYPE_GRASS);
        $this->addPopulator($doublePlantGrass);

        $grass = new TallGrass();
        $grass->setBaseAmount(5);
        $grass->setGrassType(GrassType::TYPE_GRASS);
        $this->addPopulator($grass);

		$this->setGroundCover([
			Block::get(Block::GRASS, 0),
			Block::get(Block::DIRT, 0),
			Block::get(Block::DIRT, 0),
			Block::get(Block::DIRT, 0),
		]);
	}
}