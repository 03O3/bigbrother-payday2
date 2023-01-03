<?php

namespace App\Http\Controllers\Brain;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheaterDetection extends Controller
{
        public function SendNotify($suspect, $reason)
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

                    "author" => [
                        "name" => "Profile on Website",
                        "url" => "https://example.com"
                    ],

                    "fields" => [
                        [
                            "name" => ":office_worker: Cheater info:",
                            "value" => "<:steamicon:1059590990727499856> **Steam Profile:** [Steam Profile (".$this->GetSteamId64($suspect).")](https://steamcommunity.com/profiles/".$this->GetSteamId64($suspect).")\n<:payday2icon:1059596942688145519> **PD2 Profile:** [Payday2 Profile](https://fbi.paydaythegame.com/suspect/".$this->GetSteamId64($suspect).")\n<:vacban:1059618355142729960> **VAC-BANNED:** ".$this->GetVacStatus($suspect)."\n<:question:1059847946960642098> **Detect CODE:** $reason",
                            "inline" => false
                        ],
                        [
                            "name" => ":eye: Total statistics:",
                            "value" => ":man_detective: **Total cheater:** 228\n:chart_with_upwards_trend: **Total cheater per day:** 32\n:man_judge: **Last cheater:** From Database :))",
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
    public function GetSteamId64($id){
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
