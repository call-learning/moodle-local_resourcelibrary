<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin to manage Resource Library
 *
 * @link https://www.imt.fr/formation/academie-transformations-educatives/ressources-pedagogiques/pedagotheque-numerique/
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning 2020 - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Pédagothèque numérique';

$string['activity_metadata'] = 'Metatdata pour la Pédagothèque numérique';
$string['aria:card'] = 'Vignettes';
$string['aria:controls'] = 'Paramétrages';
$string['aria:displaydropdown'] = 'Paramétrages de l\'affichage';
$string['aria:sortingdropdown'] = 'Paramétrages du tri';
$string['aria:list'] = 'Liste';
$string['aria:lastmodification'] = 'Trier par dernière modification';
$string['aria:lastmodification:desc'] = 'Trier par dernière modification (Descendant)';
$string['aria:title'] = 'Titre';
$string['aria:title:desc'] = 'Titre (Descendant)';
$string['card'] = 'Vignette';
$string['course_metadata'] = 'Metatdata pour la Pédagothèque numérique';
$string['hiddenfromstudents'] = 'Cours fermé';
$string['mainresourcelibrary'] = 'Liste de cours';
$string['manage:hiddenfilter'] = 'Caché pour Filtres?';

$string['resourcelibrary'] = 'Pédagothèque numérique';
$string['resourcelibrary:menutextoverride'] = 'Texte pour le menu/lien';
$string['resourcelibrary:menutextoverride:desc'] = 'Texte pour le menu/lien, si vide, ce sera la valeur de "resourcelibrary".
 On définit une chaine par langue par exemple {$a}';
$string['resourcelibrary:replacecourseindex'] = 'Remplacer la page de liste de cours par la page de pédagothèque numérique.';
$string['resourcelibrary:replacecourseindex:desc'] = 'Remplacer la page de liste de cours par la page de pédagothèque numérique.';

$string['resourcelibraryfieldsettings'] = 'Paramètres des champs de la Pédagothèque numérique';
$string['resourcelibrary_course_customfield'] = 'Gestion des champs personnalisés de cours pour la Pédagothèque numérique';
$string['resourcelibrary_coursemodule_customfield'] = 'Gestion des champs personnalisés d\'activité pour la Pédagothèque numérique';
$string['resourcelibraryfield_islocked'] = 'Le champ est vérouillé';
$string['resourcelibraryfield_islocked_help'] = 'Le champ est vérouillé et ne peut être changé';
$string['resourcelibraryfield_visibletoall'] = 'Le champ est visible pour tous';
$string['resourcelibraryfield_islocked_help'] = 'Le champ est visible pour tous';
$string['resourcelibraryfield_visibletoteachers'] = 'Le champ est visible pour les enseignants';
$string['resourcelibraryfield_visibletoteachers_help'] = 'Le champ est visible pour les enseignants';
$string['resourcelibraryfield_visibility'] = 'Visibilité du champ';
$string['resourcelibraryfield_visibility_help'] = 'Visibilité du champ';
$string['resourcelibraryfield_notvisible'] = 'Invisible';
$string['resourcelibraryfield_notvisible'] = 'Le champ n\'est pas visible';
$string['resourcelibrary:coursviewbaseurl'] = 'URL de base pour voir le cours.';
$string['resourcelibrary:coursviewbaseurl:desc'] = 'URL de base pour voir le cours. La plupart du temps: ce sera /course/view.php.'
    .' On rajoute l\'identifiant id qui sera celui du cours.';
$string['resourcelibrary:hiddencoursesid'] = 'Identifiant des cours invisible sur le catalogue';
$string['resourcelibrary:hiddencoursesid:desc'] = 'Liste des identifiants de cours séparés par des virgules qui n\'apparaîtront
 pas sur la pédagothèque numérique';

$string['resourcelibrarymainsettings'] = 'Pégadothèque numérique: paramètres globaux';
$string['category:general']  = 'Pégadothèque numérique: Champs génériques';

$string['enableresourcelibrary'] = 'Activer la Pédagothèque numérique';
$string['filters'] = 'Filtres';
$string['filter:anyvalue'] = 'Tous';
$string['filter:submit'] = 'Filtrer';
$string['resourcelibrary:activateactivitylibrary'] = 'Activer la bibliothèque d\'activités';
$string['resourcelibrary:activateactivitylibrary:desc'] =
    'La bibliothèque d\'activités est similaire à la pédagothèque numérique mais pour les activités de cours';
$string['resourcelibrary:manage'] = 'Peut gérer la pédagothèque numérique';
$string['resourcelibrary:managefields'] = 'Peut gérer les champs de la  Pédagothèque numérique';
$string['resourcelibrary:editvalue'] = 'Peut éditer les valeurs des champs de la  Pédagothèque numérique';
$string['resourcelibrary:configurecustomfields'] = 'Peut configurer les champs de la  Pédagothèque numérique';
$string['resourcelibrary:changelockedcustomfields'] = 'Peut éditer les valeurs des champs vérouillés de la  Pédagothèque numérique';
$string['resourcelibrary:view'] = 'Peut configurer les champs de la  Pédagothèque numérique';
$string['list'] = 'Liste';
$string['lastmodification'] = 'Dernière modification';
$string['lastmodification:desc'] = 'Dernière modification (Descendant)';
$string['noentities'] = 'Aucun résultats ! Veuillez sélectionner d\'autres valeurs pour les filtres.';
$string['operator:instructions:greaterthan'] = 'Plus grand que';
$string['privacy:metadata:resourcelibrarypagingpreference'] = 'Préférences sur la pagination';
$string['privacy:metadata:resourcelibraryviewpreference'] = 'Préférences de vue (Liste/Carte)';
$string['privacy:metadata:resourcelibrarysortpreference'] = 'Préférences d\'ordre d\'affichage';
$string['permalink:copy'] = 'Copier';
$string['title'] = 'Titre';
$string['title:desc'] = 'Titre (Descendant)';
$string['viewitem'] = 'Voir';
$string['wronghandlerforfilter'] = 'Mauvais handler {$a->handlername} pour le filtre {$a->filtername}';
