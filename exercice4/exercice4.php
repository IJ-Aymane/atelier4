<?php
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'geo_data',
    'charset' => 'utf8'
];

function dbConnect($config) {
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    
    if ($conn->connect_error) {
        die("La connexion à la base de données a échoué: " . $conn->connect_error);
    }
    
    $conn->set_charset($config['charset']);
    
    return $conn;
}

function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getCountries($conn) {
    $sql = "SELECT id, nom FROM pays ORDER BY nom ASC";
    $result = $conn->query($sql);
    
    $countries = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $countries[] = $row;
        }
    }
    
    return $countries;
}

function getRegions($conn, $paysId) {
    $paysId = $conn->real_escape_string($paysId);
    
    $sql = "SELECT id, nom FROM regions WHERE pays_id = ? ORDER BY nom ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paysId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $regions = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $regions[] = $row;
        }
    }
    
    $stmt->close();
    
    return $regions;
}

function getCities($conn, $regionId) {
    $regionId = $conn->real_escape_string($regionId);
    
    $sql = "SELECT id, nom FROM villes WHERE region_id = ? ORDER BY nom ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $regionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cities = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cities[] = $row;
        }
    }
    
    $stmt->close();
    
    return $cities;
}

$conn = dbConnect($config);

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_countries':
        sendJsonResponse(getCountries($conn));
        break;
        
    case 'get_regions':
        if (isset($_GET['pays_id']) && !empty($_GET['pays_id'])) {
            sendJsonResponse(getRegions($conn, $_GET['pays_id']));
        } else {
            sendJsonResponse([]);
        }
        break;
        
    case 'get_cities':
        if (isset($_GET['region_id']) && !empty($_GET['region_id'])) {
            sendJsonResponse(getCities($conn, $_GET['region_id']));
        } else {
            sendJsonResponse([]);
        }
        break;
        
    default:
        if (isset($_GET['pays_id'])) {
            sendJsonResponse(getRegions($conn, $_GET['pays_id']));
        } elseif (isset($_GET['region_id'])) {
            sendJsonResponse(getCities($conn, $_GET['region_id']));
        } else {
            sendJsonResponse(getCountries($conn));
        }
        break;
}

$conn->close();