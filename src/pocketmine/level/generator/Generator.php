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

/**
 * Noise classes used in Levels
 */

namespace pocketmine\level\generator;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\noise\Noise;
use pocketmine\level\generator\normal\Normal;
use pocketmine\utils\Random;

abstract class Generator {
	private static $list = [];

	/**
	 * @param $object
	 * @param $name
	 *
	 * @return bool
	 */
	public static function addGenerator($object, $name){
		if(is_subclass_of($object, Generator::class) and !isset(Generator::$list[$name = strtolower($name)])){
			Generator::$list[$name] = $object;

			return true;
		}

		return false;
	}

	/**
	 * @return string[]
	 */
	public static function getGeneratorList(){
		return array_keys(Generator::$list);
	}

	/**
	 * @param $name
	 *
	 * @return string|Generator Name of class that extends Generator (not an actual Generator object)
	 */
	public static function getGenerator($name){
		if(isset(Generator::$list[$name = strtolower($name)])){
			return Generator::$list[$name];
		}

		return Normal::class;
	}

	/**
	 * @param $class
	 *
	 * @return int|string
	 */
	public static function getGeneratorName($class){
		foreach(Generator::$list as $name => $c){
			if($c === $class){
				return $name;
			}
		}

		return "unknown";
	}

	/**
	 * @param Noise $noise
	 * @param int   $xSize
	 * @param int   $samplingRate
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 *
	 * @return \SplFixedArray
	 */
	public static function getFastNoise1D(Noise $noise, $xSize, $samplingRate, $x, $y, $z){
		if($samplingRate === 0){
			throw new \InvalidArgumentException("samplingRate cannot be 0");
		}
		if($xSize % $samplingRate !== 0){
			throw new \InvalidArgumentException("xSize % samplingRate must return 0");
		}

		$noiseArray = new \SplFixedArray($xSize + 1);

		for($xx = 0; $xx <= $xSize; $xx += $samplingRate){
			$noiseArray[$xx] = $noise->noise3D($xx + $x, $y, $z);
		}

		for($xx = 0; $xx < $xSize; ++$xx){
			if($xx % $samplingRate !== 0){
				$nx = (int) ($xx / $samplingRate) * $samplingRate;
				$noiseArray[$xx] = Noise::linearLerp($xx, $nx, $nx + $samplingRate, $noiseArray[$nx], $noiseArray[$nx + $samplingRate]);
			}
		}

		return $noiseArray;
	}

	/**
	 * @param Noise $noise
	 * @param int   $xSize
	 * @param int   $zSize
	 * @param int   $samplingRate
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 *
	 * @return \SplFixedArray
	 */
	public static function getFastNoise2D(Noise $noise, $xSize, $zSize, $samplingRate, $x, $y, $z){
		if($samplingRate === 0){
			throw new \InvalidArgumentException("samplingRate cannot be 0");
		}
		if($xSize % $samplingRate !== 0){
			throw new \InvalidArgumentException("xSize % samplingRate must return 0");
		}
		if($zSize % $samplingRate !== 0){
			throw new \InvalidArgumentException("zSize % samplingRate must return 0");
		}

		$noiseArray = new \SplFixedArray($xSize + 1);

		for($xx = 0; $xx <= $xSize; $xx += $samplingRate){
			$noiseArray[$xx] = new \SplFixedArray($zSize + 1);
			for($zz = 0; $zz <= $zSize; $zz += $samplingRate){
				$noiseArray[$xx][$zz] = $noise->noise3D($x + $xx, $y, $z + $zz);
			}
		}

		for($xx = 0; $xx < $xSize; ++$xx){
			if($xx % $samplingRate !== 0){
				$noiseArray[$xx] = new \SplFixedArray($zSize + 1);
			}

			for($zz = 0; $zz < $zSize; ++$zz){
				if($xx % $samplingRate !== 0 or $zz % $samplingRate !== 0){
					$nx = (int) ($xx / $samplingRate) * $samplingRate;
					$nz = (int) ($zz / $samplingRate) * $samplingRate;
					$noiseArray[$xx][$zz] = Noise::bilinearLerp(
						$xx, $zz, $noiseArray[$nx][$nz], $noiseArray[$nx][$nz + $samplingRate],
						$noiseArray[$nx + $samplingRate][$nz], $noiseArray[$nx + $samplingRate][$nz + $samplingRate],
						$nx, $nx + $samplingRate, $nz, $nz + $samplingRate
					);
				}
			}
		}

		return $noiseArray;
	}

