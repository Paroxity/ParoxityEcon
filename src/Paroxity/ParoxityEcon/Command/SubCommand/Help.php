<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use function implode;

class Help extends BaseSubCommand{

	public function __construct(ParoxityEcon $engine){
		parent::__construct(
			$engine,
			"help",
			"Access to ParoxityEcon help page."
		);
	}

	public function prepare(): void{
		$this->setPermission("paroxityecon.command.help");
		$this->registerArgument(0, new IntegerArgument("page", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
		if(empty($args)){
			$pageNumber = 1;
		}elseif(isset($args["page"]) && is_numeric($args["page"])){
			$pageNumber = (int) $args["page"];

			if($pageNumber <= 0){
				$pageNumber = 1;
			}
		}else{
			$sender->sendMessage("§cPage number must be numeric.");

			return;
		}

		$parent = $this->parent;
		$subCommands = $parent->getSubCommands();

		/** @var BaseSubCommand[] $commands */
		$commands = [];

		foreach($subCommands as $command){
			if($sender->hasPermission($command->getPermission())){
				$commands[$command->getName()] = $command;
			}
		}

		ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);

		$commands = array_chunk($commands, $sender->getScreenLineHeight());
		$pageNumber = (int) min(count($commands), $pageNumber);

		$sender->sendMessage("§6Paroxity§eEcon §aHelp Page §f[§c" . $pageNumber . "§f/§c" . count($commands) . "§f]");

		foreach($commands[$pageNumber - 1] as $command){
			$message = "§c-§r §2" . $command->getUsageMessage() . "§b:§r §f" . $command->getDescription();

			if(!empty($command->getAliases())){
				$message .= " (Alias: " . implode(", ", $command->getAliases()) . ")";
			}

			$sender->sendMessage($message);
		}
	}
}