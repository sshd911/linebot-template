<?php
$accessToken = 'ここにチャンネルアクセストークンを入れて下さい';

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);

//取得データ
$replyToken = $json_object->{"events"}[0]->{"replyToken"};             //返信用トークン
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};    //メッセージタイプ
$message_text = $json_object->{"events"}[0]->{"message"}->{"text"};    //メッセージ内容

//メッセージタイプが「text」以外、かつ数値以外の場合、かつ4文字の場合は何もせずに終了
if ($message_type != "text" && !is_numeric($message_text) && mb_strlen($message_text) == 4 ) exit;

// バスの時間を求める処理 
function getTime($message_text, $busTime, $callback)
{
    $i = 0;
    $time = '';

    // 入力された時間がバスの時刻表の時刻より過ぎた地点−1の地点（時刻）を出力。バスの時刻ー到着したい時間＝最適なバスの時間
    for ($i; $i <= count($busTime); $i++) {
        if ($message_text <= $busTime[$i]) { 
            $time = $busTime[$i - 1];
        } else {
            continue;
        }
        break;
    }
    return $callback($time);
};

// バスの時刻表 dbには接続していないため、ここに時刻表を入力して下さい
$busTime = [
    '0800',
    '0850',
    '0930',
    '1030',
    '1110',
    '1250',
    '1340',
    '1430',
    '1510',
    '1550',
    '1640',
    '1824',
    '1904',
];

// 時刻のフォーマット　ここでは入力された時間とバスの時刻の両方をフォーマットします
function formater($formated)
{
    if (mb_strlen($formated) == 4) {
        $formated = substr_replace($formated, "時", 2, 0);
        return $formated .= "分";
    } elseif (mb_strlen($formated) == 2) {
        return $formated = substr_replace($formated, "時", 2, 0);
    } else {
        exit;
    }
}

// バスの時刻の計算とフォーマット
$busTimeResult = getTime($message_text, $busTime, "formater");

// 夜遅いか確認。22時以降は一言付け加える。 php.iniの設定値により日本の時間より９時間遅れのため、22-9で13となっています
function check() {
    $now = date('H');
    if($now >= 13) {
    return "夜分遅くに";
    }
}
//返信メッセージ
$return_message_text = check()."失礼します。" . PHP_EOL . "明日の練習" . formater($message_text) . "開始。" . PHP_EOL . $busTimeResult . "バスでお願いします。";

//返信実行
sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
?>

<?php
//メッセージの送信
function sending_messages($accessToken, $replyToken, $message_type, $return_message_text)
{
    //レスポンスフォーマット
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];

    //ポストデータ
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];

    //curl実行
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}
?>
