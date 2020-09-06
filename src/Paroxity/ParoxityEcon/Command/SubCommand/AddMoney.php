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

class AddMoney extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			$engine,
			"add",
			"Add money to a players balance",
			["give"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.addmoney");
		
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new FloatArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$username = $args["player"];
		$money = floatval($args["money"]);

		if($money >= ParoxityEcon::getMaxMoney()){
			$money = ParoxityEcon::getMaxMoney();
		}

		$online = false;
		$string = $username;

		$player = $engine->getServer()->getPlayerExact($username);

		if(!is_null($player) && $player->isOnline()){
			$online = true;
			$string = $player->getUniqueId()->toString();
		}

		$engine->getAPI()->addMoney($string, $money, function(bool $success) use ($sender, $player, $username, $string, $online, $money): void{
			if(!$success){
				$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");

				return;
			}

			$this->engine->getAPI()->getMoney($string, function(?float $finalBalance) use ($sender, $player, $online, $username, $money): void{
				$unit = ParoxityEcon::getMonetaryUnit();

				if($online){
					$player->sendMessage("§aYour were given §6$unit" . $money . ".§aYour new balance is §6$unit" . $finalBalance);
				}

				$sender->sendMessage("§aSuccessfully added §6$unit" . "$money §ato §2$username's §aaccount. His new balance is §6$unit" . $finalBalance);
			});
		});
	}
}