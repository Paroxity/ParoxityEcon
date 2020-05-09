<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\args\FloatArgument;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use function floatval;
use function is_null;

class Set extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"set",
			"Set money of a player.",
			["setmoney"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.setmoney");
		
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new FloatArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$username = $args["player"];
		$money = floatval($args["money"]);

		if($money > ParoxityEcon::getMaxMoney()){
			$money = ParoxityEcon::getMaxMoney();
		}

		$online = false;
		$string = $username;

		$player = $engine->getServer()->getPlayerExact($username);

		if(!is_null($player) && $player->isOnline()){
			$online = true;
			$string = $player->getUniqueId()->toString();
		}

		$engine->getAPI()->setMoney($string, $money, $online, function(bool $success) use ($sender, $player, $username, $online, $money): void{
			if(!$success){
				$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");

				return;
			}

			if($online){
				$player->sendMessage("§aYour money was set to§6 " . ParoxityEcon::getMonetaryUnit() . $money);
			}

			$sender->sendMessage("§aSuccessfully set §2$username's§a money to §6" . ParoxityEcon::getMonetaryUnit() . $money);
		});
	}
}