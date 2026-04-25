<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .header { background: #1A1A1A; color: #F9F9F9; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .message { background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SweetHome Milano</h1>
        <p>Nuovo messaggio dal sito</p>
    </div>

    <div class="content">
        <div class="message">
            <p><strong>Da:</strong> {{ $submission->name }} ({{ $submission->email }})</p>
            <p><strong>Telefono:</strong> {{ $submission->phone ?? 'Non fornito' }}</p>
            <p><strong>Oggetto:</strong> {{ $submission->subject }}</p>
            <p><strong>Messaggio:</strong></p>
            <p>{{ $submission->message }}</p>
        </div>
    </div>

    <div class="footer">
        <p>Questa è una notifica automatica dal sito SweetHome Milano.</p>
    </div>
</body>
</html>
