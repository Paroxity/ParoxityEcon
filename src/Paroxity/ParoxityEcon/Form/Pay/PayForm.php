<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\Pay;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;
use function is_null;

class PayForm extends CustomForm{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine, array $labels = []){
		$this->engine = $engine;

		parent::__construct(
			"§c§lEconomy §4UI§r",
			$this->getFormElements($labels),

			function(Player $player, CustomFormResponse $response): void{
				$this->onSubmit($player, $response);
			}
		);
	}

	private function getFormElements(array $labels): array{
		$return = [];

		foreach($labels as $label){
			$return[] = $label;
		}

		$return[] = new Label("label", "Pay money to a player.\n\n");
		$return[] = new Input("player", "Player Name:", "Enter player name");
		$return[] = new Input("money", "Amount:", "Enter amount here");

		return $return;
	}

	public function onSubmit(Player $sender, CustomFormResponse $response): void{
		$data = $response->getAll();

		if(empty($data)){
			return;
		}

		$engine = $this->engine;

		$username = (string) trim($data["player"]);
		$money = (float) trim($data["money"]);

		// gets senders money and check if he has enough money
		$engine->getAPI()->getMoney($sender->getUniqueId()->toString(), true,
			function(?float $sendersBalance) use ($sender, $username, $money): void{
				if(is_null($sendersBalance)){
					$sender->sendMessage("§cSomething went wrong. Unable to get your money.");

					return;
				}

				if($money > $sendersBalance){
					$sender->sendForm(new self($this->engine, [new Label("err", "§c§lError§r. §cYou do not have enough money to perform this transaction.\n\n")]));

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
							$sender->sendForm(new self($this->engine, [new Label("error", "§c§lError§r§c. Player:§4 $username §ccould not be found.\n\n")]));

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