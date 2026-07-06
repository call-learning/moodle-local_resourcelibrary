# Changelog — Pédagothèque Numérique (local_resourcelibrary)

## Version 3.0.0 — 14 décembre 2025

### Nouvelles fonctionnalités
- **Pages du catalogue** : il est désormais possible de créer plusieurs pages de catalogue distinctes. Chaque page peut afficher uniquement certains domaines (catégories de cours) et certains champs personnalisés définis par l'administration.
- **Pagination améliorée** : le parcours des ressources est maintenant paginé avec une navigation par numéros de page, un affichage du nombre total de résultats et un choix du nombre de résultats par page (12, 24 ou 48). Cette préférence est sauvegardée pour chaque utilisateur.
- **Filtres connectés aux pages** : les filtres proposés sont maintenant adaptatifs. Selon la page du catalogue consultée, seuls les filtres pertinents (champs personnalisés) affichés sont affichés.
- **Gestion du catalogue depuis l'administration** : les administrateurs peuvent gérer les pages du catalogue depuis un menu dédié, avec des options pour voir, modifier ou supprimer chaque page.

### Améliorations
- **Interface utilisateur** : amélioration du bouton « Gestion du catalogue » visible par les administrateurs.
- **Accessibilité** : ajout d'attributs `alt` sur les images et amélioration des labels pour les lecteurs d'écran.
- **Préférences utilisateur** : la préférence de pagination (nombre de cours par page) est maintenant sauvegardée automatiquement pour chaque utilisateur.
- **Affichage des cours** : suppression du lien « Retour au cours » dans les vignettes des cours, l'accès se fait directement depuis la vignette elle-même.

### Corrections
- Correction d'un conflit possible entre les domaines (catégories) de cours et les filtres
- Amélioration du support PHP 8.3
- Correction de styles CSS pour un affichage cohérent notamment sur les pages non-thémées IMT
- Nettoyage du code et migration vers des API Moodle modernes

---

## Notes

- Le plugin nécessite Moodle 4.5+
- La version actuelle est en stage RC (Release Candidate).