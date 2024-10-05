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
use pocketmine\level\generator\populator\Cactus;
use pocketmine\level\generator\populator\DeadBush;
use pocketmine\level\generator\populator\TallGrass;

class SandyBiome extends GrassyBiome {

	/**
	 * SandyBiome constructor.
	 */
    public function __construct() {
        parent::__construct();

        $this->initializePopulators();
        $this->setEnvironment();
    }

    private function initializePopulators(): void{
        $populators = [
            $this->createPopulator(Cactus::class, 6),
            $this->createPopulator(DeadBush::class, 2),
            $this->createPopulator(TallGrass::class, 5)
        ];

        foreach ($populators as $populator) {
            $this->addPopulator($populator);
        }
    }

    private function createPopulator(string $className, int $baseAmount) {
        $populator = new $className();
        $populator->setBaseAmount($baseAmount);
        return $populator;
    }

    private function setEnvironment(): void{
        $this->setElevation(63, 81);
        $this->temperature = 0.05;
        $this->rainfall = 0.8;
        $this->setGroundCover($this->generateGroundCover());
    }

    private function generateGroundCover(): array{
        $sand = Block::get(BlockIds::SAND);
        $sandstone = Block::get(BlockIds::SANDSTONE);

        return array_merge(
            array_fill(0, 10, $sand),
            array_fill(0, 20, $sandstone)
        );
    }

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Sandy";
	}
}
