<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de vente - {{ $sale->sale_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%);
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.6;
            color: #2c3e50;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
        }
        
        .receipt {
            max-width: 850px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(51, 102, 153, 0.1);
        }
        
        .header {
            background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .pharmacy-logo {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            border: 6px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            position: relative;
        }
        
        .pharmacy-logo::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            background: linear-gradient(45deg, #f8f9fa, #ffffff, #f8f9fa);
            border-radius: 50%;
            z-index: -1;
        }
        
        .pharmacy-logo img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.2));
        }
        
        .pharmacy-logo i {
            font-size: 5rem;
            color: #336699;
            text-shadow: 0 4px 20px rgba(51, 102, 153, 0.3);
            filter: drop-shadow(0 3px 10px rgba(51, 102, 153, 0.4));
        }
        
        .pharmacy-name {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 3px;
            margin-bottom: 12px;
            text-shadow: 0 3px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .pharmacy-tagline {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 400;
            margin-bottom: 18px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .pharmacy-info {
            font-size: 0.85rem;
            opacity: 0.85;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
        }
        
        .receipt-body {
            padding: 30px;
        }
        
        .sale-number-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-align: center;
            margin: -15px auto 30px;
            width: fit-content;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            font-family: 'Poppins', sans-serif;
        }
        
        .sale-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .info-section {
            background: rgba(51, 102, 153, 0.05);
            border-radius: 15px;
            padding: 20px;
            border-left: 4px solid #336699;
        }
        
        .info-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #336699;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-line {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }
        
        .info-line strong {
            color: #2c3e50;
            min-width: 120px;
        }
        
        .info-value {
            text-align: right;
            font-weight: 500;
            color: #336699;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-paid {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
        }
        
        .status-failed {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .alert-box {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-allergies {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
        }
        
        .alert-prescription {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
            border: none;
        }
        
        .alert-notes {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            color: white;
            border: none;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .products-table th {
            background: linear-gradient(135deg, #336699 0%, #4a90e2 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .products-table td {
            padding: 15px 12px;
            border-bottom: 1px solid rgba(51, 102, 153, 0.1);
            background: white;
        }
        
        .products-table tr:nth-child(even) td {
            background: rgba(51, 102, 153, 0.02);
        }
        
        .products-table tr:hover td {
            background: rgba(51, 102, 153, 0.05);
        }
        
        .product-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .product-dosage {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 3px;
        }
        
        .product-prescription {
            font-size: 0.75rem;
            color: #ffc107;
            font-weight: 500;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .quantity-badge {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #336699;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        
        .price-cell {
            font-weight: 600;
            color: #336699;
        }
        
        .total-cell {
            font-weight: 700;
            color: #28a745;
            font-size: 1.1rem;
        }
        
        .totals-section {
            background: rgba(51, 102, 153, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 2px solid rgba(51, 102, 153, 0.1);
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
            font-size: 1rem;
        }
        
        .total-line.subtotal {
            color: #6c757d;
        }
        
        .total-line.tax {
            color: #336699;
            font-weight: 500;
        }
        
        .total-line.discount {
            color: #dc3545;
            font-weight: 500;
        }
        
        .total-line.final {
            background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
            color: white;
            font-weight: 700;
            font-size: 1.3rem;
            border-radius: 12px;
            padding: 15px 20px;
            margin-top: 15px;
            margin-bottom: 0;
            box-shadow: 0 4px 15px rgba(51, 102, 153, 0.3);
        }
        
        .footer {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            text-align: center;
            border-top: 3px solid #336699;
            margin-top: 30px;
        }
        
        .footer-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #336699;
            margin-bottom: 15px;
        }
        
        .footer-info {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-legal {
            font-size: 0.75rem;
            color: #adb5bd;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .prescription-warning {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            font-weight: 600;
            text-align: center;
        }
        
        .print-button {
            position: fixed;
            top: 30px;
            right: 30px;
            background: linear-gradient(180deg, #336699 0%, #4a90e2 100%);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 8px 25px rgba(51, 102, 153, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(51, 102, 153, 0.4);
        }
        
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .receipt {
                border-radius: 0;
                box-shadow: none;
                border: none;
                max-width: none;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .header {
                background: #336699 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .pharmacy-logo {
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .sale-info {
                grid-template-columns: 1fr 1fr;
                page-break-inside: avoid;
            }
            
            .products-table {
                page-break-inside: avoid;
            }
            
            .totals-section {
                page-break-inside: avoid;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .receipt {
                border-radius: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .receipt-body {
                padding: 20px;
            }
            
            .pharmacy-logo {
                width: 120px;
                height: 120px;
            }
            
            .pharmacy-logo img {
                width: 90px;
                height: 90px;
            }
            
            .pharmacy-logo i {
                font-size: 4rem;
            }
            
            .pharmacy-name {
                font-size: 2rem;
                letter-spacing: 2px;
            }
            
            .sale-info {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .products-table th,
            .products-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }
            
            .print-button {
                position: relative;
                top: auto;
                right: auto;
                margin: 0 auto 20px;
                display: block;
                width: fit-content;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimer le reçu
    </button>

    <div class="receipt">
        <!-- En-tête avec logo -->
        <div class="header">
            <div class="pharmacy-logo">
                <img src="{{ asset('images/logo.png') }}" alt="PHARMACIA Logo" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <i class="fas fa-plus-circle" style="display: none;"></i>
            </div>
            <div class="pharmacy-name">PHARMACIA</div>
            <div class="pharmacy-tagline">Système de Gestion de Pharmacie</div>
            <div class="pharmacy-info">
                123 Avenue de la Santé, 75001 Paris<br>
                Tél: 01 23 45 67 89 | Email: contact@pharmacia.com<br>
                SIRET: 123 456 789 00012 | TVA: FR12345678901
            </div>
        </div>

        <div class="receipt-body">
            <!-- Badge numéro de vente -->
            <div class="sale-number-badge">
                <i class="fas fa-receipt"></i> Vente {{ $sale->sale_number }}
            </div>

            <!-- Informations de la vente -->
            <div class="sale-info">
                <div class="info-section">
                    <div class="info-title">
                        <i class="fas fa-info-circle"></i>
                        Détails de la vente
                    </div>
                    <div class="info-line">
                        <strong>Date et heure:</strong>
                        <span class="info-value">{{ $sale->sale_date->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="info-line">
                        <strong>Vendeur:</strong>
                        <span class="info-value">{{ $sale->user->name }}</span>
                    </div>
                    <div class="info-line">
                        <strong>Mode de paiement:</strong>
                        <span class="info-value">{{ ucfirst($sale->payment_method) }}</span>
                    </div>
                    <div class="info-line">
                        <strong>Statut:</strong>
                        <span class="status-badge 
                            @if($sale->payment_status == 'paid') status-paid
                            @elseif($sale->payment_status == 'pending') status-pending  
                            @else status-failed @endif">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-title">
                        <i class="fas fa-user"></i>
                        Informations client
                    </div>
                    @if($sale->client)
                        <div class="info-line">
                            <strong>Nom complet:</strong>
                            <span class="info-value">{{ $sale->client->full_name }}</span>
                        </div>
                        @if($sale->client->phone)
                            <div class="info-line">
                                <strong>Téléphone:</strong>
                                <span class="info-value">{{ $sale->client->phone }}</span>
                            </div>
                        @endif
                        @if($sale->client->email)
                            <div class="info-line">
                                <strong>Email:</strong>
                                <span class="info-value">{{ $sale->client->email }}</span>
                            </div>
                        @endif
                        @if($sale->client->insurance_number)
                            <div class="info-line">
                                <strong>N° Assurance:</strong>
                                <span class="info-value">{{ $sale->client->insurance_number }}</span>
                            </div>
                        @endif
                    @else
                        <div class="info-line">
                            <span style="font-style: italic; color: #6c757d;">Client anonyme</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Alerte allergies client -->
            @if($sale->client && $sale->client->allergies)
                <div class="alert-box alert-allergies">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                    <div>
                        <strong>ALLERGIES CONNUES:</strong><br>
                        {{ $sale->client->allergies }}
                    </div>
                </div>
            @endif

            <!-- Information ordonnance -->
            @if($sale->has_prescription)
                <div class="alert-box alert-prescription">
                    <i class="fas fa-prescription-bottle fa-lg"></i>
                    <div>
                        <strong>VENTE AVEC ORDONNANCE</strong>
                        @if($sale->prescription_number)
                            <br>Numéro d'ordonnance: {{ $sale->prescription_number }}
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tableau des produits -->
            <table class="products-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-pills me-2"></i>Produit</th>
                        <th class="text-center"><i class="fas fa-hashtag me-1"></i>Qté</th>
                        <th class="text-right"><i class="fas fa-euro-sign me-1"></i>Prix unitaire</th>
                        <th class="text-right"><i class="fas fa-calculator me-1"></i>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->saleItems as $item)
                        <tr>
                            <td>
                                <div class="product-name">{{ $item->product->name }}</div>
                                @if($item->product->dosage)
                                    <div class="product-dosage">Dosage: {{ $item->product->dosage }}</div>
                                @endif
                                @if($item->product->prescription_required)
                                    <div class="product-prescription">
                                        <i class="fas fa-prescription-bottle me-1"></i>Ordonnance requise
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="quantity-badge">{{ $item->quantity }}</span>
                            </td>
                            <td class="text-right price-cell">{{ number_format($item->unit_price, 2) }} €</td>
                            <td class="text-right total-cell">{{ number_format($item->total_price, 2) }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Section totaux -->
            <div class="totals-section">
                <div class="total-line subtotal">
                    <span><i class="fas fa-calculator me-2"></i>Sous-total:</span>
                    <span>{{ number_format($sale->subtotal, 2) }} €</span>
                </div>
                <div class="total-line tax">
                    <span><i class="fas fa-percent me-2"></i>TVA (20%):</span>
                    <span>{{ number_format($sale->tax_amount, 2) }} €</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="total-line discount">
                        <span><i class="fas fa-tag me-2"></i>Remise:</span>
                        <span>-{{ number_format($sale->discount_amount, 2) }} €</span>
                    </div>
                @endif
                <div class="total-line final">
                    <span><i class="fas fa-money-bill-wave me-2"></i>TOTAL À PAYER:</span>
                    <span>{{ number_format($sale->total_amount, 2) }} €</span>
                </div>
            </div>

            <!-- Notes -->
            @if($sale->notes)
                <div class="alert-box alert-notes" style="margin-top: 25px;">
                    <i class="fas fa-sticky-note fa-lg"></i>
                    <div>
                        <strong>Notes:</strong><br>
                        {{ $sale->notes }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div class="footer-title">
                <i class="fas fa-heart me-2"></i>
                Merci pour votre confiance !
            </div>
            <div class="footer-info">
                Reçu généré le {{ now()->format('d/m/Y à H:i') }}<br>
                Conservez ce reçu pour vos remboursements d'assurance<br>
                Pour toute question, contactez-nous au 01 23 45 67 89
            </div>
            <div class="footer-legal">
                Établissement pharmaceutique autorisé par l'ARS<br>
                Pharmacien responsable: Dr. Jean MARTIN<br>
                N° SIRET: 123 456 789 00012 | TVA: FR12345678901
            </div>
            
            @if($sale->has_prescription)
                <div class="prescription-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Important: Respectez la posologie prescrite par votre médecin
                </div>
            @endif
        </div>
    </div>

    <script>
        // Impression automatique optionnelle
        // window.onload = function() { 
        //     setTimeout(() => window.print(), 500); 
        // }
        
        // Fermer après impression (optionnel)
        window.onafterprint = function() {
            // window.close(); // Décommentez pour fermer automatiquement
        }
        
        // Amélioration de l'impression
        function optimizePrint() {
            document.querySelectorAll('.no-print').forEach(el => {
                el.style.display = 'none';
            });
        }
        
        // Gestion du logo en erreur
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.querySelector('.pharmacy-logo img');
            const fallbackIcon = document.querySelector('.pharmacy-logo i');
            
            if (logo) {
                logo.onerror = function() {
                    logo.style.display = 'none';
                    fallbackIcon.style.display = 'flex';
                };
            }
        });
        
        // Animation du bouton d'impression
        document.querySelector('.print-button').addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    </script>
</body>
</html>