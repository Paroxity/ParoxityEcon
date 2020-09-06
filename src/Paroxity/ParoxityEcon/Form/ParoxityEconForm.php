<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use Paroxity\ParoxityEcon\Database\ParoxityEconQueryIds;
use Paroxity\ParoxityEcon\Form\My\MyForm;
use Paroxity\ParoxityEcon\Form\Pay\PayForm;
use Paroxity\ParoxityEcon\Form\See\SeeForm;
use Paroxity\ParoxityEcon\Form\Top\TopForm;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\Player;
use function is_null;

class ParoxityEconForm extends MenuForm{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"§l§4Economy §cUI",
			"§2Select your desired option",

			$this->getOptions(),
			function(Player $player, int $selectedOption): void{
				$this->onSubmit($player, $selectedOption);
			}
		);
	}

	/**
	 * @return array
	 */
	public function getOptions(): array{
		$options = [
			"Pay"     => "Pay money to a player",
			"Balance" => "Check your current balance",
			"Peek"    => "See another users balance",
			"Top"     => "List top players with most money"
		];

		$buttons = [];

		foreach($options as $title => $description){
			$buttons[] = new MenuOption("§0" . $title . "\n§0•§r §4" . $description . " §r§0•§r");
		}

		return $buttons;
	}

	public function onSubmit(Player $sender, int $selectedOption): void{
		$engine = $this->engine;

		switch($selectedOption){
			case 0:
				$sender->sendForm(new PayForm($engine));
			break;

			case 1:
				$this->engine->getAPI()->getMoney($sender->getUniqueId()->toString(), function(?float $money) use ($sender): void{
					if(is_null($money)){
						$sender->sendMessage("§cSomething went wrong. Unable to get your money.");

						return;
					}

					$sender->sendForm(new MyForm($money));
				});
			break;

			case 2:
				$sender->sendForm(new SeeForm($engine));
			break;

			case 3:
				$this->engine->getAPI()->getTopPlayers(ParoxityEconQueryIds::GET_TOP_10_PLAYERS, function(array $rows) use ($sender){
					$sender->sendForm(new TopForm($rows));
				});
			break;
		}
	}
}