<?php

namespace App\Http\Controllers\Brain;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheaterDetection extends Controller
{
        public function SendNotify($suspect, $author)
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

                    "url" => "https://cheat-status.su/".$this->GetSteamId64($author),

                    "timestamp" => $timestamp,

                    "color" => hexdec( "3366ff" ),

                    "footer" => [
                        "text" => "© 2023 (BigBrother) - All Rights Reserved.",
                        "icon_url" => "https://www.politicalite.com/wp-content/uploads/2022/08/52AE290D-FB00-4CD2-BA2F-C3A6CEAC74EB.jpeg"
                    ],

                    "author" => [
                        "name" => "Detected by $author",
                        "url" => "https://steamcommunity.com/profiles/".$this->GetSteamId64($author)
                    ],

                    "fields" => [
                        [
                            "name" => ":eye: Total statistics:",
                            "value" => ":man_detective: **Total cheater:** 228\n:chart_with_upwards_trend: **Total cheater per day:** 32\n:man_judge: **Last cheater:** [SteamProfile](https://steamcommunity.com/profiles/".$this->GetSteamId64($suspect).")",
                            "inline" => false
                        ],
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
        $url = file_get_contents("https://steamcommunity.com/id/".$id."/?xml=1");
        $new = simplexml_load_string($url);
        $buffer = json_encode($new);
        $buffer = json_decode($buffer, true);
        return ($buffer['steamID64']);
    }
}
