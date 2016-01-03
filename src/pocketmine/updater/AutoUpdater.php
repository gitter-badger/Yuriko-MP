<?php

/*
 *
 * __   __          _ _               __  __ ____  
 * \ \ / /   _ _ __(_) | _____       |  \/  |  _ \ 
 *  \ V / | | | '__| | |/ / _ \ _____| |\/| | |_) |
 *   | || |_| | |  | |   < (_) |_____| |  | |  __/ 
 *   |_| \__,_|_|  |_|_|\_\___/      |_|  |_|_|
 *
 * Yuriko-MP, a kawaii-powered PocketMine-based software
 * for Minecraft: Pocket Edition
 * Copyright 2016 ItalianDevs4PM.
 *
 * This work is licensed under the Creative Commons
 * Attribution-NonCommercial-NoDerivatives 4.0
 * International License.
 * 
 *
 * @author ItalianDevs4PM
 * @link   http://github.com/ItalianDevs4PM
 *
 *
 */

namespace pocketmine\updater;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\VersionString;

class AutoUpdater{

	/** @var Server */
	protected $server;
	protected $endpoint;
	protected $hasUpdate = false;
	protected $updateInfo = null;

	public function __construct(Server $server, $endpoint){
		$this->server = $server;
		$this->endpoint = "http://$endpoint/api/";

		if(/*$server->getProperty("auto-updater.enabled", true)*/false == true){ // Working in an updater server
			$this->check();
			if($this->hasUpdate()){
				if($this->server->getProperty("auto-updater.on-update.warn-console", true)){
					$this->showConsoleUpdate();
				}
			}elseif($this->server->getProperty("auto-updater.preferred-channel", true)){
				$version = new VersionString();
				if(!$version->isDev() and $this->getChannel() !== "stable"){
					$this->showChannelSuggestionStable();
				}elseif($version->isDev() and $this->getChannel() === "stable"){
					$this->showChannelSuggestionBeta();
				}
			}
		}
	}

	protected function check(){
		$response = Utils::getURL($this->endpoint . "?channel=" . $this->getChannel(), 4);
		$response = json_decode($response, true);
		if(!is_array($response)){
			return;
		}

		$this->updateInfo = [
			"version" => $response["version"],
			"api_version" => $response["api_version"],
			"build" => $response["build"],
			"date" => $response["date"],
			"details_url" => isset($response["details_url"]) ? $response["details_url"] : null,
			"download_url" => $response["download_url"]
		];

		$this->checkUpdate();
	}

	/**
	 * @return bool
	 */
	public function hasUpdate(){
		return $this->hasUpdate;
	}

	public function showConsoleUpdate(){
		$logger = $this->server->getLogger();
		$newVersion = new VersionString($this->updateInfo["version"]);
		$logger->warning("----- Yuriko-MP Auto Updater -----");
		$logger->warning("Your version of Yuriko-MP is outdated. Version " . $newVersion->get(false) . " (build #" . $newVersion->getBuild() . ") was released on " . date("D M j h:i:s Y", $this->updateInfo["date"]));
		if($this->updateInfo["details_url"] !== null){
			$logger->warning("Details: " . $this->updateInfo["details_url"]);
		}
		$logger->warning("Download: " . $this->updateInfo["download_url"]);
		$logger->warning("----- -------------------------- -----");
	}

	public function showPlayerUpdate(Player $player){
		$player->sendMessage(TextFormat::DARK_PURPLE . "The version of Yuriko-MP that this server is running is out of date. Please consider updating to the latest version.");
		$player->sendMessage(TextFormat::DARK_PURPLE . "Check the console for more details.");
	}

	protected function showChannelSuggestionStable(){
		$logger = $this->server->getLogger();
		$logger->info("----- Yuriko-MP Auto Updater -----");
		$logger->info("It appears you're running a Stable build, when you've specified that you prefer to run " . ucfirst($this->getChannel()) . " builds.");
		$logger->info("If you would like to be kept informed about new Stable builds only, it is recommended that you change 'preferred-channel' in your pocketmine.yml to 'stable'.");
		$logger->info("----- -------------------------- -----");
	}

	protected function showChannelSuggestionBeta(){
		$logger = $this->server->getLogger();
		$logger->info("----- Yuriko-MP Auto Updater -----");
		$logger->info("It appears you're running a Beta build, when you've specified that you prefer to run Stable builds.");
		$logger->info("If you would like to be kept informed about new Beta or Development builds, it is recommended that you change 'preferred-channel' in your pocketmine.yml to 'beta' or 'development'.");
		$logger->info("----- -------------------------- -----");
	}

	public function getUpdateInfo(){
		return $this->updateInfo;
	}

	public function doCheck(){
		$this->check();
	}

	protected function checkUpdate(){
		if($this->updateInfo === null){
			return;
		}
		$currentVersion = new VersionString($this->server->getPocketMineVersion());
		$newVersion = new VersionString($this->updateInfo["version"]);

		if($currentVersion->compare($newVersion) > 0 and ($currentVersion->get() !== $newVersion->get() or $currentVersion->getBuild() > 0)){
			$this->hasUpdate = true;
		}else{
			$this->hasUpdate = false;
		}

	}

	public function getChannel(){
		$channel = strtolower($this->server->getProperty("auto-updater.preferred-channel", "stable"));
		if($channel !== "stable" and $channel !== "beta" and $channel !== "development"){
			$channel = "stable";
		}

		return $channel;
	}
}