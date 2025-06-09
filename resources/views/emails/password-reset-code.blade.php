<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: #4a90e2;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-body {
            padding: 40px 30px;
        }
        .code-container {
            background-color: #f8f9fa;
            border: 2px dashed #4a90e2;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            color: #4a90e2;
            letter-spacing: 4px;
            margin: 10px 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>PHARMACIA</h1>
            <p>Système de Gestion de Pharmacie</p>
        </div>
        
        <div class="email-body">
            <h2>Bonjour {{ $userName }},</h2>
            
            <p>Vous avez demandé la réinitialisation de votre mot de passe. Voici votre code de vérification :</p>
            
            <div class="code-container">
                <p>Votre code de vérification est :</p>
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important :</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Ce code est valide pendant <strong>10 minutes</strong> seulement</li>
                    <li>Ne partagez ce code avec personne</li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                </ul>
            </div>
            
            <p>Saisissez ce code sur la page de réinitialisation pour créer un nouveau mot de passe.</p>
            
            <p>Cordialement,<br>L'équipe Pharmacia</p>
        </div>
        
        <div class="email-footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} Pharmacia - Système de Gestion de Pharmacie</p>
        </div>
    </div>
</body>
</html>