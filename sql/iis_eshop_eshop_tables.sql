-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Čtv 17. říj 2013, 17:57
-- Verze MySQL: 5.5.29
-- Verze PHP: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `iis_eshop`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_historie_cen`
--

CREATE TABLE `love_eshop_historie_cen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produkt` int(11) NOT NULL,
  `od_data` int(11) NOT NULL,
  `cena` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_hlidaci_pes`
--

CREATE TABLE `love_eshop_hlidaci_pes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produkt` int(11) NOT NULL,
  `uzivatel` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_hodnoceni`
--

CREATE TABLE `love_eshop_hodnoceni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produkt` int(11) NOT NULL,
  `uzivatel` int(11) NOT NULL,
  `hodnoceni` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_komentar`
--

CREATE TABLE `love_eshop_komentar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produkt` int(11) NOT NULL,
  `uzivatel` int(11) NOT NULL,
  `datum` int(11) NOT NULL,
  `reakce` int(11) NOT NULL,
  `zprava` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_nakupni_kosik`
--

CREATE TABLE `love_eshop_nakupni_kosik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produkt` int(11) NOT NULL,
  `mnozstvi` int(11) NOT NULL,
  `uzivatel` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_objednavka`
--

CREATE TABLE `love_eshop_objednavka` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uzivatel` int(11) NOT NULL,
  `datum_vytvoreni` int(11) NOT NULL,
  `datum_odeslani` int(11) NOT NULL,
  `datum_zaplaceni` int(11) NOT NULL,
  `dodaci_jmeno` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `dodaci_prijmeni` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `dodaci_ulice` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `dodaci_cislo_popisne` int(11) DEFAULT NULL,
  `dodaci_mesto` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `dodaci_PSC` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_objednavka_produkt`
--

CREATE TABLE `love_eshop_objednavka_produkt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objednavka` int(11) NOT NULL,
  `produkt` int(11) NOT NULL,
  `cena` float NOT NULL,
  `mnozstvi` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_produkt`
--

CREATE TABLE `love_eshop_produkt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jmeno_produktu` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `kategorie` int(11) DEFAULT NULL,
  `popis_produktu` text COLLATE utf8_czech_ci NOT NULL,
  `vyrobce` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `akce` tinyint(1) NOT NULL DEFAULT '0',
  `novinka` tinyint(1) NOT NULL DEFAULT '1',
  `mnoztvi_na_sklade` int(11) NOT NULL,
  `datum_pridani` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_produkt_kategorie`
--

CREATE TABLE `love_eshop_produkt_kategorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jmeno_kategorie` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `nadkategorie` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_eshop_uzivatel`
--

CREATE TABLE `love_eshop_uzivatel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `heslo` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `jmeno` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `prijmeni` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `mobil` int(11) NOT NULL,
  `ulice` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `cislo_popisne` int(11) NOT NULL,
  `mesto` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `psc` int(11) NOT NULL,
  `aktivni` tinyint(1) NOT NULL,
  `novinky` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
