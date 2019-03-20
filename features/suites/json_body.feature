# language: fr
Fonctionnalité: Envoyer du json à l'API, et le retrouver dans la request

Scénario: Envoyer un bon json
    Quand       je fais un POST sur / avec le corps contenu dans "good_body.json"
    Alors       le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    {
       "content": "This was a good body bro"
    }
    """

Scénario: Envoyer pas du json
    Quand       je fais un POST sur / avec le corps contenu dans "bad_body.json"
    Alors       le status HTTP devrait être 400
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    "Invalid JSON data"
    """

Scénario: Ne rien envoyer
    Quand       je fais un POST sur /
    Alors       le status HTTP devrait être 200
    Et          je devrais avoir un résultat d'API en JSON
    Et          le résultat devrait être identique au JSON suivant :
    """
    []
    """
