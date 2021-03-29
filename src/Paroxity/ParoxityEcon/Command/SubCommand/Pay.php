<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\ParoxityEconAPI;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use function floatval;

class Pay extends BaseSubCommand{

	private ParoxityEcon $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			$engine,
			"pay",
			"Give money to a player."
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.pay");

		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new ParoxityEconPlayerArgument());
		$this->registerArgument(1, new FloatArgument("money"));
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		// shut up phpstan and phpstorm
		if(!$sender instanceof Player){
			return;
		}

		$username = $args["player"];
		$money = floatval($args["money"]);

		$this->engine->getAPI()->pay(
			$sender->getName(),
			$username,
			$money,

			function(bool $success, ?float $sendersBalance, ?float $receiversBalance, int $errorCode) use ($sender, $username, $money): void{
				if($success){
					$unit = ParoxityEcon::getMonetaryUnit();
					$player = $this->engine->getServer()->getPlayerExact($username);

					if($player instanceof Player && $player->isOnline()){
						$player->sendMessage("§aPlayer: §2{$sender->getName()} §agave you §6$unit" . $money . "§a. Your new balance is §6$unit" . $receiversBalance);

					}

					$sender->sendMessage("§aSuccessfully paid §6$unit" . "$money §ato §2$username. §aYour balance now is §6$unit" . $sendersBalance);

					return;
				}

				switch($errorCode){
					default:
					case ParoxityEconAPI::ERROR_CAUSE_UNKNOWN:
						$sender->sendMessage("§cUnable to perform the transaction. Something went wrong.");
					break;

					case ParoxityEconAPI::ERROR_SENDER_NOT_FOUND:
						$sender->sendMessage("§cSomething went wrong. Unable to get your money.");
					break;

					case ParoxityEconAPI::ERROR_SENDER_LOW_ON_BALANCE:
						$sender->sendMessage("§cYou do not have enough money to perform this transaction.");
					break;

					case ParoxityEconAPI::ERROR_RECEIVER_NOT_FOUND:
						$sender->sendMessage("§cPlayer:§4 $username §ccould not be found.");
					break;
				}
			}
		);
	}
}