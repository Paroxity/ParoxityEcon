<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\My;

use JackMD\Forms\CustomForm\CustomForm;
use JackMD\Forms\CustomForm\CustomFormResponse;
use JackMD\Forms\CustomForm\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;

class MyForm extends CustomForm{

	/** @var ParoxityEcon */
	private $engine;

	/** @var int */
	private $money;

	/**
	 * MyForm constructor.
	 *
	 * @param ParoxityEcon $engine
	 * @param int           $money
	 */
	public function __construct(ParoxityEcon $engine, int $money){
		$this->engine = $engine;
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