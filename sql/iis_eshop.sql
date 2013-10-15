-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Úte 15. říj 2013, 10:29
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
-- Struktura tabulky `love_admin_content`
--

CREATE TABLE `love_admin_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  `plugin_instance_id` int(11) NOT NULL,
  `plugin_operation` int(11) DEFAULT NULL,
  `static_data` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `rank` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_admin_menu`
--

CREATE TABLE `love_admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf32 COLLATE=utf32_czech_ci AUTO_INCREMENT=7 ;

--
-- Vypisuji data pro tabulku `love_admin_menu`
--

INSERT INTO `love_admin_menu` (`id`, `name`, `title`, `parent_id`, `rank`) VALUES
(1, 'about', 'About', NULL, 0),
(2, 'settings', 'Settings', NULL, 5),
(3, 'plugins', 'Plugins', NULL, 0),
(4, 'staticContent', 'Static Content', 3, 0),
(5, 'themes', 'Themes', 2, 2),
(6, 'pages', 'Pages', 2, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `love_admin_module`
--

CREATE TABLE `love_admin_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `love_admin_module`
--

INSERT INTO `love_admin_module` (`id`, `name`) VALUES
(1, 'content');

-- --------------------------------------------------------

--
-- Struktura tabulky `love_admin_page`
--

CREATE TABLE `love_admin_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `theme` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `love_admin_page`
--

INSERT INTO `love_admin_page` (`id`, `name`, `title`, `theme`) VALUES
(1, 'about', 'About', ''),
(2, 'settings', 'Settings', '');

-- --------------------------------------------------------

--
-- Struktura tabulky `love_admin_settings`
--

CREATE TABLE `love_admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `keywords` text COLLATE utf8_czech_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `copyright` text COLLATE utf8_czech_ci NOT NULL,
  `theme` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_admin_user`
--

CREATE TABLE `love_admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `rank` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `love_admin_user`
--

INSERT INTO `love_admin_user` (`id`, `username`, `password`, `rank`, `last_login`) VALUES
(1, 'Kapa', 'kapalina', 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `love_content`
--

CREATE TABLE `love_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `plugin_id` int(11) DEFAULT NULL,
  `plugin_instance_id` int(11) NOT NULL,
  `plugin_operation` int(11) DEFAULT NULL,
  `static_data` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `love_content`
--

INSERT INTO `love_content` (`id`, `page_id`, `module_id`, `plugin_id`, `plugin_instance_id`, `plugin_operation`, `static_data`, `rank`) VALUES
(1, 1, 2, 2, 1, 1, NULL, 1),
(2, 2, 2, 2, 1, 2, NULL, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `love_module`
--

CREATE TABLE `love_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=6 ;

--
-- Vypisuji data pro tabulku `love_module`
--

INSERT INTO `love_module` (`id`, `name`) VALUES
(1, 'header'),
(2, 'content'),
(3, 'footer'),
(4, 'left-content'),
(5, 'right-content');

-- --------------------------------------------------------

--
-- Struktura tabulky `love_page`
--

CREATE TABLE `love_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `theme` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `love_page`
--

INSERT INTO `love_page` (`id`, `name`, `title`, `theme`) VALUES
(1, 'homepage', 'Domovská stránka', ''),
(2, 'detailproduktu', 'Detail Produktu', '');

-- --------------------------------------------------------

--
-- Struktura tabulky `love_plugin`
--

CREATE TABLE `love_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=7 ;

--
-- Vypisuji data pro tabulku `love_plugin`
--

INSERT INTO `love_plugin` (`id`, `name`) VALUES
(1, 'StaticContent'),
(2, 'Products'),
(3, 'Users'),
(4, 'ShoppingCart'),
(5, 'Orders'),
(6, 'Storage');

-- --------------------------------------------------------

--
-- Struktura tabulky `love_plugin_static`
--

CREATE TABLE `love_plugin_static` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `data` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabulky `love_settings`
--

CREATE TABLE `love_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `keywords` text COLLATE utf8_czech_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `copyright` text COLLATE utf8_czech_ci NOT NULL,
  `theme` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `love_settings`
--

INSERT INTO `love_settings` (`id`, `title`, `description`, `keywords`, `author`, `copyright`, `theme`) VALUES
(1, 'IIS Eshop', 'Skolni IIS projekt', 'iis, eshop, projekt, skola, vut fit', 'Karel Juřička, Jan Koriťák', '2013', 'eshop');

-- --------------------------------------------------------

--
-- Struktura tabulky `love_theme`
--

CREATE TABLE `love_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
