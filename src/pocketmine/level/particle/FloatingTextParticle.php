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

namespace pocketmine\level\particle;

use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\utils\UUID;

class FloatingTextParticle extends Particle {

	protected string $text;
	protected string $title;
	protected ?int $entityId = null;
	protected bool $invisible = false;

	public function __construct(Vector3 $pos, $text, $title = ""){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->text = $text;
		$this->title = $title;
	}

	public function getText(): string{
		return $this->text;
	}

	public function getTitle(): string{
		return $this->title;
	}

	public function setText($text): void{
		$this->text = $text;
	}

	public function setTitle($title): void{
		$this->title = $title;
	}

	public function isInvisible(): bool{
		return $this->invisible;
	}

	public function setInvisible(bool $value = true): void{
		$this->invisible = $value;
	}

	public function encode(): array{
		$packets = [];

		if($this->entityId === null){
			$this->entityId = Entity::$entityCount++;
		}else{
			$removeEntityPacket = new RemoveEntityPacket();
			$removeEntityPacket->eid = $this->entityId;
			$packets[] = $removeEntityPacket;
		}

		if(!$this->invisible){
			$addPlayerPacket = new AddPlayerPacket();
			$addPlayerPacket->uuid = UUID::fromRandom();
			$addPlayerPacket->username = $this->title . ($this->text !== "" ? "\n" . $this->text : "");
			$addPlayerPacket->eid = $this->entityId;
			$addPlayerPacket->x = $this->x;
			$addPlayerPacket->y = $this->y;
            $addPlayerPacket->z = $this->z;
			$addPlayerPacket->item = Item::get(BlockIds::AIR, 0, 0);
			$flags = (
				(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_IMMOBILE)
			);
			$addPlayerPacket->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title],
				Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]
			];

			$packets[] = $addPlayerPacket;
		}

		return $packets;
	}
}
