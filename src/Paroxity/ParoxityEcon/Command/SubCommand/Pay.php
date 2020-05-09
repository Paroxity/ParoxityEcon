<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Paroxity\ParoxityEcon\Command\Argument\ParoxityEconPlayerArgument;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use function floatval;
use function is_null;

class Pay extends BaseSubCommand{

	// THIS CLASS IS A PRIME EXAMPLE OF WHAT YOU CALL AS CALLBACK HELL!

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
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

	/**
	 * @param CommandSender|Player $sender
	 */
	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$engine = $this->engine;

		$username = $args["player"];
		$money = floatval($args["money"]);

		// gets senders money and check if he has enough money
		$engine->getAPI()->getMoney($sender->getUniqueId()->toString(), true,
			function(?float $sendersBalance) use ($sender, $username, $money): void{
				if($money > $sendersBalance){
					$sender->sendMessage("§cYou do not have enough money to perform this transaction.");

					return;
				}

				$online = false;
				$string = $username;

				$player = $this->engine->getServer()->getPlayerExact($username);

				if(!is_null($player) && $player->isOnline()){
					$online = true;
					$string = $player->getUniqueId()->toString();
				}

				// add the money to the targets balance
				$this->engine->getAPI()->addMoney($string, $money, $online,
					function(bool $success) use ($sender, $username, $money, $online, $player, $string, $sendersBalance): void{
						if(!$success){
							$sender->sendMessage("§cUnable to perform the transaction. Something went wrong.");

							return;
						}

						// before adding to targets balance, deduct from senders first
						$this->engine->getAPI()->deductMoney($sender->getUniqueId()->toString(), $money, true,
							function(bool $success) use ($sender, $player, $username, $string, $online, $money, $sendersBalance): void{
								if(!$success){
									$this->engine->getAPI()->deductMoney($string, $money, $online); // remove the money that was added to targets balance

									$sender->sendMessage("§cUnable to perform the transaction. Something went wrong.");

									return;
								}

								// target balance was added and senders money was deducted. proceed...
								// get targets updated balance
								$this->engine->getAPI()->getMoney($string, $online,
									function(?float $usersBalance) use ($sender, $username, $money, $online, $player, $sendersBalance): void{
										$unit = ParoxityEcon::getMonetaryUnit();

										if($online){
											$player->sendMessage("§aPlayer: §2{$sender->getName()} §agave you §6$unit" . $money . "§a. Your new balance is §6$unit" . $usersBalance);
										}

										$sender->sendMessage("§aSuccessfully paid §6$unit" . "$money §ato §2$username. §aYour balance now is §6$unit" . $sendersBalance);
									}
								);
							}
						);
					}
				);
			}
		);
	}
}