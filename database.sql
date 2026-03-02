-- =============================================
--  EduGuide SN — Base de données
-- =============================================

CREATE DATABASE IF NOT EXISTS eduguide_sn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eduguide_sn;

-- ─────────────────────────────────────────────
--  TABLE : ecoles
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ecoles (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(255)  NOT NULL,
    slug          VARCHAR(100)  NOT NULL UNIQUE,
    type          ENUM('public','prive') NOT NULL DEFAULT 'public',
    ville         VARCHAR(100)  NOT NULL,
    icone         VARCHAR(80)   DEFAULT 'fas fa-university',
    presentation  TEXT,
    filieres      TEXT COMMENT 'JSON array',
    domaines      TEXT COMMENT 'JSON array',
    conditions    TEXT COMMENT 'JSON array',
    pieces        TEXT COMMENT 'JSON array',
    type_admission VARCHAR(100) DEFAULT 'Par concours',
    email         VARCHAR(150),
    telephone     VARCHAR(50),
    site_web      VARCHAR(200),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  TABLE : concours
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS concours (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ecole_id      INT NOT NULL,
    nom           VARCHAR(255) NOT NULL,
    annee         YEAR         NOT NULL,
    date_limite   DATE,
    niveau_requis VARCHAR(200),
    matieres      TEXT COMMENT 'JSON array',
    description   TEXT,
    frais         DECIMAL(10,2) DEFAULT 0,
    statut        ENUM('ouvert','ferme','a_venir') DEFAULT 'a_venir',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ecole_id) REFERENCES ecoles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  TABLE : epreuves
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS epreuves (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    concours_id   INT NOT NULL,
    matiere       VARCHAR(150) NOT NULL,
    annee         YEAR        NOT NULL,
    fichier       VARCHAR(255) COMMENT 'Fichier PDF local (uploads/epreuves/)',
    lien_externe  VARCHAR(500) COMMENT 'URL externe : Google Drive, Dropbox, OneDrive, etc.',
    tag_classe    VARCHAR(50)  DEFAULT 'tag-math'  COMMENT 'Classe CSS pour le badge couleur',
    nb_telechargements INT DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (concours_id) REFERENCES concours(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ⚠️  Si la table existe déjà, lancez cette commande pour ajouter la colonne :
-- ALTER TABLE epreuves ADD COLUMN lien_externe VARCHAR(500) COMMENT 'URL externe' AFTER fichier;

-- ─────────────────────────────────────────────
--  DONNÉES : Écoles
-- ─────────────────────────────────────────────
INSERT INTO ecoles (nom, slug, type, ville, icone, presentation, filieres, domaines, conditions, pieces, type_admission, email, telephone) VALUES

('École Supérieure Polytechnique (ESP)', 'esp', 'public', 'Dakar', 'fas fa-microchip',
 'L\'ESP est l\'une des grandes écoles d\'ingénieurs du Sénégal, formant des ingénieurs dans divers domaines technologiques et scientifiques. Elle accueille chaque année les meilleurs bacheliers scientifiques du pays.',
 '["Génie Informatique","Génie Civil","Génie Électrique","Génie Mécanique","Télécommunications"]',
 '[{"label":"Ingénierie","classe":"dt-blue"},{"label":"Informatique","classe":"dt-purple"},{"label":"Télécommunications","classe":"dt-green"}]',
 '["Baccalauréat scientifique (S1, S2, S3)","Âge limite : 23 ans","Réussir le concours d\'entrée"]',
 '["Copie légalisée du bac","Certificat de nationalité","Extrait de naissance","4 photos d\'identité","Certificat médical"]',
 'Par concours', 'contact@esp.sn', '+221 33 XXX XX XX'),

('Faculté des Sciences et Techniques (FST-UCAD)', 'fst', 'public', 'Dakar', 'fas fa-flask',
 'La FST offre des formations en sciences fondamentales et appliquées au sein de l\'Université Cheikh Anta Diop de Dakar. Elle forme des scientifiques et ingénieurs de haut niveau.',
 '["Mathématiques","Physique","Chimie","Informatique","Sciences de la Vie"]',
 '[{"label":"Sciences","classe":"dt-blue"},{"label":"Mathématiques","classe":"dt-purple"},{"label":"Physique","classe":"dt-orange"}]',
 '["Baccalauréat scientifique (S1, S2, S3)","Dossier de candidature","Entretien de sélection"]',
 '["Copie légalisée du bac","Certificat de nationalité","Extrait de naissance","4 photos d\'identité","Certificat médical"]',
 'Sur dossier', 'contact@fst.ucad.sn', '+221 33 XXX XX XX'),

('Institut Supérieur de Management (ISM)', 'ism', 'prive', 'Dakar', 'fas fa-chart-line',
 'L\'ISM est une école de commerce privée de référence au Sénégal, formant les futurs leaders du monde des affaires avec des programmes accrédités internationalement.',
 '["Management","Commerce International","Finance","Marketing","Ressources Humaines"]',
 '[{"label":"Management","classe":"dt-blue"},{"label":"Commerce","classe":"dt-green"},{"label":"Finance","classe":"dt-orange"}]',
 '["Baccalauréat toutes séries","Dossier de candidature","Test d\'admission et entretien"]',
 '["Copie légalisée du bac","Certificat de nationalité","Lettre de motivation","CV","4 photos d\'identité"]',
 'Sur dossier + Entretien', 'admissions@ism.sn', '+221 33 XXX XX XX'),

('École Nationale Supérieure d\'Agriculture (ENSA)', 'ensa', 'public', 'Thiès', 'fas fa-seedling',
 'L\'ENSA forme des ingénieurs agronomes pour le développement agricole et rural du Sénégal. Implantée à Thiès, elle contribue à la modernisation du secteur agricole national.',
 '["Agronomie","Agroalimentaire","Génie Rural","Protection des Végétaux","Économie Rurale"]',
 '[{"label":"Agriculture","classe":"dt-green"},{"label":"Agroalimentaire","classe":"dt-orange"},{"label":"Environnement","classe":"dt-blue"}]',
 '["Baccalauréat scientifique (S1, S2)","Âge limite : 25 ans","Réussir le concours d\'entrée"]',
 '["Copie légalisée du bac","Certificat de nationalité","Extrait de naissance","4 photos d\'identité","Certificat médical"]',
 'Par concours', 'contact@ensa.sn', '+221 33 XXX XX XX'),

('École Supérieure Multinationale des Télécommunications (ESMT)', 'esmt', 'public', 'Dakar', 'fas fa-wifi',
 'L\'ESMT est une école d\'excellence créée par 17 pays africains, formant des ingénieurs en télécommunications et technologies de l\'information pour toute l\'Afrique de l\'Ouest.',
 '["Télécommunications","Réseaux & Systèmes","Cybersécurité","Intelligence Artificielle","Systèmes Embarqués"]',
 '[{"label":"Télécommunications","classe":"dt-purple"},{"label":"Informatique","classe":"dt-blue"},{"label":"Réseaux","classe":"dt-green"}]',
 '["Baccalauréat scientifique (S1, S2, S3)","Âge limite : 23 ans","Réussir le concours d\'entrée"]',
 '["Copie légalisée du bac","Certificat de nationalité","Extrait de naissance","4 photos d\'identité","Certificat médical"]',
 'Par concours', 'contact@esmt.sn', '+221 33 XXX XX XX'),

('Faculté de Médecine, Pharmacie et Odontologie (FMPO-UCAD)', 'fmpo', 'public', 'Dakar', 'fas fa-stethoscope',
 'La FMPO forme les médecins, pharmaciens et chirurgiens-dentistes du Sénégal au sein de l\'Université Cheikh Anta Diop. C\'est la principale faculté de santé du pays.',
 '["Médecine Générale","Pharmacie","Chirurgie Dentaire","Spécialités Médicales"]',
 '[{"label":"Santé","classe":"dt-green"},{"label":"Médecine","classe":"dt-blue"},{"label":"Pharmacie","classe":"dt-orange"}]',
 '["Baccalauréat scientifique (S1, S2)","Âge limite : 20 ans","Réussir le concours d\'entrée"]',
 '["Copie légalisée du bac","Certificat de nationalité","Extrait de naissance","4 photos d\'identité","Certificat médical","Certificat de visite médicale"]',
 'Par concours', 'contact@fmpo.ucad.sn', '+221 33 XXX XX XX');

-- ─────────────────────────────────────────────
--  DONNÉES : Concours
-- ─────────────────────────────────────────────
INSERT INTO concours (ecole_id, nom, annee, date_limite, niveau_requis, matieres, statut) VALUES
(1, 'Concours d\'entrée ESP 2024',  2024, '2024-06-15', 'Baccalauréat S1, S2, S3', '["Mathématiques","Physique-Chimie","Français","Anglais"]', 'ouvert'),
(4, 'Concours ENSA 2024',           2024, '2024-05-30', 'Baccalauréat S1, S2',     '["Mathématiques","Physique-Chimie","Biologie","Français"]', 'ouvert'),
(5, 'Concours ESMT 2024',           2024, '2024-07-20', 'Baccalauréat S1, S2, S3', '["Mathématiques","Physique","Anglais","Culture Générale"]', 'ouvert'),
(6, 'Concours Médecine FMPO 2024',  2024, '2024-06-10', 'Baccalauréat S1, S2',     '["Biologie","Chimie","Physique","Français"]', 'ouvert'),
(1, 'Concours d\'entrée ESP 2023',  2023, '2023-06-15', 'Baccalauréat S1, S2, S3', '["Mathématiques","Physique-Chimie","Français","Anglais"]', 'ferme'),
(4, 'Concours ENSA 2023',           2023, '2023-05-30', 'Baccalauréat S1, S2',     '["Mathématiques","Physique-Chimie","Biologie","Français"]', 'ferme');

-- ─────────────────────────────────────────────
--  DONNÉES : Épreuves
-- ─────────────────────────────────────────────
INSERT INTO epreuves (concours_id, matiere, annee, tag_classe) VALUES
(1, 'Mathématiques',  2024, 'tag-math'),
(1, 'Physique-Chimie',2024, 'tag-phys'),
(5, 'Mathématiques',  2023, 'tag-math'),
(5, 'Physique-Chimie',2023, 'tag-phys'),
(2, 'Biologie',       2024, 'tag-bio'),
(2, 'Mathématiques',  2024, 'tag-math'),
(3, 'Mathématiques',  2024, 'tag-math'),
(3, 'Anglais',        2024, 'tag-ang'),
(4, 'Biologie',       2024, 'tag-bio'),
(4, 'Chimie',         2024, 'tag-chim'),
(5, 'Mathématiques',  2022, 'tag-math'),
(5, 'Physique-Chimie',2022, 'tag-phys');
