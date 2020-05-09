<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Form\Pay;

use Paroxity\ParoxityEcon\ParoxityEcon;
use Paroxity\ParoxityEcon\Session\BaseSession;
use JackMD\Forms\CustomForm\CustomForm;
use JackMD\Forms\CustomForm\CustomFormResponse;
use JackMD\Forms\CustomForm\element\Input;
use JackMD\Forms\CustomForm\element\Label;
use pocketmine\Player;
use function is_null;

class PayForm extends CustomForm{

	/** @var ParoxityEcon */
	private $engine;
	/** @var BaseSession */
	private $senderSession;

	public function __construct(ParoxityEcon $engine, BaseSession $senderSession, array $labels = []){
		$this->engine = $engine;
		$this->senderSession = $senderSession;

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
		$senderSession = $this->senderSession;

		$username = (string) trim($data["player"]);
		$money = (int) trim($data["money"]);

		if($money > $senderSession->getMoney()){
			$sender->sendForm(new self($engine, $senderSession, [new Label("err", "§c§lError§r. §cYou do not have enough money to perform this transaction.\n\n")]));

			return;
		}

		$found = $engine->getAPI()->getMoney($username, function(int $balance, ?BaseSession $session) use ($sender, $username, $money, $senderSession){
			$finalBalance = $balance + $money;

			if($finalBalance >= ParoxityEcon::getMaxMoney()){
				$sender->sendForm(new self($this->engine, $senderSession, [new Label("err", "§c§lError§r. §cUser balance plus the money added exceeds the max-money limit.\n\n")]));

				return;
			}

			$unit = ParoxityEcon::getMonetaryUnit();

			if(is_null($session)){
				$this->engine->getDatabase()->updateMoney($username, $finalBalance);
			}else{
				$session->addMoney($money);
				$session->getPlayer()->sendMessage("§aPlayer §2{$sender->getName()} §agave you §6$unit" . "$money.");
			}

			$senderSession->reduceMoney($money);

			$sender->sendMessage("§aSuccessfully paid §6$unit" . "$money §ato §2$username. §aYour balance now is §6$unit" . $senderSession->getMoney());

		});

		if(!$found){
			$sender->sendForm(new self($this->engine, $senderSession, [new Label("error", "§c§lError§r§c. Player:§4 $username §ccould not be found.\n\n")]));
		}
	}
}