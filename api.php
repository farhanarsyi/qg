<?php
if(isset($_POST['action'])){
    $action = $_POST['action'];
    
    switch($action){
        case "fetchProjects":
            $year = $_POST['year'];
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll";
            $postData = http_build_query([
                "year" => $year
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchGates":
            $id_project = $_POST['id_project'];
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/gates/fetchAll";
            $postData = http_build_query([
                "id_project" => $id_project
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchMeasurements":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab  = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/measurements/fetchAll";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate"    => $id_gate,
                "prov"       => $prov,
                "kab"        => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchPreventivesByKab":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab  = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Preventives/fetchByKab";
            $postData = http_build_query([
                "data[year]" => $year,
                "data[id_project]" => $id_project,
                "data[id_gate]" => $id_gate,
                "data[id_measurement]" => $id_measurement,
                "data[prov]" => $prov,
                "data[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchPreventivesByMeasurement":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab  = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Preventives/fetchByMeasurement";
            $postData = http_build_query([
                "year" => $year,
                "param[year]" => $year,
                "param[id_project]" => $id_project,
                "param[id_gate]" => $id_gate,
                "param[id_measurement]" => $id_measurement,
                "param[prov]" => $prov,
                "param[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchAssessments":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Measurements/fetchAllAssessments";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate" => $id_gate,
                "prov" => $prov,
                "kab" => $kab
            ]);
            
            echo callApi($url, $postData);
            break;
        case "fetchNeedCorrectives":
            $year = isset($_POST['year']) ? $_POST['year'] : "2025";
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/assessments/fetchNeedCorrectives";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate" => $id_gate,
                "prov" => $prov,
                "kab" => $kab,
                "year" => $year
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchProjectSpesific":
            $year = $_POST['year'];
            $project_id = $_POST['project_id'];
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchSpesific";
            $postData = http_build_query([
                "year" => $year,
                "project_id" => $project_id
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchCoverages":
            $id_project = $_POST['id_project'];
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/coverages/fetchAll";
            $postData = http_build_query([
                "id_project" => $id_project
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchCorrectivesByKab":
            $year = $_POST['data']['year'];
            $id_project = $_POST['data']['id_project'];
            $id_gate = $_POST['data']['id_gate'];
            $id_measurement = $_POST['data']['id_measurement'];
            $prov = isset($_POST['data']['prov']) ? $_POST['data']['prov'] : "00";
            $kab = isset($_POST['data']['kab']) ? $_POST['data']['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Correctives/fetchByKab";
            $postData = http_build_query([
                "data[year]" => $year,
                "data[id_project]" => $id_project,
                "data[id_gate]" => $id_gate,
                "data[id_measurement]" => $id_measurement,
                "data[prov]" => $prov,
                "data[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchCorrectivesByMeasurement":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Correctives/fetchByMeasurement";
            $postData = http_build_query([
                "year" => $year,
                "param[year]" => $year,
                "param[id_project]" => $id_project,
                "param[id_gate]" => $id_gate,
                "param[id_measurement]" => $id_measurement,
                "param[prov]" => $prov,
                "param[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchAllActions":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/measurements/fetchAllActions";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate" => $id_gate,
                "prov" => $prov,
                "kab" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        default:
            echo json_encode(["status" => false, "message" => "Invalid action"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "No action specified"]);
}

function callApi($url, $postData){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With: XMLHttpRequest',
        'Cookie: ci_session=5503368b0cb86766c2be9ed5d93c038762698865'
    ]);
    $response = curl_exec($ch);
    if(curl_errno($ch)){
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(["status" => false, "message" => $error_msg]);
    }
    curl_close($ch);
    return $response;
}
?>
