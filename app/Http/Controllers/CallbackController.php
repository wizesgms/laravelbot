<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CallbackController extends Controller
{
    public function webhook($token)
    {
        $update = json_decode(file_get_contents('php://input'), true);

        if ($update["message"]) {
            $chatID = $update["message"]["chat"]["id"];
            $userID = $update["message"]["from"]["id"];
            $msg = $update["message"]["text"];
            $username = $update["message"]["chat"]["username"];
            $name = $update["message"]["chat"]["first_name"];
        } else if ($update["callback_query"]["data"]) {
            $chatID = $update["callback_query"]["message"]["chat"]["id"];
            $userID = $update["callback_query"]["from"]["id"];
            $msgid = $update["callback_query"]["message"]["message_id"];
        } else if (isset($update["inline_query"])) {
            $msg = $update["inline_query"]["query"];
            $userID = $update["inline_query"]["from"]["id"];
            $username = $update["inline_query"]["from"]["username"];
            $name = $update["inline_query"]["from"]["first_name"];
        }

        switch ($msg) {
            case '/start':
                return $this->langmenu($chatID, $token);
                break;
            default:
                $this->setPhoto($chatID, $token);
                break;
        }
    }


    function langmenu($chatID, $token)
    {
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🇲🇾 Bahasa Melayu', 'callback_data' => 'info']],
                [['text' => '🇬🇧 English', 'callback_data' => 'info']],
                [['text' => '🇨🇳 China', 'callback_data' => 'info']]
            ]
        ];

        $caption = "Sila pilih bahasa utama anda di bawah
