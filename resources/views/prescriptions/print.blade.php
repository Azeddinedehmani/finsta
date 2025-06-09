<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnance - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: white;
            font-size: 14px;
            line-height: 1.4;
        }
        .prescription {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border: 2px solid #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .pharmacy-name {
            font-size: 28px;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 5px;
        }
        .pharmacy-info {
            color: #666;
            font-size: 12px;
        }
        .prescription-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .info-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
        }
        .info-title {
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-line {
            margin-bottom: 5px;
        }
        .patient-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #4a90e2;
            margin-bottom: 20px;
        }
        .doctor-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin-bottom: 20px;
        }
        .medications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .medications-table th,
        .medications-table td {
            border: 1px solid #333;
            padding: 12px;
            text-align: left;
        }
        .medications-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .medications-table .text-center {
            text-align: center;
        }
        .prescription-footer {
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 200px;
            height: 80px;
            border: 1px solid #333;
            text-align: center;
            padding: 10px;
        }
        .medical-notes {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .allergies-alert {
            background-color: #f8d7da;
            border: 1px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #721c24;
        }
        .status-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #0c5460;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .prescription {
                border: 2px solid #333;
                box-shadow: none;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-button:hover {
            background-color: #357abd;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Imprimer
    </button>

    <div class="prescription">
        <!-- En-tête -->
        <div class="header">
            <div class="pharmacy-name">PHARMACIA</div>
            <div class="pharmacy-info">
                Système de Gestion de Pharmacie<br>
                123 Avenue de la Santé, 75001 Paris<br>
                Tél: 01 23 45 67 89 | Email: contact@pharmacia.com
            </div>
        </div>

        <!-- Statut de l'ordonnance -->
        <div class="status-info">
            <strong>Statut de l'ordonnance:</strong> {{ $prescription->status_label }}
            @if($prescription->isExpired())
                - <span style="color: #dc3545;"><strong>EXPIRÉE</strong></span>
            @elseif($prescription->isAboutToExpire())
                - <span style="color: #ffc107;"><strong>Expire dans {{ $prescription->expiry_date->diffInDays(now()) }} jour(s)</strong></span>
            @endif
        </div>

        <!-- Informations patient -->
        <div class="patient-info">
            <div class="info-title">INFORMATIONS PATIENT</div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                    <div class="info-line"><strong>Nom:</strong> {{ $prescription->client->full_name }}</div>
                    @if($prescription->client->date_of_birth)
                        <div class="info-line"><strong>Date de naissance:</strong> {{ $prescription->client->date_of_birth->format('d/m/Y') }}</div>
                        <div class="info-line"><strong>Âge:</strong> {{ $prescription->client->age }} ans</div>
                    @endif
                </div>
                <div style="flex: 1;">
                    @if($prescription->client->phone)
                        <div class="info-line"><strong>Téléphone:</strong> {{ $prescription->client->phone }}</div>
                    @endif
                    @if($prescription->client->insurance_number)
                        <div class="info-line"><strong>N° Assurance:</strong> {{ $prescription->client->insurance_number }}</div>
                    @endif
                    @if($prescription->client->address)
                        <div class="info-line"><strong>Adresse:</strong> {{ $prescription->client->address }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alerte allergies -->
        @if($prescription->client->allergies)
            <div class="allergies-alert">
                <strong>⚠️ ALLERGIES CONNUES:</strong> {{ $prescription->client->allergies }}
            </div>
        @endif

        <!-- Informations médecin -->
        <div class="doctor-info">
            <div class="info-title">MÉDECIN PRESCRIPTEUR</div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                    <div class="info-line"><strong>Dr {{ $prescription->doctor_name }}</strong></div>
                    @if($prescription->doctor_speciality)
                        <div class="info-line">{{ $prescription->doctor_speciality }}</div>
                    @endif
                </div>
                <div style="flex: 1;">
                    @if($prescription->doctor_phone)
                        <div class="info-line"><strong>Tél:</strong> {{ $prescription->doctor_phone }}</div>
                    @endif
                    <div class="info-line"><strong>Date prescription:</strong> {{ $prescription->prescription_date->format('d/m/Y') }}</div>
                    <div class="info-line"><strong>Validité:</strong> {{ $prescription->expiry_date->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Notes médicales -->
        @if($prescription->medical_notes)
            <div class="medical-notes">
                <strong>📋 NOTES MÉDICALES:</strong>
                <p style="margin: 10px 0 0 0;">{{ $prescription->medical_notes }}</p>
            </div>
        @endif

        <!-- Ordonnance -->
        <div style="text-align: center; margin: 30px 0; font-size: 18px; font-weight: bold; border: 2px solid #333; padding: 10px;">
            ORDONNANCE N° {{ $prescription->prescription_number }}
        </div>

        <!-- Tableau des médicaments -->
        <table class="medications-table">
            <thead>
                <tr>
                    <th>MÉDICAMENT</th>
                    <th class="text-center">QTÉ PRESCRITE</th>
                    <th class="text-center">QTÉ DÉLIVRÉE</th>
                    <th>POSOLOGIE</th>
                    <th>INSTRUCTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prescription->prescriptionItems as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if($item->product->dosage)
                                <br><small>{{ $item->product->dosage }}</small>
                            @endif
                            @if($item->duration_days)
                                <br><small>Durée: {{ $item->duration_days }} jour(s)</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity_prescribed }}</td>
                        <td class="text-center">
                            {{ $item->quantity_delivered }}
                            @if($item->remaining_quantity > 0)
                                <br><small style="color: #dc3545;">Reste: {{ $item->remaining_quantity }}</small>
                            @endif
                        </td>
                        <td style="font-weight: bold;">{{ $item->dosage_instructions }}</td>
                        <td>{{ $item->instructions ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Notes du pharmacien -->
        @if($prescription->pharmacist_notes)
            <div style="background-color: #e8f4fd; padding: 15px; border-left: 4px solid #007bff; margin-bottom: 20px;">
                <strong>💊 NOTES DU PHARMACIEN:</strong>
                <p style="margin: 10px 0 0 0;">{{ $prescription->pharmacist_notes }}</p>
            </div>
        @endif

        <!-- Pied de page -->
        <div class="prescription-footer">
            <div style="text-align: center; margin-bottom: 20px;">
                <strong>RESPECTEZ LA POSOLOGIE PRESCRITE PAR VOTRE MÉDECIN</strong><br>
                <small>En cas de doute, consultez votre pharmacien ou votre médecin</small>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <strong>Date d'impression:</strong> {{ now()->format('d/m/Y à H:i') }}
                </div>
                <div>
                    <strong>Imprimé par:</strong> {{ $prescription->createdBy->name }}
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-box">
                    <strong>Signature du médecin</strong><br>
                    Dr {{ $prescription->doctor_name }}
                </div>
                <div class="signature-box">
                    <strong>Cachet et signature<br>du pharmacien</strong>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
                Pharmacia - Système de Gestion de Pharmacie<br>
                N° SIRET: 123 456 789 00012 | Pharmacien responsable: {{ auth()->user()->name }}
            </div>
        </div>
    </div>

    <script>
        // Fermer la fenêtre après impression
        window.onafterprint = function() {
            // window.close(); // Décommentez si vous voulez fermer automatiquement
        }
    </script>
</body>
</html>