<?php

declare(strict_types=1);

namespace Vecnavium\SkyblocksPM\generator;

use pocketmine\player\Player;
use pocketmine\world\Position;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Vecnavium\SkyblocksPM\SkyBlocksPM;

class Generator {

    private SkyBlocksPM $plugin;
    
    public function __construct(SkyBlocksPM $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     * @return void
     *
     * Thanks SkyWars by GamakCZ
     */
    public function setIslandWorld(Player $player): void {
        $world = $player->getWorld();
        $world->setSpawnLocation($player->getPosition());
        $worldPath = $this->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world->getFolderName();

        if ($world->getDisplayName() === $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getDisplayName()) {
            $player->sendMessage($this->plugin->getMessages()->getMessage('default-world'));
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($worldPath)), RecursiveIteratorIterator::LEAVES_ONLY);

        $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        $this->plugin->getServer()->getWorldManager()->unloadWorld($world);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if (!$file->isFile()) continue;

            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen($this->plugin->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $world->getFolderName()));
            @mkdir($this->plugin->getDataFolder() . "cache/island/db");
            copy($filePath, $this->plugin->getDataFolder() . "cache/island/" . $localPath);
        }

    }

    /**
     * @param Player $player
     * @param string $folderName
     * @param string $name
     *
     * Thanks SkyWars by GamakCZ
     */
    public function generateIsland(Player $player, string $folderName, string $name): void{
        $path = $this->plugin->getDataFolder() . "cache/island";
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($path)), RecursiveIteratorIterator::LEAVES_ONLY);

        $path = $this->plugin->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName;
        @mkdir($path);
        @mkdir($path . "/db");

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
            $localPath = substr($filePath, strlen($this->plugin->getDataFolder() . 'cache/island'));
            if ($file->isDir()) {
                @mkdir($path . $localPath);
                continue;
            }
            copy($filePath,  $path . DIRECTORY_SEPARATOR . $localPath);
        }

        $this->plugin->getServer()->getWorldManager()->loadWorld($folderName);
        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($folderName);
        $player->teleport(Position::fromObject($world->getSpawnLocation(), $world));
        $this->plugin->getSkyBlockManager()->createSkyBlock($world->getFolderName(), $this->plugin->getPlayerManager()->getPlayerByPrefix($player->getName()), $name, $world);
    }

}
