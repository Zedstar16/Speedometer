<?php

declare(strict_types=1);

namespace Zedstar16\Speedometer;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

class Speedometer extends PluginBase implements Listener
{

    public static $data = [];

    public $lastpos = [];

    public $config;

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
        $this->saveDefaultConfig();
        $this->config = $this->getConfig()->getAll();
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $p = $event->getPlayer();
        $name = $p->getName();
        $pos = $p->getPosition()->asVector3();
        try {
            if ($event->getTo()->distance($event->getFrom()) > 0) {
                if (!isset($this->lastpos[$name][0])) {
                    $this->lastpos[$name][0]["pos"] = $pos;
                    $this->lastpos[$name][0]["time"] = microtime(true);
                } else {
                    array_unshift($this->lastpos[$name], [
                        "pos" => $pos,
                        "time" => microtime(true)
                    ]);
                    if (count($this->lastpos[$name][0]) > 2) {
                        array_pop($this->lastpos[$name]);
                    }
                    if (!isset($this->data[$name])) {
                        self::$data[$name] = [];
                    }
                    $distance = $this->lastpos[$name][0]["pos"]->distance($this->lastpos[$name][1]["pos"]);
                    $time = $this->lastpos[$name][0]["time"] - $this->lastpos[$name][1]["time"];
                    array_unshift(self::$data[$name], [
                        "distance" => $distance,
                        "time" => $time,
                        "timestamp" => microtime(true)
                    ]);
                    if (count(self::$data[$name]) > 100) {
                        array_pop(self::$data[$name]);
                    }
                }
                if ($this->config["display-speed"]) {
                    if (($this->config["display-only-with-elytra"] && $p->getArmorInventory()->getChestplate()->getId() === ItemIds::ELYTRA) or !$this->config["display-only-with-elytra"]) {
                        $speed = self::calculateSpeed($p, 2);
                        if ($speed !== null) {
                            $string = "§fSpeed: §b" . $speed . " §fm/s";
                            switch ($this->config["display-type"]) {
                                case "popup":
                                    $p->sendPopup($string);
                                    break;
                                case "tip":
                                    $p->sendTip($string);
                                    break;
                                case "action-bar":
                                    $p->addActionBarMessage($string);
                                    break;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $error) {
            $this->getLogger()->error($error->getMessage() . " on Line " . $error->getLine());
        }
    }

    /**
     * @param Player $p
     * @param int $precision
     * @return float|null
     */
    public static function calculateSpeed(Player $p, int $precision = 2): ?float
    {
        try {
            $name = $p->getName();
            if (isset(self::$data[$name])) {
                $data = array_filter(self::$data[$name], function ($entry): bool {
                    return (microtime(true) - $entry["timestamp"]) <= 1;
                });
                $speeds = [];
                foreach ($data as $entry) {
                    $time = $entry["time"];
                    $speeds[] = ($entry["distance"] / $time);
                }
                return round(array_sum($speeds) / count($speeds), $precision);
            }
        } catch (\Throwable $error) {
            Server::getInstance()->getLogger()->error($error->getMessage() . " on Line " . $error->getLine());
        }
        return null;
    }
}
