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


CREATE TABLE commande (
    id SERIAL PRIMARY KEY,
    status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10, 2) DEFAULT 0,
    user_id INT REFERENCES utilisateur(id) ON DELETE CASCADE
);


CREATE TABLE recette (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    temps_cuisson INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255)
);


CREATE TABLE ingredient (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    quantite_stock INT NOT NULL DEFAULT 0,
    seuil_minimum INT DEFAULT 10
);


CREATE TABLE recette_ingredient (
    id SERIAL PRIMARY KEY,
    recette_id INT REFERENCES recette(id) ON DELETE CASCADE,
    ingredient_id INT REFERENCES ingredient(id) ON DELETE CASCADE,
    quantite INT NOT NULL
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
    commande_id INT REFERENCES commande(id) ON DELETE CASCADE,
    montant DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-----------------------------------
CREATE TABLE commande_recette (
    id SERIAL PRIMARY KEY,
    commande_id INT REFERENCES commande(id) ON DELETE CASCADE,
    recette_id INT REFERENCES recette(id) ON DELETE CASCADE,
    quantite INT NOT NULL CHECK (quantite > 0)
);

--------------------------------------
CREATE TABLE commande_ingredient (
    commande_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    PRIMARY KEY (commande_id, ingredient_id),
    CONSTRAINT fk_commande FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE,
    CONSTRAINT fk_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredient(id) ON DELETE CASCADE
);

-------------------------
ALTER TABLE utilisateur ADD COLUMN firebase_uid VARCHAR(255) UNIQUE;
--------------
ALTER TABLE recette ADD COLUMN prix DECIMAL(10,2) NOT NULL DEFAULT 0.00;
