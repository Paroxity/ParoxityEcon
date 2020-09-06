<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\See;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;
use function is_null;
use function trim;

class SeeForm extends CustomForm{

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

		$return[] = new Label("label", "Check another players balance.\n\n");
		$return[] = new Input("player", "Player Name:", "Enter player name");

		return $return;
	}

	public function onSubmit(Player $sender, CustomFormResponse $response): void{
		$data = $response->getAll();

		if(empty($data)){
			return;
		}

		$string = $username = trim($data["player"]);
		$player = $this->engine->getServer()->getPlayerExact($username);

		if(!is_null($player) && $player->isOnline()){
			$string = $player->getUniqueId()->toString();
		}

		$this->engine->getAPI()->getMoney($string, function(?float $money) use ($sender, $username): void{
			if(is_null($money)){
				$sender->sendForm(new self($this->engine, [new Label("error", "§c§lError§r§c. Player:§4 $username §ccould not be found.\n\n")]));

				return;
			}

			$sender->sendMessage("§aPlayer§2 $username's §abalance is §6" . ParoxityEcon::getMonetaryUnit() . $money);
		});
	}
}