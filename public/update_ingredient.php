<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restau";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$data = json_decode(trim(file_get_contents("php://input")), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Données JSON invalides ou vides."]);
    exit;
}

if (isset($data['nom']) && isset($data['quantity_used'])) {
    $nom = trim($data['nom']); 
    $quantity_used = intval($data['quantity_used']); 

    $check_query = "SELECT quantite_stock FROM ingredient WHERE nom = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $nom);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Ingrédient introuvable."]);
        exit;
    }

    $row = $result->fetch_assoc();
    $quantite_actuelle = intval($row['quantite_stock']);

    if ($quantite_actuelle < $quantity_used) {
        echo json_encode(["status" => "error", "message" => "Stock insuffisant."]);
        exit;
    }

    $query = "UPDATE ingredient SET quantite_stock = GREATEST(0, quantite_stock - ?) WHERE nom = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $quantity_used, $nom);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Quantité mise à jour avec succès."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Aucun changement apporté."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Données manquantes ou invalides."]);
}

$conn->close();
?>
