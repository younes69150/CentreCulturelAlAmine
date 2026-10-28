<?php
declare(strict_types=1);

// [méthode, chemin (regex), fonction]
return [
    ['GET',  '/', 'home'],
    ['GET',  '/connexion', 'login_form'],
    ['POST', '/connexion', 'login_submit'],
    ['GET',  '/inscription', 'register_form'],
    ['POST', '/inscription', 'register_submit'],
    ['POST', '/deconnexion', 'logout'],
    ['GET',  '/mot-de-passe-oublie', 'forgot_form'],
    ['POST', '/mot-de-passe-oublie', 'forgot_submit'],
    ['GET',  '/mot-de-passe/nouveau', 'reset_form'],
    ['POST', '/mot-de-passe/nouveau', 'reset_submit'],
    ['GET',  '/mentions-legales', 'legal_mentions'],
    ['GET',  '/confidentialite', 'legal_privacy'],
    ['GET',  '/cgu', 'legal_cgu'],
    ['GET',  '/robots.txt', 'robots_txt'],
    ['GET',  '/sitemap.xml', 'sitemap_xml'],
    ['GET',  '/installation', 'install_form'],
    ['POST', '/installation', 'install_submit'],

    ['GET',  '/actualites', 'news_index'],
    ['GET',  '/actualites/(\d+)', 'news_show'],
    ['GET',  '/documents', 'documents_index'],
    ['GET',  '/direct', 'lives_index'],
    ['GET',  '/documents/(\d+)/telecharger', 'documents_download'],
    ['GET',  '/mon-compte', 'account_show'],
    ['POST', '/mon-compte/mot-de-passe', 'account_password'],

    ['GET',  '/admin', 'admin_dashboard'],
    ['GET',  '/admin/statistiques', 'admin_stats'],

    ['GET',  '/admin/direct', 'admin_lives_index'],
    ['GET',  '/admin/direct/nouveau', 'admin_lives_create'],
    ['POST', '/admin/direct/nouveau', 'admin_lives_store'],
    ['GET',  '/admin/direct/(\d+)', 'admin_lives_edit'],
    ['POST', '/admin/direct/(\d+)', 'admin_lives_update'],
    ['POST', '/admin/direct/(\d+)/terminer', 'admin_lives_end'],
    ['POST', '/admin/direct/(\d+)/supprimer', 'admin_lives_delete'],

    ['GET',  '/admin/actualites', 'admin_news_index'],
    ['GET',  '/admin/actualites/nouvelle', 'admin_news_create'],
    ['POST', '/admin/actualites/nouvelle', 'admin_news_store'],
    ['GET',  '/admin/actualites/(\d+)', 'admin_news_edit'],
    ['POST', '/admin/actualites/(\d+)', 'admin_news_update'],
    ['POST', '/admin/actualites/(\d+)/supprimer', 'admin_news_delete'],

    ['GET',  '/admin/documents', 'admin_documents_index'],
    ['POST', '/admin/documents', 'admin_documents_store'],
    ['POST', '/admin/documents/(\d+)/supprimer', 'admin_documents_delete'],

    ['GET',  '/admin/messages', 'admin_annonces_index'],
    ['GET',  '/admin/messages/nouveau', 'admin_annonces_create'],
    ['POST', '/admin/messages/nouveau', 'admin_annonces_store'],
    ['GET',  '/admin/messages/(\d+)', 'admin_annonces_edit'],
    ['POST', '/admin/messages/(\d+)', 'admin_annonces_update'],
    ['POST', '/admin/messages/(\d+)/supprimer', 'admin_annonces_delete'],

    ['GET',  '/admin/codes', 'admin_codes_index'],
    ['POST', '/admin/codes', 'admin_codes_store'],
    ['GET',  '/admin/codes/generes', 'admin_codes_created'],
    ['POST', '/admin/codes/(\d+)/revoquer', 'admin_codes_revoke'],

    ['GET',  '/admin/classes', 'admin_classes_index'],
    ['POST', '/admin/classes', 'admin_classes_store'],
    ['POST', '/admin/classes/(\d+)', 'admin_classes_update'],
    ['POST', '/admin/classes/(\d+)/supprimer', 'admin_classes_delete'],

    ['GET',  '/admin/utilisateurs', 'admin_users_index'],
    ['GET',  '/admin/utilisateurs/(\d+)', 'admin_users_edit'],
    ['POST', '/admin/utilisateurs/(\d+)', 'admin_users_update'],
    ['POST', '/admin/utilisateurs/(\d+)/mot-de-passe', 'admin_users_reset_password'],
    ['POST', '/admin/utilisateurs/(\d+)/supprimer', 'admin_users_delete'],
];
