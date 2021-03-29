<?php
declare(strict_types = 1);

namespace Paroxity\ParoxityEcon\Database;

interface ParoxityEconQueryIds{

	public const ECO_INIT     = "paroxityecon.economy.init";
	public const ECO_REGISTER = "paroxityecon.economy.register";

	public const ADD_BY_USERNAME = "paroxityecon.economy.add.by-username";
	public const ADD_BY_UUID     = "paroxityecon.economy.add.by-uuid";

	public const DEDUCT_BY_USERNAME = "paroxityecon.economy.deduct.by-username";
	public const DEDUCT_BY_UUID     = "paroxityecon.economy.deduct.by-uuid";

	public const SET_BY_USERNAME = "paroxityecon.economy.set.by-username";
	public const SET_BY_UUID     = "paroxityecon.economy.set.by-uuid";

	public const GET_BY_USERNAME = "paroxityecon.economy.get.by-username";
	public const GET_BY_UUID     = "paroxityecon.economy.get.by-uuid";

	public const GET_TOP_PLAYERS    = "paroxityecon.economy.get.top";
	public const GET_TOP_10_PLAYERS = "paroxityecon.economy.get.top10";

	public const CURRENCY_INIT       = "paroxityecon.currency.init";
	public const CURRENCY_ADD        = "paroxityecon.currency.add";
	public const CURRENCY_UPDATE     = "paroxityecon.currency.update";
	public const CURRENCY_GET_ALL    = "paroxityecon.currency.get.all";
	public const CURRENCY_GET_VIA_ID = "paroxityecon.currency.get.via-id";
	public const CURRENCY_DELETE     = "paroxityecon.currency.delete";
}