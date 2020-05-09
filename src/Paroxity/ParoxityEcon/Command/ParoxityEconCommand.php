<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Command;

use CortexPE\Commando\BaseCommand;
use Paroxity\ParoxityEcon\Command\SubCommand\Help;
use Paroxity\ParoxityEcon\Form\ParoxityEconForm;
use Paroxity\ParoxityEcon\ParoxityEcon;
use pocketmine\command\CommandSender;
use Paroxity\ParoxityEcon\Command\SubCommand\AddMoney;
use Paroxity\ParoxityEcon\Command\SubCommand\MyMoney;
use Paroxity\ParoxityEcon\Command\SubCommand\Pay;
use Paroxity\ParoxityEcon\Command\SubCommand\ReduceMoney;
use Paroxity\ParoxityEcon\Command\SubCommand\SeeMoney;
use Paroxity\ParoxityEcon\Command\SubCommand\Set;
use Paroxity\ParoxityEcon\Command\SubCommand\TopMoney;
use pocketmine\Player;

class ParoxityEconCommand extends BaseCommand{

	/** @var ParoxityEcon */
	private $engine;

	public function __construct(ParoxityEcon $engine){
		$this->engine = $engine;

		parent::__construct(
			"eco",
			"Access to servers economy.",
			["economy"]
		);
	}

	protected function prepare(): void{
		$this->setPermission("paroxityecon.command.use");

		$engine = $this->engine;

		$subCommands = [
			new Set($engine),
			new AddMoney($engine),
			new MyMoney($engine),
			new Pay($engine),
			new ReduceMoney($engine),
			new SeeMoney($engine),
			new TopMoney($engine),
			new Help()
		];

		foreach($subCommands as $subCommand){
			$this->registerSubCommand($subCommand);
		}
	}

	public function onRun(CommandSender $sender, string $alias, array $args): void{
		if($sender instanceof Player){
			$sender->sendForm(new ParoxityEconForm($this->engine));

			return;
		}

		$this->sendUsage();
	}
}