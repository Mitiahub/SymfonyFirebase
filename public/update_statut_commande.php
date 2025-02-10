<?php
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$password = "";
$dbname = "restau";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["commande_id"]) || !isset($data["recette_id"]) || !isset($data["quantite_reduite"])) {
    echo json_encode(["success" => false, "message" => "Données manquantes"]);
    exit();
}

$commande_id = intval($data["commande_id"]);
$recette_id = intval($data["recette_id"]);
$quantite_reduite = intval($data["quantite_reduite"]);

if ($quantite_reduite <= 0) {
    echo json_encode(["success" => false, "message" => "Quantité invalide"]);
    exit();
}

$sql_update = "UPDATE commande_recette 
               SET quantite = GREATEST(0, quantite - $quantite_reduite) 
               WHERE commande_id = $commande_id AND recette_id = $recette_id";
$conn->query($sql_update);

$sql_check = "SELECT SUM(quantite) AS total FROM commande_recette WHERE commande_id = $commande_id";
$result = $conn->query($sql_check);
$row = $result->fetch_assoc();

if ($row["total"] == 0) {

    $sql_status = "UPDATE commande SET status = 'terminé' WHERE id = $commande_id";
    $conn->query($sql_status);
}

echo json_encode(["success" => true, "message" => "Mise à jour réussie"]);

$conn->close();
?>
