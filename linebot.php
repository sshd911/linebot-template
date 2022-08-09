<?php
//------------------------------------------------------------------------------------------------
$accessToken = '{your access token}'; // enter your access token
//------------------------------------------------------------------------------------------------
// get message
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
// get data
$replyToken = $json_object->{"events"}[0]->{"replyToken"}; // response token
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"}; // message type
$message_text = $json_object->{"events"}[0]->{"message"}->{"text"}; // message content
//------------------------------------------------------------------------------------------------
// write logic 



// response messasge
$return_message_text = '{response messasge}';
sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
//------------------------------------------------------------------------------------------------
// response
function sending_messages($accessToken, $replyToken, $message_type, $return_message_text)
{
    // response format
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];
    // post date 
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];
    // execute
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    curl_exec($ch);
    curl_close($ch);
}