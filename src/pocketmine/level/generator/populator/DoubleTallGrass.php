<?php

declare(strict_types=1);

namespace pocketmine\level\generator\populator;

use pocketmine\block\BlockIds;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class DoubleTallGrass extends Populator {

    private ChunkManager $level;
    private int $randomAmount = 1;
    private int $baseAmount = 0;
    private int $plantType = 1;

    public function setRandomAmount(int $amount): void{
        $this->randomAmount = $amount;
    }

    public function setBaseAmount(int $amount): void{
        $this->baseAmount = $amount;
    }
    public function setPlantType(int $plantType): void{
        $this->plantType = $plantType;
    }

    public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random): void{
        $this->level = $level;
        $amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;
        for($i = 0; $i < $amount; ++$i){
            $x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
            $z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
            $y = $this->getHighestWorkableBlock($x, $z);

            if($y !== -1 and $this->canTallGrassStay($x, $y, $z)){
                $this->level->setBlockIdAt($x, $y, $z, BlockIds::DOUBLE_PLANT);
                $this->level->setBlockDataAt($x, $y, $z, $this->plantType);
                $this->level->setBlockIdAt($x, $y + 1, $z, BlockIds::DOUBLE_PLANT);
                $this->level->setBlockDataAt($x, $y + 1, $z, 10);
            }
        }
    }

    private function canTallGrassStay($x, $y, $z): bool{
        return $this->level->getBlockIdAt($x, $y - 1, $z) === BlockIds::GRASS;
    }

    private function getHighestWorkableBlock($x, $z): int{
        for($y = 127; $y >= 0; --$y){
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if($b !== BlockIds::AIR and $b !== BlockIds::LEAVES and $b !== BlockIds::LEAVES2){
                break;
            }
        }

        return $y === 0 ? -1 : ++$y;
    }
}