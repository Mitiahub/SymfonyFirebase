 <?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost"; 
$username = "root";       
$password = "";    
$dbname = "restau";        

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "❌ Connexion à la base de données échouée"]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id_commande"])) {
    echo json_encode(["success" => false, "message" => "❌ ID de commande manquant"]);
    exit();
}

$commande_id = $data["id_commande"];

$sql = "SELECT cr.quantite 
        FROM commande c
        JOIN commande_recette cr ON c.id = cr.commande_id
        WHERE c.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $commande_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "❌ Commande introuvable"]);
    exit();
}

$row = $result->fetch_assoc();
$current_quantity = (int)$row["quantite"]; 

if ($current_quantity > 1) {
    // Décrémenter la quantité
    $new_quantity = $current_quantity - 1;
    $update_sql = "UPDATE commande_recette SET quantite = ? WHERE commande_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_quantity, $commande_id);
    $update_stmt->execute();

    echo json_encode(["success" => true, "message" => "✅ Quantité mise à jour", "new_quantity" => $new_quantity]);
} else {
    // Supprimer la commande si la quantité est à 0
    $delete_sql = "DELETE FROM commande_recette WHERE commande_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $commande_id);
    $delete_stmt->execute();

    echo json_encode(["success" => true, "message" => "🚨 Commande supprimée car quantité épuisée"]);
}

$conn->close();
?>