Please select your preferred language below
请在以下选项中选择您的首选语言";
        $data = [
            'chat_id' => $chatID,
            'text' => $caption,
            'parse_mode' => 'html',
            'reply_markup' => json_encode($keyboard)
        ];
        return Http::post('https://api.telegram.org/' . $token . '/sendMessage?' . http_build_query($data));
    }

    function setPhoto($chatID, $token)
    {
        $keyboard = [
            'inline_keyboard' => [
                [['text' => 'ℹ️ Info', 'callback_data' => 'info']],
                [['text' => 'ℹ️ Info', 'callback_data' => 'info']]
            ]
        ];

        $caption = "Selamat Datang ke <b>WIN96</b>!
Kompani Eksklusif Tanpa Ejen!
Sila tekan pilihan di bawah :";
        $data = [
            'chat_id' => $chatID,
            'photo' => 'https://cdn.cpgaming.tech/ImageFile/wUmDXNMpj0j5CepEilzVk0Co3TuOxKUtlpxnqM69.webp',
            'caption' => $caption,
            'parse_mode' => 'html',
            'reply_markup' => json_encode($keyboard)
        ];
        return Http::post('https://api.telegram.org/' . $token . '/sendPhoto?' . http_build_query($data));
    }

    function menu($text)
    {
        global $lang;
        global $chatID;
        $menu[] = array($lang['addmemo']);
        $menu[] = array($lang['savedmemo']);
        $menu[] = array($lang['info']);
        $menu[] = array($lang['feedback']);
        $menu[] = array($lang['settings'], $lang['github']);
        $this->sm($chatID, $text, $menu, 'HTML', false, false, true, $text);
    }

    function setmenu($text)
    {
        global $lang;
        global $chatID;
        $menu[] = array($lang['inlinemode']);
        $menu[] = array($lang['justwritemode']);
        $menu[] = array($lang['deleteallnote']);
        $menu[] = array($lang['settimezone']);
        $menu[] = array($lang['cancel']);
        $this->sm($chatID, $text, $menu, 'HTML', false, false, true, $text);
    }

    function inlinemodeset($invertmemodata)
    {
        global $lang;
        global $chatID;
        if ($invertmemodata == 1) {
            $invertmemodatatxt = $lang['enabled'];
        } else {
            $invertmemodatatxt = $lang['disabled'];
        }
        $menu[] = array(array(
            "text" => $lang['invertmemodata'] . $invertmemodatatxt,
            "callback_data" => "toggle-0-invertmemodata"
        ));
        $this->sm($chatID, $lang['settingstextinline'], $menu, 'HTML', false, false, false, true, $invertmemodata);
    }

    function toendate($date)
    {
        $date = str_ireplace("oggi", "today", $date);
        $date = str_ireplace("ieri", "yesterday", $date);
        $date = str_ireplace("domani", "tomorrow", $date);
        $date = str_ireplace("alle", "", $date);
        $date = str_ireplace("at", "", $date);
        return $date;
    }


    function acq($id, $text, $alert = false)
    {
        global $api;
        global $chatID;
        $args = array(
            'callback_query_id' => $id,
            'text' => $text,
            'show_alert' => $alert
        );
        $rr = Http::post('https://api.telegram.org/' . $api . '/answerCallbackQuery', $args);
        return $rr;
    }

    //Send Message
    function sm($api, $chatID, $text, $rmf = false, $pm = false, $dis = false, $replyto = false, $preview = false, $inline = false)
    {
        if ($inline) {
            $rm = array('inline_keyboard' => $rmf);
        } else {
            $rm = array(
                'keyboard' => $rmf,
                'resize_keyboard' => true
            );
        }

        $rm = json_encode($rm);

        $args = array(
            'chat_id' => $chatID,
            'text' => $text,
            'disable_notification' => $dis
        );
        if ($replyto) $args['reply_to_message_id'] = $update["message"]["message_id"];
        if ($rmf) $args['reply_markup'] = $rm;
        if ($preview) $args['disable_web_page_preview'] = $preview;
        if ($pm) $args['parse_mode'] = $pm;
        if ($text) {
            $rr = Http::post('https://api.telegram.org/' . $api . '/sendmessage', $args);
            $ar = json_decode($rr, true);
        }
        return $ar;
    }

    //Send Voice
    function sv($chatID, $file_id, $caption, $rmf = false, $pm = false, $dis = false, $replyto = false, $inline = false)
    {
        global $api;
        global $update;

        if ($inline) {
            $rm = array('inline_keyboard' => $rmf);
        } else {
            $rm = array(
                'keyboard' => $rmf,
                'resize_keyboard' => true
            );
        }

        $rm = json_encode($rm);

        $args = array(
            'chat_id' => $chatID,
            'voice' => $file_id,
            'disable_notification' => $dis
        );
        if ($replyto) $args['reply_to_message_id'] = $update["message"]["message_id"];
        if ($caption) $args['caption'] = $caption;
        if ($rmf) $args['reply_markup'] = $rm;
        if ($pm) $args['parse_mode'] = $pm;
        if ($file_id) {
            $rr = Http::post('https://api.telegram.org/' . $api . '/sendVoice', $args);
        }
        return $rr;
    }

    //Edit Message
    function em($chatID, $messageID, $text, $rmf = false, $inline = false, $pm = false)
    {
        global $api;

        if ($inline) {
            $rm = array('inline_keyboard' => $rmf);
        } else {
            $rm = array(
                'keyboard' => $rmf,
                'resize_keyboard' => true
            );
        }

        $rm = json_encode($rm);

        $args = array(
            'chat_id' => $chatID,
            'message_id' => $messageID,
            'text' => $text
        );
        if ($rmf) $args['reply_markup'] = $rm;
        if ($pm) $args['parse_mode'] = $pm;
        if ($text) {
            $rr = Http::post('https://api.telegram.org/' . $api . '/editMessageText', $args);
        }
        return $rr;
    }

    //Edit Message keyboard
    function emk($chatID, $messageID, $rmf, $inline_msgid = false)
    {
        global $api;

        $rm = array('inline_keyboard' => $rmf);
        $rm = json_encode($rm);

        $args["reply_markup"] = $rm;
        if ($inline_msgid) {
            $args["inline_message_id"] = $inline_msgid;
        } else {
            $args["chat_id"] = $chatID;
            $args["message_id"] = $messageID;
        }

        Http::post('https://api.telegram.org/' . $api . '/editMessageReplyMarkup', $args);
    }

    //Edit Message caption
    function emc($chatID, $messageID, $caption, $rmf, $inline_msgid = false)
    {
        global $api;

        $rm = array('inline_keyboard' => $rmf);
        $rm = json_encode($rm);

        $args["reply_markup"] = $rm;
        if ($inline_msgid) {
            $args["inline_message_id"] = $inline_msgid;
        } else {
            $args["chat_id"] = $chatID;
            $args["message_id"] = $messageID;
        }
        $args["caption"] = $caption;

        Http::post('https://api.telegram.org/' . $api . '/editMessageCaption', $args);
    }

    //Delete Message
    function dm($chatID, $msgID)
    {
        global $api;

        $args = array(
            "chat_id" => $chatID,
            "message_id" => $msgID
        );

        Http::post('https://api.telegram.org/' . $api . '/deleteMessage', $args);
    }
}
