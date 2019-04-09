# language: fr
Fonctionnalité: J'instancie mon bundle puis le configure

Scénario: Configurer ElasticsearchBundle comme il faut
    Etant donné que je crée un nouveau kernel de test
    Quand       je configure le kernel avec le fichier "config.php"
    Et          je boot le kernel
    Alors       ca devrait s'être bien déroulé
    Et          le service "json.json_request_service" devrait exister
    Et          je n'ai plus besoin du kernel de test
