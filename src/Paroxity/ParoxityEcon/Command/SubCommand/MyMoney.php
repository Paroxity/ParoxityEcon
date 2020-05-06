<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command\SubCommand;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use function is_null;

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

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		$session = $this->engine->getSessionManager()->getSession($sender->getName());

		if(is_null($session)){
			throw new \RuntimeException("Player {$sender->getName()} session is null in Economy");
		}

		$sender->sendMessage("§aYour balance: §6" . ParoxityEcon::$MONETARY_UNIT . $session->getMoney());
	}
}