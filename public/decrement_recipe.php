<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Connexion à la base de données
$servername = "localhost"; // À modifier si nécessaire
$username = "root";        // Ton utilisateur MySQL
$password = "";            // Ton mot de passe MySQL
$dbname = "restau"; // Nom de ta base de données

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "❌ Connexion à la base de données échouée"]));
}

// Lire les données envoyées par Godot
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id_commande"])) {
    echo json_encode(["success" => false, "message" => "❌ ID de commande manquant"]);
    exit();
}

$id_commande = $data["id_commande"];

// Vérifier si la commande existe et récupérer la quantité actuelle
$sql = "SELECT quantity FROM commandes WHERE id_commande = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "❌ Commande introuvable"]);
    exit();
}

$row = $result->fetch_assoc();
$current_quantity = (int)$row["quantity"];

if ($current_quantity > 1) {
    // Décrémenter la quantité
    $new_quantity = $current_quantity - 1;
    $update_sql = "UPDATE commandes SET quantity = ? WHERE id_commande = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_quantity, $id_commande);
    $update_stmt->execute();

    echo json_encode(["success" => true, "message" => "✅ Quantité mise à jour", "new_quantity" => $new_quantity]);
} else {
    // Supprimer la commande si la quantité est à 0
    $delete_sql = "DELETE FROM commandes WHERE id_commande = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id_commande);
    $delete_stmt->execute();

    echo json_encode(["success" => true, "message" => "🚨 Commande supprimée car quantité épuisée"]);
}

$conn->close();
