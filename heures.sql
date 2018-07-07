-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 28 Février 2013 à 09:28
-- Version du serveur: 5.1.66
-- Version de PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `heures`
--

-- --------------------------------------------------------

--
-- Structure de la table `heures`
--

CREATE TABLE IF NOT EXISTS `heures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `matin_dbt` varchar(255) NOT NULL,
  `matin_fin` varchar(255) NOT NULL,
  `ap_dbt` varchar(255) NOT NULL,
  `ap_fin` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `heures`
--

INSERT INTO `heures` (`id`, `matin_dbt`, `matin_fin`, `ap_dbt`, `ap_fin`, `date`) VALUES
(3, '17:06', '17:14', '17:15', '17:15', '2013/02/23;Samedi 23 Février 2013');
