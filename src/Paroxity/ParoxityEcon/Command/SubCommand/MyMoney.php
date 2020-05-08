<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class MyMoney extends BaseSubCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"my",
			"See your current balance.",
			["mymoney"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.mymoney");
		
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	/**
	 * @param CommandSender|Player $sender
	 */
	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$this->engine->getAPI()->getMoney($sender->getUniqueId()->toString(), true, function(?float $money) use ($sender): void{
			$sender->sendMessage("§aYour §abalance is §6" . ParoxityEcon::$MONETARY_UNIT . $money);
		});
	}
}