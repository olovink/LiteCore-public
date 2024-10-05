<?php


namespace pocketmine\level\generator\populator;

use pocketmine\block\BlockIds;
use pocketmine\level\ChunkManager;
use pocketmine\type\GrassType;
use pocketmine\utils\Random;

class TallGrass extends Populator {
	private ChunkManager $level;

    private int $grassType = GrassType::TYPE_GRASS;
	private int $randomAmount = 1;
	private int $baseAmount = 0;

    public function setGrassType(int $grassType): void{
        $this->grassType = $grassType;
    }

	public function setRandomAmount($amount): void{
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount): void{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random): void{
		$this->level = $level;
		$amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
			$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
			$y = $this->getHighestWorkableBlock($x, $z);

			if($y !== -1 and $this->canTallGrassStay($x, $y, $z)){
				$this->level->setBlockIdAt($x, $y, $z, BlockIds::TALL_GRASS);
				$this->level->setBlockDataAt($x, $y, $z, $this->grassType);
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