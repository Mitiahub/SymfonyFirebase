INSERT INTO ingredient (nom, quantite_stock, seuil_minimum) VALUES
('Tomate', "100", "10"),
('Pâte', 50, 5),
('Oignon', 75, 15),
('Bouillon de soupe', 40, 5),
('Fromage', 60, 10),
('Pain', 80, 20),
('Charcuterie', 45, 5),
('Riz', 30, 10),
('Porc', 25, 5),
('Oeuf', 50, 10);

INSERT INTO recette (nom, temps_cuisson, description, image_url) VALUES
('Pizza', 900, 'Une pizza classique avec sauce tomate et fromage.', 'pizza.jpg'),
('Soupe', 600, 'Une soupe chaude et nourrissante.', 'soupe.jpg'),
('Sandwich', 300, 'Un sandwich rapide et délicieux.', 'sandwich.jpg');

INSERT INTO recette_ingredient (recette_id, ingredient_id, quantite) VALUES
(1, 1, 2), -- 2 Tomates pour Pizza
(1, 2, 1), -- 1 Pâte pour Pizza
(2, 3, 1), -- 1 Oignon pour Soupe
(2, 4, 1), -- 1 Bouillon pour Soupe
(3, 6, 1), -- 1 Pain pour Sandwich
(3, 7, 1); -- 1 Charcuterie pour Sandwich

INSERT INTO utilisateur (nom, email, mot_de_passe, role, actif) VALUES
('Admin', 'admin@example.com', 'admin', 'admin', TRUE),
('mitia', 'mitia@example.com', 'mitia', 'client', TRUE),
('john', 'john@example.com', 'john', 'client', TRUE);


INSERT INTO commande_recette (commande_id, recette_id, quantite) VALUES
(1, 1, 2),  -- Commande 2 contient 2x Pizza
(1, 2, 1),  -- Commande 2 contient 1x Soupe
(2, 3, 3);  -- Commande 3 contient 3x Sandwich

INSERT INTO commande_ingredient (commande_id, ingredient_id) VALUES
-- Commande 2 (contient Pizza et Soupe)
(1, 1),  -- Tomate (pour Pizza)
(1, 2),  -- Pâte (pour Pizza)
(1, 3),  -- Oignon (pour Soupe)
(1, 4),  -- Bouillon de soupe (pour Soupe)

-- Commande 3 (contient Sandwich)
(2, 5),  -- Fromage (pour Sandwich)
(2, 6),  -- Pain (pour Sandwich)
(2, 7);  -- Charcuterie (pour Sandwich)

UPDATE utilisateur
SET firebase_uid = 'SGKT5Q7jsag7EWNfhZ8XGBqGbI42'
WHERE email = 'john@example.com';

UPDATE utilisateur
SET firebase_uid = 'lz2yhFnYrmVoUXnDL8HUJIfkePq1',
    mot_de_passe = CONCAT(mot_de_passe, '123')
WHERE email = 'mitia@example.com';

UPDATE utilisateur
SET firebase_uid = '4enFhEh931VK2poM7LftGriQDJO2',
    mot_de_passe = CONCAT(mot_de_passe, '123')
WHERE email = 'admin@example.com';


UPDATE recette SET prix = 10.50 WHERE id = 1; -- Pizza
UPDATE recette SET prix = 7.00 WHERE id = 2; -- Soupe
UPDATE recette SET prix = 5.50 WHERE id = 3; -- Sandwich
UPDATE recette SET prix = 9.00 WHERE id = 4; -- Gratin
UPDATE recette SET prix = 6.50 WHERE id = 6; -- Pain perdu
UPDATE recette SET prix = 8.75 WHERE id = 5; -- Riz sauté au porc
