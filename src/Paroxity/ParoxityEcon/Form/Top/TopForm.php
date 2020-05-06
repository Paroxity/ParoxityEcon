<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\Top;

use JackMD\Forms\CustomForm\CustomForm;
use JackMD\Forms\CustomForm\CustomFormResponse;
use JackMD\Forms\CustomForm\element\Label;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;

class TopForm extends CustomForm{

	/** @var ParoxityEcon */
	private $engine;
	/** @var array */
	private $data;

	public function __construct(ParoxityEcon $engine, array $data){
		$this->engine = $engine;
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
			$playerName = $data["player"];
			$money = $data["money"];

			$text .= "§e$i §l§c»§r §2$playerName §l§b»§r §fwith: §6" . ParoxityEcon::$MONETARY_UNIT . $money . "\n";

			$i++;
		}

		return [
			new Label("label", $text)
		];
	}
}