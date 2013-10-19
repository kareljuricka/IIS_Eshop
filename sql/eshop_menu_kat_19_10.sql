INSERT INTO `love_content` (`id`, `page_id`, `module_id`, `plugin_id`, `plugin_instance_id`, `plugin_operation`, `static_data`, `rank`) VALUES
(7, 0, 1, 3, 1, 3, NULL, 1),
(2, 2, 2, 2, 1, 2, NULL, 1),
(6, 5, 2, 2, 1, 1, NULL, 1),
(4, 3, 2, 3, 1, 1, NULL, 1),
(9, 6, 2, 3, 1, 4, NULL, 1),
(8, 4, 2, 3, 1, 2, NULL, 1),
(10, 8, 2, 3, 1, 5, NULL, 1),
(11, 0, 5, 4, 1, 1, NULL, 1),
(12, 9, 2, 4, 1, 2, NULL, 1),
(13, 0, 4, 7, 1, 1, NULL, 1);

CREATE TABLE `love_eshop_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=6 ;

--
-- Vypisuji data pro tabulku `love_eshop_menu`
--

INSERT INTO `love_eshop_menu` (`id`, `name`, `title`, `parent_id`, `rank`) VALUES
(1, 'homepage', 'Domů', NULL, 1),
(2, 'kategorie', 'Kategorie', NULL, 3),
(3, 'kontakt', 'Kontakt', NULL, 5),
(4, 'obchodni-podminky', 'Obchodní podmínky', NULL, 4),
(5, 'novinky', 'Novinky', NULL, 2);

INSERT INTO `love_eshop_produkt` (`id`, `jmeno_produktu`, `kategorie`, `popis_produktu`, `vyrobce`, `akce`, `novinka`, `mnoztvi_na_sklade`, `datum_pridani`, `cena`) VALUES
(1, 'TITLEIST driver 910D2 10.5° Stiff', 2, 'Golfový driver Titleist 910D2 je modelem z roku 2011. Driver má standardní objem hlavy 460ccm', 'Titleist', 0, 1, 30, '0000-00-00 00:00:00', 1),
(2, 'Nicklaus Dual Point 460cc driver - shaft Fujikura', 2, 'Lehčí a pružnější materiál zvyšuje rychlost hlavy při odpalu a přidává k jeho délce až 13 metrù. Konstrukce hlavy driveru je navržena pro využití nejvýše povolené M.O.I., díky novému rozložení těžištì o váze 10 gramù v hlavi hole, které je ukotveno níže a hlouběji', 'Nicklaus', 0, 1, 11, '0000-00-00 00:00:00', 1),
(3, 'Jack Nicklaus EZ-UP driver Square', 3, 'Největší možné C.O.R.( je to membránová úderová líc hlavy hole, která se po úderu v máčkne zpět do hlavy, aby vzápětí vystřelila před svou normální polohu)', 'Nicklaus', 0, 1, 213, '0000-00-00 00:00:00', 1),
(4, 'Confidence Golf HQ7 Square', 3, 'Dřeva Confidence HQ7 jsou pravděpodobně jedeny z nejsnáze použitelných holí.', 'Confidence Golf', 0, 1, 23, '0000-00-00 00:00:00', 1),
(5, 'Test hůl', 2, 'Hehe', 'Mehe', 0, 1, 12, '0000-00-00 00:00:00', 1);


INSERT INTO `love_eshop_produkt_kategorie` (`id`, `jmeno_kategorie`, `nadkategorie`) VALUES
(1, 'Golfové hole', NULL),
(2, 'Drivery', 1),
(3, 'Dřeva', 1),
(4, 'Golfové rukavice', NULL),
(5, 'Golfové oblečení', NULL),
(6, 'Golfové kalhoty', 5);

INSERT INTO `love_page` (`id`, `name`, `title`, `theme`) VALUES
(1, 'homepage', 'Domovská stránka', ''),
(2, 'produkt', 'Detail Produktu', ''),
(3, 'registrace', 'Registrace', ''),
(4, 'prihlaseni', 'Přihlášení', ''),
(5, 'kategorie', 'Kategorie', ''),
(6, 'upravit-osobni-udaje', 'Upravit osobní údaje', ''),
(7, 'uzivatel', 'Uživatel', ''),
(8, 'zmena-hesla', 'Změna hesla', ''),
(9, 'kosik', 'Košik', '');

INSERT INTO `love_plugin` (`id`, `name`) VALUES
(1, 'StaticContent'),
(2, 'Products'),
(3, 'Users'),
(4, 'ShoppingCart'),
(5, 'Orders'),
(6, 'Storage'),
(7, 'EshopMenu');