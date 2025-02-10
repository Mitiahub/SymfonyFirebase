create DATABASE restau
-- Connexion à la base
\c restau;

-- Création des tables
CREATE TABLE utilisateur (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'client',
    actif BOOLEAN DEFAULT TRUE
);
INSERT INTO utilisateur (nom, email, mot_de_passe, role, actif) 
VALUES ('jean', 'jean@email.com', '123', 'client', TRUE);


CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10, 2) DEFAULT 0,
    user_id INT REFERENCES utilisateur(id) ON DELETE CASCADE
);


CREATE TABLE recette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    temps_cuisson INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255)
);


CREATE TABLE ingredient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    quantite_stock INT NOT NULL DEFAULT 0,
    seuil_minimum INT DEFAULT 10
);


CREATE TABLE recette_ingredient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recette_id INT ,
    ingredient_id INT ,
    quantite INT NOT NULL,
    FOREIGN KEY (recette_id) REFERENCES recette(id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredient(id)
);

CREATE TABLE notification (
    id SERIAL PRIMARY KEY,
    message TEXT NOT NULL,
    user_id INT REFERENCES utilisateur(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status BOOLEAN DEFAULT FALSE
);

---------------------------
CREATE TABLE paiement (
    id SERIAL PRIMARY KEY,
    commande_id INT ,
    montant DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commande(id) 
);

-----------------------------------
CREATE TABLE commande_recette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT,
    recette_id INT,
    quantite INT NOT NULL CHECK (quantite > 0),
    FOREIGN KEY (commande_id) REFERENCES commande(id) ,
    FOREIGN KEY (recette_id) REFERENCES recette(id)
);


--------------------------------------
CREATE TABLE commande_ingredient (
    commande_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    PRIMARY KEY (commande_id, ingredient_id),
    FOREIGN KEY (commande_id) REFERENCES commande(id) ,
    FOREIGN KEY (ingredient_id) REFERENCES ingredient(id) 
);

-------------------------
ALTER TABLE utilisateur ADD COLUMN firebase_uid VARCHAR(255) UNIQUE;
--------------
ALTER TABLE recette ADD COLUMN prix DECIMAL(10,2) NOT NULL DEFAULT 0.00;



CREATE VIEW vue_commande_recette AS
SELECT 
    r.id as recette_id,
    cr.commande_id ,
    r.nom,
    r.temps_cuisson,
    cr.quantite
FROM commande_recette cr
JOIN recette r ON cr.recette_id = r.id;


