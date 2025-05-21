-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 07 mai 2025 à 17:07
-- Version du serveur : 5.7.17
-- Version de PHP : 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Base de données : `nnn_sae`
CREATE DATABASE IF NOT EXISTS `nnn_sae`;
USE `nnn_sae`;

-- -------------------------
-- Table : criteres
-- -------------------------
CREATE TABLE criteres (
  critere_id INT AUTO_INCREMENT PRIMARY KEY,
  nom_critere VARCHAR(100) NOT NULL,
  note_max INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- -------------------------
-- Table : criteres_evaluation
-- ------------------------

CREATE TABLE criteres_evaluation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_email VARCHAR(255) NOT NULL,
  critere_id INT NOT NULL,
  note_obtenue INT,
  commentaire TEXT,
  FOREIGN KEY (utilisateur_email) REFERENCES users(utilisateur_email) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (critere_id) REFERENCES criteres(critere_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- -------------------------
-- Table : professionnels
-- -------------------------
CREATE TABLE professionnels (
  pro_email VARCHAR(255) PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  metier VARCHAR(100),
  entreprise VARCHAR(100),
  linkedin LONGBLOB
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- -------------------------
-- Table : users
-- -------------------------
CREATE TABLE users (
  utilisateur_email VARCHAR(255) PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  role ENUM('etudiant', 'enseignant') NOT NULL,
  motdepasse VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- -------------------------
-- Table : interviews
-- -------------------------
CREATE TABLE interviews (
  interview_id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_email VARCHAR(255) NOT NULL,
  pro_email VARCHAR(255) NOT NULL,
  fichier_interview LONGBLOB,
  fichier_attestation LONGBLOB,
  FOREIGN KEY (utilisateur_email) REFERENCES users(utilisateur_email) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (pro_email) REFERENCES professionnels(pro_email) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- -------------------------
-- Insertion : utilisateurs
-- -------------------------
INSERT INTO users (utilisateur_email, nom, prenom, role, motdepasse) VALUES
('nolanfontaine@example.com', 'Fontaine', 'Nolan', 'etudiant', '1234'),
('noadouit@example.com', 'Douit', 'Noa', 'etudiant', '1234'),
('nathaelbenoit@example.com', 'Benoit', 'Nathael', 'enseignant', '1234');

COMMIT;
