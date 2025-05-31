CREATE DATABASE fil_rouge_rattrapage;

DROP TABLE IF EXISTS tickets CASCADE;
DROP TABLE IF EXISTS seances CASCADE;
DROP TABLE IF EXISTS clients CASCADE;

CREATE TABLE clients (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20)
);

CREATE TABLE seances (
    id SERIAL PRIMARY KEY,
    film VARCHAR(255) NOT NULL,
    horaire TIMESTAMP NOT NULL, 
    prix DECIMAL(10, 2) NOT NULL,
    salle VARCHAR(50) NOT NULL,
    places_disponibles INT NOT NULL CHECK (places_disponibles >= 0),
    type_seance VARCHAR(10) NOT NULL CHECK (type_seance IN ('2D', '3D')), 
    qualite_image VARCHAR(50), 
    technologie_3d VARCHAR(50),
    lunettes_incluses BOOLEAN 
);

CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    client_id INT NOT NULL,
    seance_id INT NOT NULL,
    nombre_places INT NOT NULL CHECK (nombre_places > 0 AND nombre_places <= 5), 
    montant_total DECIMAL(10, 2) NOT NULL CHECK (montant_total >= 0),
    statut VARCHAR(20) NOT NULL DEFAULT 'RÉSERVÉ' CHECK (statut IN ('VENDU', 'RÉSERVÉ', 'ANNULÉ')),
    
    CONSTRAINT fk_client
        FOREIGN KEY (client_id)
        REFERENCES clients(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_seance
        FOREIGN KEY (seance_id)
        REFERENCES seances(id)
        ON DELETE CASCADE
);


INSERT INTO clients (name, email, phone) VALUES
('Alice Dupont', 'alice.dupont@example.com', '0612345678'),
('Bob Martin', 'bob.martin@example.com', '0789012345'),
('Charlie Brown', 'charlie.brown@example.com', '0555112233');

INSERT INTO seances (film, horaire, prix, salle, places_disponibles, type_seance, qualite_image, technologie_3d, lunettes_incluses) VALUES
('Dune: Part Two', '2024-03-01 18:00:00', 70.00, 'Salle 1', 100, '2D', 'Full HD', NULL, NULL);

INSERT INTO seances (film, horaire, prix, salle, places_disponibles, type_seance, qualite_image, technologie_3d, lunettes_incluses) VALUES
('Avatar: The Way of Water', '2024-03-01 20:30:00', 90.00, 'Salle IMAX', 50, '3D', NULL, 'RealD 3D', TRUE);

INSERT INTO seances (film, horaire, prix, salle, places_disponibles, type_seance, qualite_image, technologie_3d, lunettes_incluses) VALUES
('The Marvels', '2024-03-02 14:00:00', 65.00, 'Salle 2', 80, '2D', 'Standard HD', NULL, NULL);

INSERT INTO seances (film, horaire, prix, salle, places_disponibles, type_seance, qualite_image, technologie_3d, lunettes_incluses) VALUES
('Spider-Man: Across the Spider-Verse', '2024-03-02 16:30:00', 85.00, 'Salle 3', 70, '3D', NULL, 'Dolby 3D', FALSE);