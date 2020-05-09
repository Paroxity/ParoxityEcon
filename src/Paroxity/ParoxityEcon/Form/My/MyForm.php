<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\My;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;

class MyForm extends CustomForm{

	/** @var float */
	private $money;

	public function __construct(float $money){
		$this->money = $money;

		parent::__construct(
			"§c§lEconomy §4UI§r",
			$this->getFormElements(),
			function(Player $player, CustomFormResponse $response): void{
			}
		);
	}

	private function getFormElements(): array{
		return [
			new Label("label", "§aYour current balance is: §6" . ParoxityEcon::getMonetaryUnit() . $this->money)
		];
	}
}