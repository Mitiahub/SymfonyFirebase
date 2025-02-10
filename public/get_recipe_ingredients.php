<?php
header('Content-Type: application/json');

$host = 'localhost'; 
$dbname = 'restau'; 
$username = 'root'; 
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur de connexion à la base de données."]);
    exit;
}

if (!isset($_GET['recette_id']) || !ctype_digit($_GET['recette_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "recette_id manquant ou invalide."]);
    exit;
}

$recette_id = (int) $_GET['recette_id'];

try {
    $stmt = $pdo->prepare("
        SELECT vcr.commande_id, ri.recette_id, i.nom, ri.quantite, i.quantite_stock 
        FROM recette_ingredient ri
        JOIN ingredient i ON ri.ingredient_id = i.id
        JOIN vue_commande_recette vcr ON ri.recette_id = vcr.recette_id
        WHERE ri.recette_id = :recette_id
    ");

    $stmt->bindParam(':recette_id', $recette_id, PDO::PARAM_INT);
    $stmt->execute();

    $ingredients = $stmt->fetchAll();

    if ($ingredients) {
        echo json_encode($ingredients);
    } else {
        echo json_encode(["message" => "Aucun ingrédient trouvé pour cette recette."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur lors de la récupération des ingrédients."]);
}
?>
