<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Session\BaseSession;
use pocketmine\command\CommandSender;

class SeeMoney extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"see",
			"Check a players balance.",
			["peek"]
		);
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$username = $args["player"];

		$found = $this->engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username){
			$sender->sendMessage("§aPlayer§2 $username's §abalance is §6" . ParoxityEcon::$MONETARY_UNIT . $balance);
		});

		if(!$found){
			$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");
		}
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.seemoney");

		$this->registerArgument(0, new ParoxityEconPlayerArgument());
	}
}