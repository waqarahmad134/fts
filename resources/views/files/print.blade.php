<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print File - {{ $file->file_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: white;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 2px solid #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .file-details {
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .detail-label {
            font-weight: bold;
            width: 200px;
            color: #333;
        }

        .detail-value {
            flex: 1;
            color: #555;
        }

        .qr-section {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            border: 2px dashed #666;
            background: #f9f9f9;
        }

        .qr-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        .qr-code-image {
            max-width: 300px;
            margin: 0 auto;
            display: block;
        }

        .qr-instructions {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #696cff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background: #5a5dcc;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                padding: 0;
            }

            .print-container {
                border: none;
                padding: 0;
            }

            .qr-section {
                page-break-inside: avoid;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-closed {
            background: #d4edda;
            color: #155724;
        }

        .status-reopened {
            background: #cce5ff;
            color: #004085;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        🖨️ Print
    </button>

    <div class="print-container">
        <div class="header">
            <h1>File Tracking System</h1>
            <p>File Details & QR Code</p>
        </div>

        <div class="file-details">
            <div class="detail-row">
                <div class="detail-label">File No:</div>
                <div class="detail-value"><strong>{{ $file->file_no }}</strong></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Subject:</div>
                <div class="detail-value">{{ $file->subject }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    <span class="status-badge status-{{ $file->status }}">
                        {{ ucfirst($file->status) }}
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Created By:</div>
                <div class="detail-value">{{ $file->creator->name ?? 'N/A' }} ({{ $file->creator->role->name ?? 'N/A' }})</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Created At:</div>
                <div class="detail-value">{{ $file->created_at->format('d M Y, h:i A') }}</div>
            </div>

            @if($file->handover_note)
            <div class="detail-row">
                <div class="detail-label">Handover Note:</div>
                <div class="detail-value">{{ $file->handover_note }}</div>
            </div>
            @endif

            <div class="detail-row">
                <div class="detail-label">PUC Proposal:</div>
                <div class="detail-value">{{ Str::limit($file->puc_proposal, 200) }}</div>
            </div>
        </div>

        <div class="qr-section">
            <h2>Scan to Transfer File</h2>
            <img src="{{ url('/files/' . $file->id . '/qr-code') }}" alt="QR Code" class="qr-code-image">
            <p class="qr-instructions">
                Scan this QR code with the FTS mobile app to receive and track this file
            </p>
        </div>

        <div class="footer">
            <p>File Tracking System - Generated on {{ now()->format('d M Y, h:i A') }}</p>
            <p>This is a system-generated document</p>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional - can be removed if not desired)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>
