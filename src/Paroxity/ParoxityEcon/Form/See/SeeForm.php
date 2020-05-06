<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\See;

use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Session\BaseSession;
use JackMD\Forms\CustomForm\CustomForm;
use JackMD\Forms\CustomForm\CustomFormResponse;
use JackMD\Forms\CustomForm\element\Input;
use JackMD\Forms\CustomForm\element\Label;
use pocketmine\Player;
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

		$username = trim($data["player"]);

		$found = $this->engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username){
			$sender->sendMessage("§aPlayer§2 $username's §abalance is §6" . ParoxityEcon::$MONETARY_UNIT . $balance);
		});

		if(!$found){
			$sender->sendForm(new self($this->engine, [new Label("error", "§c§lError§r§c. Player:§4 $username §ccould not be found.\n\n")]));
		}
	}
}