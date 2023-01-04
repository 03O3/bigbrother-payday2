<?php

namespace App\Http\Controllers\Brain;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheaterDetection extends Controller
{

    public function App($suspect, $reason){
        if($this->checkSuspect($suspect, $reason)){
            $this->checkSuspectReason($suspect, $reason);
            return;
        }
        $this->sendNotify($suspect, $reason);
        $this->addSuspect($suspect, $reason);
    }

    private function checkSuspect($suspect, $reason){
        $results = app('db')->select("SELECT * FROM `cheaters` WHERE `steamid64` = '$suspect'");
        if(!empty($results)) return true;
    }
    public function addSuspect($suspect, $reason){
        $profile = "https://steamcommunity.com/profiles/".$this->GetSteamId64($suspect);
        app('db')->insert("INSERT INTO `cheaters` (`profile`, `steamid64`, `reason`) VALUES ('$profile', '$suspect', '$reason')");
    }
    public function checkSuspectReason($suspect, $reason){
        $results = app('db')->select("SELECT * FROM `cheaters` WHERE `steamid64` = '$suspect'");
        if(!in_array($reason, explode(",", $results[0]->reason))){
            $buffer = array();
            $items = [ 'reason' => $results[0]->reason.','.$reason];
            $buffer[] = $items['reason'];
            app('db')->update("UPDATE `cheaters` SET `reason` = '".$buffer['0']."' WHERE `steamid64` = '$suspect'");
        }
    }
        public function sendNotify($suspect, $reason)
    {
        $webhook = "https://discord.com/api/webhooks/1059535015001727136/IuDGqDLbIc_MDNmgfYQblmwqWUZaYA4LG2eoUNIIywJhY5xfQw06Zs8FF4dZXxEnl155";
        $timestamp = date("c", strtotime("now"));

        $json_data = json_encode([
            "username" => "BigBrother",

            "tts" => false,

            "embeds" => [
                [

                    "title" => "New cheater detected!",

                    "type" => "rich",

                    "description" => "There's one less cheater in payday today!",

                    "timestamp" => $timestamp,

                    "color" => hexdec( "3366ff" ),

                    "footer" => [
                        "text" => "© 2023 (BigBrother) - All Rights Reserved.",
                        "icon_url" => "https://static.wikia.nocookie.net/watchdogscombined/images/8/8b/DedSec_App.png/revision/latest?cb=20161119164213"
                    ],

                    "fields" => [
                        [
                            "name" => ":office_worker: Cheater info:",
                            "value" => "<:steamicon:1059590990727499856> **Steam Profile:** [Steam Profile (".$this->GetSteamId64($suspect).")](https://steamcommunity.com/profiles/".$this->GetSteamId64($suspect).")\n<:payday2icon:1059596942688145519> **PD2 Profile:** [Payday2 Profile](https://fbi.paydaythegame.com/suspect/".$this->GetSteamId64($suspect).")\n<:vacban:1059618355142729960> **VAC-BANNED:** ".$this->GetVacStatus($suspect)."\n<:question:1059847946960642098> **Detect CODE:** $reason",
                            "inline" => false
                        ],
                        [
                            "name" => ":eye: Total statistics:",
                            "value" => ":man_detective: **Total cheater:** ".$this->GetRowCheater()."\n:chart_with_upwards_trend: **Total cheater per day:** ".$this->GetCheaterPerDay()."\n:man_judge: **Last cheater:** [Steam Profile (".$this->GetSteamId64($this->GetLastCheater($suspect)).")](https://steamcommunity.com/profiles/".$this->GetLastCheater($suspect).")",
                            "inline" => false
                        ]
                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $this->SendWebhook($json_data, $webhook);

    }

    public function SendWebhook($message, $url){
        if(!empty($url))
        {
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $message);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt( $ch, CURLOPT_HEADER, 0);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec( $ch );
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close( $ch );
            if($response_code != 200) {
                return response('', 500);
            }
            return response('', 200);
        }
    }

    public function GetCheaterPerDay(){
        $today = Carbon::today();
        $results = app('db')->select("SELECT * FROM `cheaters` WHERE `date` BETWEEN '$today' AND '".$today->endOfDay()."'");
        return count($results);
    }

    public function GetRowCheater(){
        $results = app('db')->select("SELECT * FROM `cheaters`");
        if(count($results) == 0) return '1';
        return count($results);
    }


    public function GetLastCheater($id){
        $results = app('db')->select("SELECT * FROM `cheaters` ORDER BY id DESC LIMIT 1");
        if(empty($results)) return $id;
        return $results[0]->profile;
    }
    public function GetSteamId64($id){
        if (filter_var($id, FILTER_VALIDATE_URL)){
            $buffer = json_encode(simplexml_load_string(file_get_contents($id."?xml=1")));
            $buffer = json_decode($buffer, true);
            return $buffer['steamID64'];
        }
        if (str_contains($id, '765')) return $id;
        $buffer = json_encode(simplexml_load_string(file_get_contents("https://steamcommunity.com/id/".$id."/?xml=1")));
        $buffer = json_decode($buffer, true);
        return $buffer['steamID64'];
    }

    public function GetVacStatus($id){
        $buffer = json_encode(simplexml_load_string(file_get_contents("https://steamcommunity.com/profiles/".$id."/?xml=1")));
        $buffer = json_decode($buffer, true);
        if($buffer['vacBanned']) return ":white_check_mark:";
        return ":x:";
    }
}
