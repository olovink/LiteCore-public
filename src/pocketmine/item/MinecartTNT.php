<?php 

/**
*     ______  _     _
*    / ____/ (_)   (_) ___    __  __
*   / /_    / /   / / / _ \  / / / /
*  / __/   / /   / / /  __/ / /_/ /
* /_/     /_/ __/ /  \___/  \__, /
*            /___/         /____/
*   @author Fijey
*   @link github.com/fijey000
*/

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\Rail;
use pocketmine\entity\MinecartTNT as MinecartTNTEntity;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class MinecartTNT extends Item {
    /**
	 * MinecartTNT constructor.
	 *
	 * @param int $meta
	 * @param int $count
	 */
    public function __construct($meta = 0, $count = 1){
        parent::__construct(self::MINECART_WITH_TNT, $meta, $count, 'Minecart TNT');
    }

    /**
     * @return int
     */
    public function getMaxStackSize() : int {
        return 1;
    }

    /**
	 * @param Level  $level
	 * @param Player $player
	 * @param Block  $block
	 * @param Block  $target
	 * @param        $face
	 * @param        $fx
	 * @param        $fy
	 * @param        $fz
	 */
    public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
        if($target instanceof Rail){
            $entity = new MinecartTNTEntity($level, new CompoundTag('', [
                'Pos' => new ListTag('Pos', [new DoubleTag('', $block->getX()), new DoubleTag('', $block->getY()), new DoubleTag('', $block->GetZ())]),
                'Rotation' => new ListTag('Rotation', [new DoubleTag('', 0), new DoubleTag('', 0)])]));
            $entity->spawnToAll();
        }
        if($player instanceof Player){
            if($player->isSurvival()){
                $player->getInventory()->setItemInHand(Item::get(0));
            }
        }
    }
}