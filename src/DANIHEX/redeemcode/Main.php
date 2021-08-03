<?php

declare(strict_types = 1);

namespace DANIHEX\redeemcode;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat as TF;
use DANIHEX\redeemcode\libs\jojoe77777\FormAPI\CustomForm;

class Main extends PluginBase implements Listener {

    public $codes = [];
    public $df;

    public function onLoad(){
        $this->df = $this->getDataFolder();
        @mkdir($this->df);
        @mkdir($this->df . "codes");
    }

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
        switch ($command->getName()) {
            case "generatecode":
                if($sender instanceof Player){
                    if($sender->hasPermission("redeemcode.command.generatecode")){
                        if(count($args) === 1){
                            $code = $args[0];
                            if(isset($this->codes[$sender->getName()])) unset($this->codes[$sender->getName()]);
                            $this->codes[$sender->getName()] = $code;
                            $this->openGenerateForm($sender);
                        } else {
                            $sender->sendMessage(TF::YELLOW . "Usage: /generatecode <text:code>");
                        }
                    } else {
                        $sender->sendMessage(TF::RED . "You don't have permission to use this command");
                    }
                } else {
                    $sender->sendMessage(TF::RED . "You can use this command only in-game!");
                }
            break;
            case "redeem":
                if($sender instanceof Player){
                    if($sender->hasPermission("redeemcode.command.redeem")){
                        if(count($args) === 1){
                            $code = $args[0];
                            $this->redeem($sender, $code);
                        } else {
                            $this->openRedeemForm($sender);
                        }
                    } else {
                        $sender->sendMessage(TF::RED . "You don't have permission to use this command");
                    }
                } else {
                    $sender->sendMessage(TF::RED . "You can use this command only in-game!");
                }
            break;
        }
        return true;
    }

    public function generate($user, string $code, string $command, int $times = 1){
        if(!file_exists($this->df . "codes/" . $code . ".json")){
            $json = [
                "code" => $code,
                "command" => $command,
                "times" => $times,
                "uses" => []
            ];
            $f = fopen($this->df . "codes/" . $code . ".json", "w");
            fwrite($f, json_encode($json));
            fclose($f);
            if($user instanceof Player){
                $user->sendMessage(TF::GREEN . "Redeem code generated!");
            }
        } else {
            $json = [
                "code" => $code,
                "command" => $command,
                "times" => $times,
                "uses" => []
            ];
            unlink($this->df . "codes/" . $code . ".json");
            $f = fopen($this->df . "codes/" . $code . ".json", "w");
            fwrite($f, json_encode($json));
            fclose($f);
            if($user instanceof Player){
                $user->sendMessage(TF::GREEN . "Redeem code updated!");
            }
        }
    }

    public function redeem(Player $player, string $code){
        if(file_exists($this->df . "codes/" . $code . ".json")){
            $json = json_decode(file_get_contents($this->df . "codes/" . $code . ".json"), true);
            if($json["times"] === -1){
                if(!isset($json["uses"][$player->getName()])){
                    $json["uses"][$player->getName()] = true;
                    file_put_contents($this->df . "codes/" . $code . ".json", json_encode($json));
                    $command = str_replace(["{player}"], [$player->getName()], $json["command"]);
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
                    $player->sendMessage(TF::YELLOW . "Redeem code successfuly used!");
                } else {
                    $player->sendMessage(TF::RED . "You have used this code before.");
                }
            } elseif($json["times"] > 0) {
                if(!isset($json["uses"][$player->getName()])){
                    $json["times"] = $json["times"] - 1;
                    $json["uses"][$player->getName()] = true;
                    file_put_contents($this->df . "codes/" . $code . ".json", json_encode($json));
                    $command = str_replace(["{player}"], [$player->getName()], $json["command"]);
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
                    $player->sendMessage(TF::YELLOW . "Redeem code successfuly used!");
                } else {
                    $player->sendMessage(TF::RED . "You have used this code before.");
                }
            } else {
                $player->sendMessage(TF::YELLOW . "Sorry, The redeem code has expired!");
            }
        } else {
            $player->sendMessage(TF::YELLOW . "Oops, The redeem code does not exist!");
        }
    }

    public function openGenerateForm(Player $player, string $label = ""){
        $form = new CustomForm(function (Player $player, array $data = null){
            if($data === null) return true;
            if(!empty($data[1]) and !empty($data[2])){
                $times = (int) $data[2];
                if(is_string($data[1]) and is_int($times)){
                    if($times < -1 or $times === 0){
                        $this->openGenerateForm($player, TF::RED . "You can only type -1 to make the code infinite or 1 and bigger numbers in the second input.");
                    } else {
                        if(isset($this->codes[$player->getName()])){
                            $this->generate($player, $this->codes[$player->getName()], $data[1], $times);
                            unset($this->codes[$player->getName()]);
                        } else {
                            $player->sendMessage(TF::RED . "Something went wrong! Please use '/generatecode' command again.");
                        }
                    }
                } else {
                    $this->openGenerateForm($player, TF::RED . "First input should be a command and second one should be a number.");
                }
            } else {
                $this->openGenerateForm($player, TF::RED . "Inputs can not be empty!");
            }
        });
        $l = $label;
        if($l === ""){
            $l = "Put the command you want to execute when someone uses this redeem code whitout '/' in the first input.\nType -1 in the second input to make this code infinite  or type how many times you wanna use this code:\n";
        }
        $form->setTitle("Generate Redeem Code");
        $form->addLabel($l);
        $form->addInput("Command", "The command?");
        $form->addInput("Times", "How many times?");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openRedeemForm(Player $player, string $label = ""){
        $form = new CustomForm(function (Player $player, array $data = null){
            if($data === null) return true;
            if(!empty($data[1])){
                $this->redeem($player, $data[1]); 
            } else {
                $this->openRedeemForm($player, TF::RED . "Inputs can not be empty!");   
            }
        });
        $l = $label;
        if($l === ""){
            $l = "Inter a redeem code and get it's reward";
        }
        $form->setTitle("Use Redeem Code");
        $form->addLabel($l);
        $form->addInput("Redeem Code", "Type the code here");
        $form->sendToPlayer($player);
        return $form;
    }

}
