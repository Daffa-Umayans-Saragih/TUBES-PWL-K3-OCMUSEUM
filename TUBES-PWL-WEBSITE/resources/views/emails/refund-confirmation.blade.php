<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Refund Confirmation</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F5F7FA; color: #1E293B; margin: 0; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 12px; border: 1px solid #D9E2EC; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: bold; color: #103B78; letter-spacing: -0.025em; text-transform: uppercase; }
        .title { font-size: 22px; font-weight: 700; color: #082B5B; margin-top: 10px; margin-bottom: 20px; }
        .message { font-size: 16px; line-height: 1.6; color: #1E293B; margin-bottom: 30px; }
        .amount-box { background: #F5F7FA; border: 1px solid #D9E2EC; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 30px; }
        .amount-label { font-size: 14px; font-weight: 600; color: #103B78; text-transform: uppercase; letter-spacing: 0.05em; }
        .amount-value { font-size: 32px; font-weight: 800; color: #082B5B; margin-top: 5px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table th, .details-table td { padding: 12px 0; border-bottom: 1px solid #D9E2EC; text-align: left; font-size: 15px; }
        .details-table th { color: #1E293B; font-weight: 600; width: 35%; }
        .details-table td { color: #103B78; font-weight: 500; }
        .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 40px; border-top: 1px solid #D9E2EC; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏛️ OC Museum</div>
            <div class="title">Ticket Refund Confirmed</div>
        </div>
        
        <div class="message">
            Hello, <strong>{{ $customerName }}</strong>.
            <br><br>
            Your refund for order <strong>{{ $order->order_code }}</strong> has been successfully processed. The tickets associated with this transaction have been marked as invalid.
        </div>
        
        <div class="amount-box">
            <div class="amount-label">Refund Amount</div>
            <div class="amount-value">₹{{ number_format($refundAmount, 2) }}</div>
        </div>
        
        <table class="details-table">
            <tr>
                <th>Order Reference</th>
                <td>{{ $order->order_code }}</td>
            </tr>
            <tr>
                <th>Refund Date</th>
                <td>{{ $refundDate }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>Refunded</td>
            </tr>
            <tr>
                <th>Ticket Summary</th>
                <td>{{ $ticketSummary }}</td>
            </tr>
        </table>
        
        <div class="message" style="margin-bottom: 0;">
            If you have any questions or require further assistance, please contact our support team.
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} OC Museum. All rights reserved.
        </div>
    </div>
</body>
</html>
