<?php

namespace pocketmine\level\generator;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class GenerationTask extends AsyncTask {

	public bool $state;
	public int $levelId;
	public string $chunk;

	/**
	 * GenerationTask constructor.
	 *
	 * @param Level $level
	 * @param Chunk $chunk
	 */
	public function __construct(Level $level, Chunk $chunk){
		$this->state = true;
		$this->levelId = $level->getId();
		$this->chunk = $chunk->fastSerialize();
	}

	public function onRun(): void{
		/** @var SimpleChunkManager $manager */
		$manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
		/** @var Generator $generator */
		$generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");
		if($manager === null or $generator === null){
			$this->state = false;
			return;
		}

        $chunk = Chunk::fastDeserialize($this->chunk);

        $manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);

		$generator->generateChunk($chunk->getX(), $chunk->getZ());

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->setGenerated();
		$this->chunk = $chunk->fastSerialize();

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);
	}

	public function onCompletion(Server $server): void{
		$level = $server->getLevel($this->levelId);
		if($level !== null){
            $chunk = Chunk::fastDeserialize($this->chunk);
            $level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
