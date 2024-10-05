<?php

/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalcorp.ml/
 *
 *
*/

namespace pocketmine\level\generator\normal;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\Stone;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Normal extends Generator {
	const NAME = "Normal";

	/** @var Populator[] */
	protected $populators = [];
	/** @var ChunkManager */
	protected $level;
	/** @var Random */
	protected $random;
	protected $waterHeight = 62;
	protected $bedrockDepth = 5;

	/** @var Populator[] */
	protected $generationPopulators = [];
	/** @var Simplex */
	protected $noiseBase;

	/** @var BiomeSelector */
	protected $selector;

	private static $GAUSSIAN_KERNEL = null;
	private static $SMOOTH_SIZE = 2;

	public function __construct(array $options = []){
		if(self::$GAUSSIAN_KERNEL === null){
			self::generateKernel();
		}
	}

	private static function generateKernel(){
		self::$GAUSSIAN_KERNEL = [];

		$bellSize = 1 / self::$SMOOTH_SIZE;
		$bellHeight = 2 * self::$SMOOTH_SIZE;

		for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx){
			self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE] = [];

			for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz){
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}

	public function getName() : string{
		return self::NAME;
	}

	public function getWaterHeight() : int{
		return $this->waterHeight;
	}

	public function getSettings(){
		return [];
	}

	public function pickBiome($x, $z){
		$hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed();
		$hash *= $hash + 223;
		$xNoise = $hash >> 20 & 3;
		$zNoise = $hash >> 22 & 3;
		if($xNoise == 3){
			$xNoise = 1;
		}
		if($zNoise == 3){
			$zNoise = 1;
		}

		return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
	}

	public function init(ChunkManager $level, Random $random): void{
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());
		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 32);
		$this->random->setSeed($this->level->getSeed());
        $this->selector = new BiomeSelector($this->random, function($temperature, $rainfall) {
            return $this->determineBiome($temperature, $rainfall);
        }, Biome::getBiome(Biome::SMALL_MOUNTAINS));

		$this->selector->addBiome(Biome::getBiome(Biome::PLAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::DESERT));
		$this->selector->addBiome(Biome::getBiome(Biome::MOUNTAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::FOREST));
		$this->selector->addBiome(Biome::getBiome(Biome::TAIGA));
		$this->selector->addBiome(Biome::getBiome(Biome::SWAMP));
		$this->selector->addBiome(Biome::getBiome(Biome::RIVER));
		$this->selector->addBiome(Biome::getBiome(Biome::ICE_PLAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::SMALL_MOUNTAINS));
		$this->selector->addBiome(Biome::getBiome(Biome::BIRCH_FOREST));
		$this->selector->addBiome(Biome::getBiome(Biome::BEACH));
		$this->selector->addBiome(Biome::getBiome(Biome::MESA));

		$this->selector->recalculate();

		$cover = new GroundCover();
		$this->generationPopulators[] = $cover;

		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(new CoalOre(), 20, 16, 0, 128),
			new OreType(New IronOre(), 20, 8, 0, 64),
			new OreType(new RedstoneOre(), 8, 7, 0, 16),
			new OreType(new LapisOre(), 1, 6, 0, 32),
			new OreType(new GoldOre(), 2, 8, 0, 32),
			new OreType(new DiamondOre(), 1, 7, 0, 16),
			new OreType(new Dirt(), 20, 32, 0, 128),
			new OreType(new Stone(Stone::GRANITE), 20, 32, 0, 128),
			new OreType(new Stone(Stone::DIORITE), 20, 32, 0, 128),
			new OreType(new Stone(Stone::ANDESITE), 20, 32, 0, 128),
			new OreType(new Gravel(), 10, 16, 0, 128)
		]);
		$this->populators[] = $ores;
	}

    private function determineBiome($temperature, $rainfall): int{
        if ($rainfall < 0.25) {
            return $this->handleLowRainfall($temperature);
        } elseif ($rainfall < 0.60) {
            return $this->handleModerateRainfall($temperature);
        } elseif ($rainfall < 0.80) {
            return $this->handleHighRainfall($temperature);
        } else {
            return $this->handleVeryHighRainfall($temperature);
        }
    }

    private function handleLowRainfall($temperature): int{
        if ($temperature < 0.7) {
            return Biome::FOREST;
        } elseif ($temperature < 0.85) {
            return Biome::RIVER;
        } else {
            return Biome::SWAMP;
        }
    }

    private function handleModerateRainfall($temperature): int{
        if ($temperature < 0.25) {
            return Biome::ICE_PLAINS;
        } elseif ($temperature < 0.75) {
            return Biome::PLAINS;
        } else {
            return Biome::DESERT;
        }
    }

    private function handleHighRainfall($temperature): int{
        if ($temperature < 0.25) {
            return Biome::TAIGA;
        } elseif ($temperature < 0.75) {
            return Biome::FOREST;
        } else {
            return Biome::BIRCH_FOREST;
        }
    }

    private function handleVeryHighRainfall($temperature): int{
        if ($temperature < 0.20) {
            return Biome::MOUNTAINS;
        } elseif ($temperature < 0.40) {
            return Biome::PLAINS;
        } else {
            return Biome::RIVER;
        }
    }

    public function generateChunk($chunkX, $chunkZ): void {
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        $noise = Generator::getFastNoise3D($this->noiseBase, 16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $biomeCache = [];

        for ($x = 0; $x < 16; ++$x) {
            for ($z = 0; $z < 16; ++$z) {
                [$minSum, $maxSum, $weightSum] = [0, 0, 0];
                $biome = $this->pickBiome($chunkX * 16 + $x, $chunkZ * 16 + $z);
                $chunk->setBiomeId($x, $z, $biome->getId());

                for ($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx) {
                    for ($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz) {
                        $weight = self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE];
                        $adjacent = ($sx === 0 && $sz === 0) ? $biome : $this->getAdjacentBiome($biomeCache, $chunkX, $chunkZ, $x, $z, $sx, $sz);
                        $minSum += ($adjacent->getMinElevation() - 1) * $weight;
                        $maxSum += $adjacent->getMaxElevation() * $weight;
                        $weightSum += $weight;
                    }
                }

                $minSum /= $weightSum;
                $maxSum /= $weightSum;
                $this->generateTerrain($chunk, $x, $z, $minSum, $maxSum, $noise);
            }
        }

        foreach ($this->generationPopulators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
        }
    }

    private function getAdjacentBiome(array &$biomeCache, int $chunkX, int $chunkZ, int $x, int $z, int $sx, int $sz) {
        $index = Level::chunkHash($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);
        if (!isset($biomeCache[$index])) {
            $biomeCache[$index] = $this->pickBiome($chunkX * 16 + $x + $sx, $chunkZ * 16 + $z + $sz);
        }
        return $biomeCache[$index];
    }

    private function generateTerrain($chunk, int $x, int $z, float $minSum, float $maxSum, array $noise): void {
        $solidLand = false;
        for ($y = 127; $y >= 0; --$y) {
            if ($y === 0) {
                $chunk->setBlockId($x, $y, $z, BlockIds::BEDROCK);
                continue;
            }

            $noiseAdjustment = 2 * (($maxSum - $y) / ($maxSum - $minSum)) - 1;
            $caveLevel = $minSum - 10;
            $distAboveCaveLevel = max(0, $y - $caveLevel);
            $noiseAdjustment = min($noiseAdjustment, 0.4 + ($distAboveCaveLevel / 10));
            $noiseValue = $noise[$x][$z][$y] + $noiseAdjustment;

            if ($noiseValue > 0) {
                $chunk->setBlockId($x, $y, $z, BlockIds::STONE);
                $solidLand = true;
            } elseif ($y <= $this->waterHeight && !$solidLand) {
                $chunk->setBlockId($x, $y, $z, BlockIds::STILL_WATER);
            }
        }
    }

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}

	public function getSpawn(){
		return new Vector3(127.5, 128, 127.5);
	}

}