	/**
	 * @param Noise $noise
	 * @param int   $xSize
	 * @param int   $ySize
	 * @param int   $zSize
	 * @param int   $xSamplingRate
	 * @param int   $ySamplingRate
	 * @param int   $zSamplingRate
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 *
	 * @return array
     */
    public static function getFastNoise3D(Noise $noise, $xSize, $ySize, $zSize, $xSamplingRate, $ySamplingRate, $zSamplingRate, $x, $y, $z) {
        // Validate sampling rates
        if ($xSamplingRate === 0 || $ySamplingRate === 0 || $zSamplingRate === 0) {
            throw new \InvalidArgumentException("Sampling rates cannot be 0");
        }

        // Validate sizes against sampling rates
        foreach ([$xSize => $xSamplingRate, $ySize => $ySamplingRate, $zSize => $zSamplingRate] as $size => $rate) {
            if ($size % $rate !== 0) {
                throw new \InvalidArgumentException("Size % SamplingRate must return 0");
            }
        }

        // Initialize noise array
        $noiseArray = array_fill(0, $xSize + 1, array_fill(0, $zSize + 1, array_fill(0, $ySize + 1, 0)));

        // Generate noise at sampled points
        for ($xx = 0; $xx <= $xSize; $xx += $xSamplingRate) {
            for ($zz = 0; $zz <= $zSize; $zz += $zSamplingRate) {
                for ($yy = 0; $yy <= $ySize; $yy += $ySamplingRate) {
                    $noiseArray[$xx][$zz][$yy] = $noise->noise3D($x + $xx, $y + $yy, $z + $zz, true);
                }
            }
        }

        // Interpolate noise values for non-sampled points
        for ($xx = 0; $xx < $xSize; ++$xx) {
            for ($zz = 0; $zz < $zSize; ++$zz) {
                for ($yy = 0; $yy < $ySize; ++$yy) {
                    if ($xx % $xSamplingRate !== 0 || $zz % $zSamplingRate !== 0 || $yy % $ySamplingRate !== 0) {
                        $nx = (int)($xx / $xSamplingRate) * $xSamplingRate;
                        $ny = (int)($yy / $ySamplingRate) * $ySamplingRate;
                        $nz = (int)($zz / $zSamplingRate) * $zSamplingRate;

                        $nnx = $nx + $xSamplingRate;
                        $nny = $ny + $ySamplingRate;
                        $nnz = $nz + $zSamplingRate;

                        $dx1 = ($nnx - $xx) / ($nnx - $nx);
                        $dx2 = ($xx - $nx) / ($nnx - $nx);
                        $dy1 = ($nny - $yy) / ($nny - $ny);
                        $dy2 = ($yy - $ny) / ($nny - $ny);

                        $noiseArray[$xx][$zz][$yy] = (($nnz - $zz) / ($nnz - $nz)) * (
                                $dy1 * ($dx1 * $noiseArray[$nx][$nz][$ny] + $dx2 * $noiseArray[$nnx][$nz][$ny]) +
                                $dy2 * ($dx1 * $noiseArray[$nx][$nz][$nny] + $dx2 * $noiseArray[$nnx][$nz][$nny])
                            ) + (($zz - $nz) / ($nnz - $nz)) * (
                                $dy1 * ($dx1 * $noiseArray[$nx][$nnz][$ny] + $dx2 * $noiseArray[$nnx][$nnz][$ny]) +
                                $dy2 * ($dx1 * $noiseArray[$nx][$nnz][$nny] + $dx2 * $noiseArray[$nnx][$nnz][$nny])
                            );
                    }
                }
            }
        }

        return $noiseArray;
    }

	/**
	 * @return int
	 */
	public function getWaterHeight() : int{
		return 0;
	}

	/**
	 * Generator constructor.
	 *
	 * @param array $settings
	 */
	public abstract function __construct(array $settings = []);

	/**
	 * @param ChunkManager $level
	 * @param Random       $random
	 *
	 * @return mixed
	 */
	public abstract function init(ChunkManager $level, Random $random);

	/**
	 * @param $chunkX
	 * @param $chunkZ
	 *
	 * @return mixed
	 */
	public abstract function generateChunk($chunkX, $chunkZ);

	/**
	 * @param $chunkX
	 * @param $chunkZ
	 *
	 * @return mixed
	 */
	public abstract function populateChunk($chunkX, $chunkZ);

	public abstract function getSettings();

	public abstract function getName();

	public abstract function getSpawn();
}
