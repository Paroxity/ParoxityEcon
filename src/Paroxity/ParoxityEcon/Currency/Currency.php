<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Currency;

class Currency{

	public const SYMBOL_POS_START = "start";
	public const SYMBOL_POS_END   = "end";

	private string $id;
	private string $name;
	private string $symbol;
	private string $symbolPosition;
	private float $startingAmount;
	private float $maximumAmount;

	public function __construct(string $id, string $name, string $symbol, string $symbolPosition, float $startingAmount, float $maximumAmount){
		$this->id = $id;
		$this->name = $name;
		$this->symbol = $symbol;
		$this->symbolPosition = $symbolPosition;
		$this->startingAmount = $startingAmount;
		$this->maximumAmount = $maximumAmount;
	}

	public function getId(): string{
		return $this->id;
	}

	public function getName(): string{
		return $this->name;
	}

	public function getSymbol(): string{
		return $this->symbol;
	}

	public function getSymbolPosition(): string{
		return $this->symbolPosition;
	}

	public function getStartingAmount(): float{
		return $this->startingAmount;
	}

	public function getMaximumAmount(): float{
		return $this->maximumAmount;
	}

	public function isSymbolAtStart(): bool{
		return $this->symbolPosition === self::SYMBOL_POS_START;
	}
}