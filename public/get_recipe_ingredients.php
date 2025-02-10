<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost'; 
$dbname = 'restau'; 
$username = 'root'; 
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur de connexion à la base de données."]);
    exit;
}

// Récupérer `recette_id` depuis GET ou POST
$data = json_decode(file_get_contents("php://input"), true);
$recette_id = $_GET['recette_id'] ?? $data['recette_id'] ?? null;

if (!isset($recette_id) || !ctype_digit($recette_id)) {
    http_response_code(400);
    echo json_encode(["error" => "recette_id manquant ou invalide."]);
    exit;
}

$recette_id = (int) $recette_id;

try {
    // TESTER SANS LA VUE POUR VOIR SI ÇA MARCHE
    $stmt = $pdo->prepare("
        SELECT ri.recette_id, i.nom, ri.quantite, i.quantite_stock 
        FROM recette_ingredient ri
        JOIN ingredient i ON ri.ingredient_id = i.id
        WHERE ri.recette_id = :recette_id
    ");

    $stmt->bindParam(':recette_id', $recette_id, PDO::PARAM_INT);
    $stmt->execute();

    $ingredients = $stmt->fetchAll();

    if ($ingredients) {
        echo json_encode(["success" => true, "ingredients" => $ingredients]);
    } else {
        echo json_encode(["success" => false, "message" => "Aucun ingrédient trouvé pour cette recette."]);
    }
} catch (PDOException $e) {
    error_log("Erreur SQL: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des ingrédients."]);
}
?>
