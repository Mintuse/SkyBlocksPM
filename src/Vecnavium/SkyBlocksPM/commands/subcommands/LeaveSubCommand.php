<?php

declare(strict_types=1);

namespace Vecnavium\SkyBlocksPM\commands\subcommands;

use Vecnavium\SkyBlocksPM\libs\CortexPE\Commando\BaseSubCommand;
use Vecnavium\SkyBlocksPM\skyblock\SkyBlock;
use Vecnavium\SkyBlocksPM\SkyBlocksPM;
use Vecnavium\SkyBlocksPM\player\Player;
use pocketmine\player\Player as P;
use pocketmine\command\CommandSender;
use function array_search;

class LeaveSubCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission('skyblockspm.leave');
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        /** @var SkyBlocksPM $plugin */
        $plugin = $this->getOwningPlugin();
        
        if (!$sender instanceof P) return;

        $skyblockPlayer = $plugin->getPlayerManager()->getPlayerByPrefix($sender->getName());
        if (!$skyblockPlayer instanceof Player) return;

        if ($skyblockPlayer->getSkyBlock() == '') {
            $sender->sendMessage($plugin->getMessages()->getMessage('no-sb'));
            return;
        }
        $skyblock = $plugin->getSkyBlockManager()->getSkyBlockByUuid($skyblockPlayer->getSkyBlock());
        if ($skyblock instanceof SkyBlock) {
            if($skyblock->getLeader() == $sender->getName()) {
                $sender->sendMessage($plugin->getMessages()->getMessage('no-leave'));
                return;
            }
            $skyblockPlayer->setSkyBlock('');
            $members = $skyblock->getMembers();
            unset($members[array_search($sender->getName(), $members)]);
            $skyblock->setMembers($members);
            foreach ($skyblock->getMembers() as $member) {
                $mbr = $plugin->getServer()->getPlayerByPrefix($member);
                if ($mbr instanceof P)
                    $mbr->sendMessage($plugin->getMessages()->getMessage('member-left', [
                        "{PLAYER}" => $sender->getName()
                    ]));
            }
        }
    }
}
