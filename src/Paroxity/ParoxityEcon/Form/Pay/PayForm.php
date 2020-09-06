<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\Pay;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\ParoxityEconAPI;
use pocketmine\Player;

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

		$username = (string) trim($data["player"]);
		$money = (float) trim($data["money"]);

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
						$sender->sendForm(new self($this->engine, [new Label("err", "§c§lError§r. §cYou do not have enough money to perform this transaction.\n\n")]));
					break;

					case ParoxityEconAPI::ERROR_RECEIVER_NOT_FOUND:
						$sender->sendForm(new self($this->engine, [new Label("err", "§c§lError§r§c. Player:§4 $username §ccould not be found.\n\n")]));
					break;
				}
			}
		);
	}
}