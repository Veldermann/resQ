<?php

function curlfnc($todo, $data){
    $url = "https://warp-regulator-bd7q33crqa-lz.a.run.app/api/" . $todo;
    
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $headers = array(
       "accept: application/json",
       "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    if($data != ''){
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
    }
    $response = json_decode(curl_exec($curl));
    curl_close($curl);
    if($response->message == 'Engine started'){
        echo $response->message . '<br>';
        $authCode .= $response->authorizationCode;
        return $response;
    } else {
        return $response;
    }
}

function start(){
    $data = '{"name": "Kerth Veldermann", "email": "kerth@veldermann.ee"}';
    $response = curlfnc('start', $data);
    if($response->status == 'OK'){
        return $response->authorizationCode;
    } else {
        echo 'Something went wrong =(<br>';
        return;
    }
}

function status($authCode){
        $data = '';
        echo 'Checking engine fuel status . . .<br>';
        $response = curlfnc('status?authorizationCode=' . $authCode , $data);
        if($response->intermix == '0.5' and $response->flowRate == 'OPTIMAL'){
            echo 'Everything is working as intended . . . <br>';
        } else {
            echo 'Mix : ' . $response->intermix . ' - - - Flow : ' . $response->flowRate . '<br>';
            calcAdjust($response->intermix, $response->flowRate, $authCode);
        }
}

function calcAdjust($mix, $flow, $authCode){
    $matter = 0.5 - $mix;
    $antimatter = $mix - 0.5;
    if($flow == 'OPTIMAL'){
        if($matter < -0.2){
            $matter = 0.2;
            $antimatter = -0.2;
        }
        if($matter > 0.2){
            $matter = -0.2;
            $antimatter = 0.2;
        }
        echo "Optimal, just adjusting Mix . . .<br>";
        echo 'Matter : ' . $matter . ' - - - Antimatter : ' . $antimatter . '<br>';
        $data = '{"authorizationCode": "' . $authCode . '", "value": ' . $matter . '}';
        curlfnc('adjust/matter', $data);
        $data = '{"authorizationCode": "' . $authCode . '", "value": ' . $antimatter . '}';
        curlfnc('adjust/antimatter', $data);
    }
    elseif($flow == 'HIGH') {
        $matter = $matter - 0.1;
        $antimatter = $antimatter - 0.1;
        if($matter < -0.2){
            $matter = -0.2;
            $antimatter = -0.2;
        }
        echo 'High, adjusting if needed and removing fuel . . . <br>';
        echo 'Matter : ' . $matter . ' - - - Antimatter : ' . $antimatter . '<br>';
        $data = '{"authorizationCode": "' . $authCode . '", "value": ' . $matter . '}';
        curlfnc('adjust/matter', $data);
        $data = '{"authorizationCode": "' . $authCode . '", "value": ' . $antimatter . '}';
        curlfnc('adjust/antimatter', $data);
    }
    elseif($flow == 'LOW') {
        $matter = $matter + 0.1;
        $antimatter = $antimatter + 0.1;
        if($matter > 0.2){
            $matter = 0.2;
            $antimatter = 0.2;
        }
        echo "Low, adjusting if needed and adding fuel . . .<br>";
        echo 'Matter : ' . $matter . ' - - - Antimatter : ' . $antimatter . '<br>';
        $data = '{"authorizationCode": "' . $authCode . '", "value": ' . $matter . '}';
        curlfnc('adjust/matter', $data);
        $data = '{"authorizationCode": "' . $authCode . '", "value": ' . $antimatter . '}';
        curlfnc('adjust/antimatter', $data); 
    }
}

$startTime = time();
$authCode = start();

while(time() - $startTime < 80){
    status($authCode);
    sleep(1);
    echo ' - - - - - - - - - <br>';
}

echo 'We could do it forever, lets just stop here . . .';
?>


