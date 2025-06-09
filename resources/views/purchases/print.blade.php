<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande d'achat - {{ $purchase->purchase_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #e3f2fd 0%, #e8f5e8 100%);
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        
        .purchase-order {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .purchase-order::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #336699 0%, #4a90e2 100%);
        }
        
        .header {
            text-align: center;
            padding-bottom: 30px;
            margin-bottom: 30px;
            border-bottom: 3px solid #336699;
            position: relative;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 25px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border: 4px solid #f8f9fa;
            position: relative;
        }
        
        .logo img {
            max-width: 80px !important;
            max-height: 80px !important;
            border-radius: 50%;
        }
        
        .logo i {
            color: #336699 !important;
            font-size: 48px !important;
        }
        
        .system-info {
            text-align: left;
        }
        
        .system-name {
            font-size: 42px;
            font-weight: 700;
            color: #336699;
            margin-bottom: 5px;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .system-subtitle {
            color: #336699;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .pharmacy-info {
            color: #888;
            font-size: 12px;
            font-weight: 400;
        }
        
        .order-title {
            text-align: center;
            margin: 30px 0;
            font-size: 22px;
            font-weight: 700;
            color: #336699;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #336699;
            position: relative;
        }
        
        .order-title::before {
            content: '📋';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
        }
        
        .status-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border: none;
            border-left: 5px solid #17a2b8;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 0 10px 10px 0;
            color: #0c5460;
            font-weight: 500;
        }
        
        .info-section {
            margin-bottom: 25px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .supplier-info {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border-left: 5px solid #28a745;
            padding: 20px;
        }
        
        .order-info {
            background: linear-gradient(135deg, #e8f4fd 0%, #d1ecf1 100%);
            border-left: 5px solid #336699;
            padding: 20px;
        }
        
        .info-title {
            font-weight: 700;
            color: #336699;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .info-title::before {
            content: '▶';
            margin-right: 10px;
            color: #336699;
        }
        
        .info-line {
            margin-bottom: 8px;
            font-weight: 400;
        }
        
        .info-line strong {
            color: #336699;
            font-weight: 600;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .products-table th {
            background: linear-gradient(135deg, #336699 0%, #4a90e2 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .products-table td {
            border: none;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 12px;
            background: white;
        }
        
        .products-table tbody tr:nth-child(even) td {
            background: #f8f9fa;
        }
        
        .products-table tbody tr:hover td {
            background: rgba(51, 102, 153, 0.05);
        }
        
        .products-table .text-center {
            text-align: center;
        }
        
        .products-table .text-right {
            text-align: right;
        }
        
        .products-table tfoot tr {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .products-table tfoot .total-row {
            background: linear-gradient(135deg, #336699 0%, #4a90e2 100%) !important;
            color: white;
        }
        
        .products-table tfoot th {
            background: transparent;
            color: #336699;
            font-weight: 600;
            padding: 12px;
        }
        
        .products-table tfoot .total-row th {
            color: white;
        }
        
        .notes-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 5px solid #ffc107;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 0 12px 12px 0;
        }
        
        .notes-section strong {
            color: #856404;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .order-footer {
            margin-top: 40px;
            border-top: 3px solid #336699;
            padding-top: 30px;
        }
        
        .conditions {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        
        .conditions strong {
            color: #336699;
            font-size: 16px;
            font-weight: 600;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .signature-box {
            flex: 1;
            height: 100px;
            border: 2px solid #336699;
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .signature-box strong {
            color: #336699;
            font-weight: 600;
            font-size: 13px;
        }
        
        .footer-info {
            text-align: center;
            font-size: 11px;
            color: #666;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #336699 0%, #4a90e2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(51, 102, 153, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(51, 102, 153, 0.4);
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
                background: white;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .purchase-order {
                border-radius: 0;
                box-shadow: none;
                padding: 20px;
            }
            
            .no-print {
                display: none !important;
            }
            
            .products-table th,
            .products-table tfoot .total-row th {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .signature-box {
                page-break-inside: avoid;
            }
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Imprimer
    </button>

    <div class="purchase-order">
        <!-- En-tête avec logo PHARMACIA -->
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="PHARMACIA Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <i class="fas fa-pills" style="display: none;"></i>
                </div>
                <div class="system-info">
                    <div class="system-name">PHARMACIA</div>
                    <div class="system-subtitle">Pharmacie Moderne & Professionnelle</div>
                    <div class="pharmacy-info">
                        Système de Gestion de Pharmacie<br>
                        123 Avenue de la Santé, 75001 Paris<br>
                        Tél: 01 23 45 67 89 | Email: contact@pharmacia.com
                    </div>
                </div>
            </div>
        </div>

        <!-- Titre de la commande -->
        <div class="order-title">
            BON DE COMMANDE N° {{ $purchase->purchase_number }}
        </div>

        <!-- Statut de la commande -->
        <div class="status-info">
            <strong>📊 Statut de la commande:</strong> 
            <span class="badge badge-{{ $purchase->status === 'received' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'danger') }}">
                {{ $purchase->status_label }}
            </span>
            @if($purchase->expected_date && $purchase->expected_date->isPast() && $purchase->status === 'pending')
                - <span style="color: #dc3545; font-weight: 700;">⚠️ EN RETARD</span> (prévu le {{ $purchase->expected_date->format('d/m/Y') }})
            @endif
        </div>

        <!-- Informations fournisseur -->
        <div class="info-section supplier-info">
            <div class="info-title">🏢 INFORMATIONS FOURNISSEUR</div>
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <div class="info-line"><strong>Nom:</strong> {{ $purchase->supplier->name }}</div>
                    @if($purchase->supplier->contact_person)
                        <div class="info-line"><strong>Contact:</strong> {{ $purchase->supplier->contact_person }}</div>
                    @endif
                    @if($purchase->supplier->address)
                        <div class="info-line"><strong>Adresse:</strong> {{ $purchase->supplier->address }}</div>
                    @endif
                </div>
                <div style="flex: 1; min-width: 250px;">
                    @if($purchase->supplier->phone_number)
                        <div class="info-line"><strong>📞 Téléphone:</strong> {{ $purchase->supplier->phone_number }}</div>
                    @endif
                    @if($purchase->supplier->email)
                        <div class="info-line"><strong>📧 Email:</strong> {{ $purchase->supplier->email }}</div>
                    @endif
                    @if($purchase->supplier->siret)
                        <div class="info-line"><strong>SIRET:</strong> {{ $purchase->supplier->siret }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations commande -->
        <div class="info-section order-info">
            <div class="info-title">📋 DÉTAILS DE LA COMMANDE</div>
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <div class="info-line"><strong>📅 Date de commande:</strong> {{ $purchase->order_date->format('d/m/Y') }}</div>
                    @if($purchase->expected_date)
                        <div class="info-line"><strong>🚚 Date de livraison prévue:</strong> {{ $purchase->expected_date->format('d/m/Y') }}</div>
                    @endif
                    <div class="info-line"><strong>👤 Commandé par:</strong> {{ $purchase->user->name }}</div>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <div class="info-line"><strong>🖨️ Date d'impression:</strong> {{ now()->format('d/m/Y à H:i') }}</div>
                    @if($purchase->received_date)
                        <div class="info-line"><strong>✅ Date de réception:</strong> {{ $purchase->received_date->format('d/m/Y') }}</div>
                    @endif
                    <div class="info-line"><strong>📈 Progression:</strong> {{ $purchase->progress_percentage }}% ({{ $purchase->received_items }}/{{ $purchase->total_items }})</div>
                </div>
            </div>
        </div>

        <!-- Notes de commande -->
        @if($purchase->notes)
            <div class="notes-section">
                <strong>📝 NOTES DE COMMANDE</strong>
                <p style="margin: 10px 0 0 0; font-weight: 400;">{{ $purchase->notes }}</p>
            </div>
        @endif

        <!-- Tableau des produits -->
        <table class="products-table">
            <thead>
                <tr>
                    <th>💊 PRODUIT</th>
                    <th class="text-center">📦 QTÉ COMMANDÉE</th>
                    <th class="text-center">✅ QTÉ REÇUE</th>
                    <th class="text-right">💰 PRIX UNITAIRE</th>
                    <th class="text-right">💳 TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->purchaseItems as $item)
                    <tr>
                        <td>
                            <strong style="color: #336699;">{{ $item->product->name }}</strong>
                            @if($item->product->dosage)
                                <br><small style="color: #666;">Dosage: {{ $item->product->dosage }}</small>
                            @endif
                            @if($item->notes)
                                <br><small style="color: #17a2b8; font-weight: 500;">📋 {{ $item->notes }}</small>
                            @endif
                        </td>
                        <td class="text-center" style="font-weight: 600;">{{ $item->quantity_ordered }}</td>
                        <td class="text-center">
                            <span style="font-weight: 600; color: {{ $item->quantity_received == $item->quantity_ordered ? '#28a745' : '#ffc107' }};">
                                {{ $item->quantity_received }}
                            </span>
                            @if($item->remaining_quantity > 0)
                                <br><small class="badge badge-warning">Reste: {{ $item->remaining_quantity }}</small>
                            @endif
                        </td>
                        <td class="text-right" style="font-weight: 500;">{{ number_format($item->unit_price, 2) }} €</td>
                        <td class="text-right" style="font-weight: 700; color: #336699;">{{ number_format($item->total_price, 2) }} €</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Sous-total HT:</th>
                    <th class="text-right">{{ number_format($purchase->subtotal, 2) }} €</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">TVA (20%):</th>
                    <th class="text-right">{{ number_format($purchase->tax_amount, 2) }} €</th>
                </tr>
                <tr class="total-row">
                    <th colspan="4" class="text-right">💰 TOTAL TTC:</th>
                    <th class="text-right">{{ number_format($purchase->total_amount, 2) }} €</th>
                </tr>
            </tfoot>
        </table>

        <!-- Pied de page -->
        <div class="order-footer">
            <div class="conditions">
                <strong>📋 CONDITIONS DE LIVRAISON ET DE RÉCEPTION</strong><br><br>
                <div style="text-align: left; font-size: 13px; color: #666;">
                    ✓ Livraison à l'adresse de la pharmacie aux heures d'ouverture (8h-19h)<br>
                    ✓ Vérification obligatoire de la conformité et des dates d'expiration<br>
                    ✓ Contrôle de température pour les produits thermosensibles<br>
                    ✓ Tout produit non conforme ou endommagé sera refusé<br>
                    ✓ Accusé de réception obligatoire avec signature
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature-box">
                    <strong>✍️ Signature du responsable<br>des achats</strong>
                    <div style="margin-top: 10px; color: #666; font-weight: 500;">{{ $purchase->user->name }}</div>
                </div>
                <div class="signature-box">
                    <strong>🏢 Cachet et signature<br>du fournisseur</strong>
                    <div style="margin-top: 10px; color: #666; font-weight: 500;">{{ $purchase->supplier->name }}</div>
                </div>
            </div>

            <div class="footer-info">
                <strong style="color: #336699;">PHARMACIA - Pharmacie Moderne & Professionnelle</strong><br>
                Système de Gestion de Pharmacie<br>
                N° SIRET: 123 456 789 00012 | Responsable: {{ auth()->user()->name ?? 'Pharmacien' }}<br>
                📞 Contact: 01 23 45 67 89 | 📧 contact@pharmacia.com<br><br>
                <em>Ce bon de commande fait foi jusqu'à réception complète de la marchandise</em><br>
                <small>Document généré automatiquement le {{ now()->format('d/m/Y à H:i:s') }}</small>
            </div>
        </div>
    </div>

    <script>
        // Fermer la fenêtre après impression (optionnel)
        window.onafterprint = function() {
            // window.close(); // Décommentez si vous voulez fermer automatiquement
        }
        
        // Animation d'impression
        document.querySelector('.print-button').addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    </script>
</body>
</html>