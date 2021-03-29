<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\Top;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;

class TopForm extends CustomForm{

	private array $data = [];

	public function __construct(array $data){
		$this->data = $data;

		parent::__construct(
			"§c§lEconomy §4UI§r",
			$this->getFormElements(),
			function(Player $player, CustomFormResponse $response): void{
			}
		);
	}

	private function getFormElements(): array{
		$text = "§aTop 10 players with most balance.\n\n";
		$i = 1;

		foreach($this->data as $data){
			$playerName = $data["display_name"];
			$money = $data["money"];

			$text .= "§e$i §l§c»§r §2$playerName §l§b»§r §fwith: §6" . ParoxityEcon::getMonetaryUnit() . $money . "\n";

			$i++;
		}

		return [
			new Label("label", $text)
		];
	}
}