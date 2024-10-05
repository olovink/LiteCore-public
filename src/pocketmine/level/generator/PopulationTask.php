<?php


namespace pocketmine\level\generator;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;


class PopulationTask extends AsyncTask {

    public bool $state;
    public int $levelId;
    public string $chunk;

    public function __construct(Level $level, Chunk $chunk) {
        $this->state = true;
        $this->levelId = $level->getId();
        $this->chunk = $chunk->fastSerialize();
        $this->initializeAdjacentChunks($level, $chunk);
    }

    private function initializeAdjacentChunks(Level $level, Chunk $chunk): void {
        foreach ($level->getAdjacentChunks($chunk->getX(), $chunk->getZ()) as $i => $c) {
            $this->{"chunk$i"} = $c?->fastSerialize();
        }
    }

    public function onRun(): void {
        $manager = $this->getFromThreadStore("generation.level{$this->levelId}.manager");
        $generator = $this->getFromThreadStore("generation.level{$this->levelId}.generator");

        if (!($manager instanceof SimpleChunkManager) || !($generator instanceof Generator)) {
            $this->state = false;
            return;
        }

        $chunks = $this->prepareChunks();
        $this->processChunk($manager, $generator, $chunks);
        $this->finalizeChunk($manager, $generator, $chunks);
    }

    private function prepareChunks(): array {
        $chunks = [];
        $chunk = Chunk::fastDeserialize($this->chunk);

        for ($i = 0; $i < 9; ++$i) {
            if ($i === 4) {
                continue;
            }
            $xx = -1 + $i % 3;
            $zz = -1 + (int)($i / 3);
            $ck = $this->{"chunk$i"};
            $chunks[$i] = $ck === null ? new Chunk($chunk->getX() + $xx, $chunk->getZ() + $zz) : Chunk::fastDeserialize($ck);
        }

        return $chunks;
    }

    private function processChunk(SimpleChunkManager $manager, Generator $generator, array $chunks): void {
        $chunk = Chunk::fastDeserialize($this->chunk);
        $manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);

        if (!$chunk->isGenerated()) {
            $generator->generateChunk($chunk->getX(), $chunk->getZ());
            $chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
            $chunk->setGenerated();
        }

        foreach ($chunks as $i => $c) {
            $manager->setChunk($c->getX(), $c->getZ(), $c);
            if (!$c->isGenerated()) {
                $generator->generateChunk($c->getX(), $c->getZ());
                $chunks[$i] = $manager->getChunk($c->getX(), $c->getZ());
                $chunks[$i]->setGenerated();
            }
        }
    }

    private function finalizeChunk(SimpleChunkManager $manager, Generator $generator, array $chunks): void {
        $chunk = Chunk::fastDeserialize($this->chunk);
        $generator->populateChunk($chunk->getX(), $chunk->getZ());
        $chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
        $chunk->setPopulated();
        $chunk->recalculateHeightMap();
        $chunk->populateSkyLight();
        $chunk->setLightPopulated();
        $this->chunk = $chunk->fastSerialize();

        foreach ($chunks as $i => $c) {
            $this->{"chunk$i"} = $c->hasChanged() ? $c->fastSerialize() : null;
        }

        $manager->cleanChunks();
    }

    public function onCompletion(Server $server): void {
        $level = $server->getLevel($this->levelId);
        if ($level !== null) {
            if (!$this->state) {
                $level->registerGenerator();
            }

            $chunk = Chunk::fastDeserialize($this->chunk);
            $this->generateCallbackForChunks($level, $chunk);
        }
    }

    private function generateCallbackForChunks(Level $level, Chunk $chunk): void {
        for ($i = 0; $i < 9; ++$i) {
            if ($i === 4) {
                continue;
            }
            $c = $this->{"chunk$i"};
            if ($c !== null) {
                $c = Chunk::fastDeserialize($c);
                $level->generateChunkCallback($c->getX(), $c->getZ(), $this->state ? $c : null);
            }
        }
        $level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $this->state ? $chunk : null);
    }
}