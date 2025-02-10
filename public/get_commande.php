<?php
require_once "dbconnect.php";

header('Content-Type: application/json; charset=utf-8');


try {
    $result = $conn->query("SELECT recette_id, nom,temps_cuisson,quantite, commande_id FROM vue_commande_recette");

    if (!$result) {
        throw new Exception("Erreur SQL : " . $conn->error);
    }

    $ingredients = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($ingredients, JSON_UNESCAPED_UNICODE);
    error_log(json_encode($ingredients, JSON_UNESCAPED_UNICODE));

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>
