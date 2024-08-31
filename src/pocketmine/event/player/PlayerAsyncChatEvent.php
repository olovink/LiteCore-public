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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\player;

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\WaitGroup;
use function spl_object_id;

/**
 * AsyncChatEvent 
 */
class PlayerAsyncChatEvent extends PlayerEvent implements Cancellable {
	public static $handlerList = null;

    private WaitGroup $waitGroup;

	protected string $message;

	protected string $format;

	protected array $recipients = [];

	/**
	 * PlayerChatEvent constructor.
	 *
	 * @param Player     $player
	 * @param            $message
	 * @param string     $format
	 * @param array|null $recipients
	 */
	public function __construct(Player $player, $message, $format = "chat.type.text", array $recipients = null){
		$this->player = $player;
		$this->message = $message;

		$this->format = $format;

        $this->waitGroup = new WaitGroup;
		if($recipients === null){
			foreach(Server::getInstance()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof CommandSender){
					$this->recipients[spl_object_id($permissible)] = $permissible;
				}
			}
		}else{
			$this->recipients = $recipients;
		}
	}

	/**
	 * @return string
	 */
	public function getMessage(){
		return $this->message;
	}

	public function setMessage($message): void{
		$this->message = $message;
	}

	public function setPlayer(Player $player): void{
		$this->player = $player;
	}

	/**
	 * @return string
	 */
	public function getFormat(){
		return $this->format;
	}

	/**
	 * @param $format
	 */
	public function setFormat($format){
		$this->format = $format;
	}

	/**
	 * @return array|Player[]
	 */
	public function getRecipients(){
		return $this->recipients;
	}

	/**
	 * @param array $recipients
	 */
	public function setRecipients(array $recipients){
		$this->recipients = $recipients;
	}

    public function getWaitGroup() : WaitGroup{
        return $this->waitGroup;
    }
}