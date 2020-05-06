<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form;

use Paroxity\ParoxityEcon\Form\My\MyForm;
use Paroxity\ParoxityEcon\Form\Pay\PayForm;
use Paroxity\ParoxityEcon\Form\See\SeeForm;
use Paroxity\ParoxityEcon\Form\Top\TopForm;
use Paroxity\ParoxityEcon\ParoxityEcon;
use JackMD\Forms\MenuForm\MenuForm;
use JackMD\Forms\MenuForm\MenuOption;
use Paroxity\ParoxityEcon\Utils\ParoxityEconQuery;
use pocketmine\Player;
use function is_null;

class EconomyForm extends MenuForm{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"§l§4Economy §cUI",
			"§2Select your desired option",

			$this->getOptions(),
			function(Player $player, string $selectedOption): void{
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
			$buttons[] = new MenuOption($title, "§0" . $title . "\n§0•§r §4" . $description . " §r§0•§r");
		}

		return $buttons;
	}

	public function onSubmit(Player $sender, string $selectedOption): void{
		$engine = $this->engine;

		$senderName = $sender->getName();
		$senderSession = $engine->getSessionManager()->getSession($senderName);

		if(is_null($senderSession)){
			throw new \RuntimeException("Player {$sender->getName()} session is null in Economy");
		}

		switch($selectedOption){
			case "balance":
				$sender->sendForm(new MyForm($engine, $senderSession->getMoney()));
			break;

			case "peek":
				$sender->sendForm(new SeeForm($engine));
			break;

			case "pay":
				$sender->sendForm(new PayForm($engine, $senderSession));
			break;

			case "top":
				$this->engine->getDatabase()->getTopPlayers(ParoxityEconQuery::GET_TOP_10_PLAYERS, function(array $rows) use ($sender){
					$sender->sendForm(new TopForm($this->engine, $rows));
				});
			break;
		}
	}
}