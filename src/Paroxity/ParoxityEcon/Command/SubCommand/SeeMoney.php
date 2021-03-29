<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use function is_null;

class SeeMoney extends BaseSubCommand{

	private ParoxityEcon $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			$engine,
			"see",
			"Check a players balance.",
			["peek"]
		);
	}


	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.seemoney");

		$this->registerArgument(0, new ParoxityEconPlayerArgument());
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$string = $username = $args["player"];
		$player = $engine->getServer()->getPlayerExact($username);

		if(!is_null($player) && $player->isOnline()){
			$string = $player->getUniqueId()->toString();
		}

		$engine->getAPI()->getMoney($string, function(?float $money) use ($sender, $username): void{
			if(is_null($money)){
				$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");

				return;
			}

			$sender->sendMessage("§aPlayer§2 $username's §abalance is §6" . ParoxityEcon::getMonetaryUnit() . $money);
		});
	}
}