-- Insertion des ingrédients
INSERT INTO ingredient (nom, quantite_stock, seuil_minimum) VALUES
('Tomate', 100, 10),
('Pâte', 50, 5),
('Oignon', 75, 15),
('Bouillon de soupe', 40, 5),
('Fromage', 60, 10),
('Pain', 80, 20),
('Charcuterie', 45, 5),
('Riz', 30, 10),
('Porc', 25, 5),
('Oeuf', 50, 10);

-- Insertion des recettes
INSERT INTO recette (nom, temps_cuisson, description, image_url, prix) VALUES
('Pizza', 900, 'Une pizza classique avec sauce tomate et fromage.', 'pizza.jpg', 10.50),
('Soupe', 600, 'Une soupe chaude et nourrissante.', 'soupe.jpg', 7.00),
('Sandwich', 300, 'Un sandwich rapide et délicieux.', 'sandwich.jpg', 5.50),
('Gratin', 1200, 'Un gratin de pommes de terre délicieux.', 'gratin.jpg', 9.00),
('Riz sauté au porc', 900, 'Un riz sauté aux saveurs exotiques.', 'riz_saute.jpg', 8.75),
('Pain perdu', 600, 'Un dessert sucré classique.', 'pain_perdu.jpg', 6.50);

-- Insertion des ingrédients des recettes
INSERT INTO recette_ingredient (recette_id, ingredient_id, quantite) VALUES
(1, 1, 2), -- 2 Tomates pour Pizza
(1, 2, 1), -- 1 Pâte pour Pizza
(2, 3, 1), -- 1 Oignon pour Soupe
(2, 4, 1), -- 1 Bouillon pour Soupe
(3, 6, 1), -- 1 Pain pour Sandwich
(3, 7, 1); -- 1 Charcuterie pour Sandwich

-- Insertion des utilisateurs
INSERT INTO utilisateur (nom, email, mot_de_passe, role, actif, firebase_uid) VALUES
('Admin', 'admin@example.com', CONCAT('admin', '123'), 'admin', TRUE, '4enFhEh931VK2poM7LftGriQDJO2'),
('mitia', 'mitia@example.com', CONCAT('mitia', '123'), 'client', TRUE, 'lz2yhFnYrmVoUXnDL8HUJIfkePq1'),
('john', 'john@example.com', 'john', 'client', TRUE, 'SGKT5Q7jsag7EWNfhZ8XGBqGbI42');

-- Insertion des commandes (pour éviter erreur FOREIGN KEY)
INSERT INTO commande (status, created_at, updated_at, montant_total, user_id) VALUES
('en attente', NOW(), NOW(), 21.00, 1),
('en attente', NOW(), NOW(), 12.00, 2);

-- Insertion des commandes de recettes
INSERT INTO commande_recette (commande_id, recette_id, quantite) VALUES
(1, 1, 2),  -- Commande 1 contient 2x Pizza
(1, 2, 1),  -- Commande 1 contient 1x Soupe
(2, 3, 3);  -- Commande 2 contient 3x Sandwich

-- Insertion des commandes d'ingrédients
INSERT INTO commande_ingredient (commande_id, ingredient_id) VALUES
(1, 1),  -- Tomate (pour Pizza)
(1, 2),  -- Pâte (pour Pizza)
(1, 3),  -- Oignon (pour Soupe)
(1, 4),  -- Bouillon de soupe (pour Soupe)
(2, 5),  -- Fromage (pour Sandwich)
(2, 6),  -- Pain (pour Sandwich)
(2, 7);  -- Charcuterie (pour Sandwich)

-- Mise à jour des utilisateurs avec firebase_uid sécurisé
UPDATE utilisateur SET firebase_uid = 'SGKT5Q7jsag7EWNfhZ8XGBqGbI42' WHERE email = 'john@example.com';
UPDATE utilisateur SET firebase_uid = 'lz2yhFnYrmVoUXnDL8HUJIfkePq1' WHERE email = 'mitia@example.com';
UPDATE utilisateur SET firebase_uid = '4enFhEh931VK2poM7LftGriQDJO2' WHERE email = 'admin@example.com';

-- Mise à jour des prix des recettes
UPDATE recette SET prix = 10.50 WHERE id = 1; -- Pizza
UPDATE recette SET prix = 7.00 WHERE id = 2; -- Soupe
UPDATE recette SET prix = 5.50 WHERE id = 3; -- Sandwich
UPDATE recette SET prix = 9.00 WHERE id = 4; -- Gratin
UPDATE recette SET prix = 8.75 WHERE id = 5; -- Riz sauté au porc
UPDATE recette SET prix = 6.50 WHERE id = 6; -- Pain perdu
