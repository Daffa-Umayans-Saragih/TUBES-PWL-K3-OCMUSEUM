<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Your OC Museum Ticket Confirmation</title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #F5F7FA; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1E293B; }
        a { color: #082B5B; text-decoration: underline; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #F5F7FA; padding-top: 40px; padding-bottom: 40px; }
        .main-content { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .hero { background-color: #082B5B; color: #ffffff; padding: 40px 30px; text-align: center; }
        .hero h1 { margin: 0; font-size: 28px; font-weight: 300; letter-spacing: 2px; text-transform: uppercase; }
        .hero p { margin: 10px 0 0; font-size: 16px; color: #F5F7FA; font-weight: 300; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 20px; font-weight: bold; color: #1E293B; margin-top: 0; margin-bottom: 10px; }
        .intro-text { font-size: 16px; color: #1E293B; line-height: 1.5; margin-top: 0; margin-bottom: 30px; }
        
        .summary-card { background-color: #ffffff; border: 1px solid #D9E2EC; border-radius: 6px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
        .summary-card h3 { margin-top: 0; margin-bottom: 15px; font-size: 16px; color: #103B78; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #F5F7FA; padding-bottom: 10px; }
        .summary-table { width: 100%; }
        .summary-table td { padding: 6px 0; font-size: 14px; color: #1E293B; }
        .summary-table td.label { font-weight: bold; color: #1E293B; width: 40%; }
        
        .ticket-card { border: 1px solid #D9E2EC; border-left: 4px solid #082B5B; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .ticket-header { font-size: 18px; font-weight: bold; color: #103B78; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #D9E2EC; padding-bottom: 10px; }
        .ticket-layout { width: 100%; }
        .ticket-info { font-size: 14px; color: #1E293B; line-height: 1.6; }
        .ticket-info strong { color: #1E293B; }
        .ticket-qr { text-align: right; width: 160px; vertical-align: top; }
        .qr-wrapper { background: #ffffff; padding: 10px; border: 1px solid #082B5B; border-radius: 4px; display: inline-block; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-valid { background-color: #F5F7FA; color: #082B5B; }
        .status-pending { background-color: #fef7e0; color: #f29900; }
        .status-used { background-color: #e1f5fe; color: #01579b; }

        .info-panel { background-color: #F5F7FA; border-left: 4px solid #082B5B; padding: 20px; margin-top: 30px; margin-bottom: 30px; border-radius: 0 6px 6px 0; }
        .info-panel h4 { margin-top: 0; margin-bottom: 10px; font-size: 15px; color: #103B78; text-transform: uppercase; letter-spacing: 1px; }
        .info-panel ul { margin: 0; padding-left: 20px; font-size: 14px; color: #1E293B; line-height: 1.6; }
        .info-panel li { margin-bottom: 8px; }

        .btn-container { text-align: center; margin-top: 30px; margin-bottom: 10px; }
        .btn { display: inline-block; background-color: #082B5B; color: #ffffff !important; text-decoration: none; font-size: 16px; font-weight: bold; padding: 14px 28px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; }

        .footer { background-color: #103B78; color: #F5F7FA; text-align: center; padding: 30px; font-size: 12px; line-height: 1.5; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; }
        .footer a { color: #ffffff; text-decoration: none; font-weight: bold; }
        .footer .logo { font-size: 20px; color: #ffffff; font-weight: bold; margin-bottom: 15px; letter-spacing: 2px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td align="center">
                    <!-- Main Content Container -->
                    <table class="main-content" width="100%" cellpadding="0" cellspacing="0" border="0" align="center" style="max-width: 600px;">
                        
                        <!-- Hero Header -->
                        <tr>
                            <td class="hero">
                                <h1>OC</h1>
                                <p>Your Ticket Is Confirmed</p>
                            </td>
                        </tr>
                        
                        <!-- Content Area -->
                        <tr>
                            <td class="content">
                                <p class="greeting">Hi {{ $billing ? ($billing['first_name'] . ' ' . $billing['last_name']) : 'there' }},</p>
                                @if(isset($order) && strtolower($order->order_type) === 'membership')
                                    <p class="intro-text">Thank you for purchasing a Membership to OC Museum. Your payment has been successfully processed and your invoice is attached below.</p>
                                @else
                                    <p class="intro-text">Thank you for booking your visit to OC Museum. Your admission tickets have been successfully generated and are ready for your visit.</p>
                                @endif
                                
                                <!-- Order Summary -->
                                <div class="summary-card">
                                    <h3>Order Summary</h3>
                                    <table class="summary-table" width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td class="label">Order Code</td>
                                            <td>{{ $order->order_code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Purchase Date</td>
                                            <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('F j, Y') : now()->format('F j, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Total Paid</td>
                                            <td>${{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Payment Status</td>
                                            <td><strong style="color: #082B5B;">PAID</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="label">Admission Date</td>
                                            <td>
                                                @php
                                                    $admissionDate = null;
                                                    if ($order->tickets->isNotEmpty() && $order->tickets->first()->ticketAvailability && $order->tickets->first()->ticketAvailability->visitSchedule) {
                                                        $admissionDate = $order->tickets->first()->ticketAvailability->visitSchedule->visit_date;
                                                    }
                                                @endphp
                                                {{ $admissionDate ? \Carbon\Carbon::parse($admissionDate)->format('F j, Y') : 'Date Flexible' }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                 @if(isset($order) && strtolower($order->order_type) === 'membership')
                                     <!-- Membership Item Card -->
                                     <div class="ticket-card">
                                         <div class="ticket-header">Membership</div>
                                         <table class="ticket-layout" cellpadding="0" cellspacing="0" border="0">
                                             <tr>
                                                 <td class="ticket-info">
                                                     <p style="margin: 0 0 8px 0;"><strong>Membership Tier:</strong><br>{{ $order->membership->tier_name ?? 'MET Premium Membership' }}</p>
                                                     <p style="margin: 0 0 8px 0;"><strong>Payment Status:</strong><br><span class="status-badge status-valid">PAID</span></p>
                                                     <p style="margin: 0;"><strong>Important:</strong><br>An activation link has been sent to the designated email address.</p>
                                                 </td>
                                             </tr>
                                         </table>
                                     </div>
                                 @else
                                     <!-- Ticket Cards -->
                                     @foreach($order->tickets as $ticket)
                                     @php
                                         $statusClass = 'status-valid';
                                         $statusText = strtoupper($ticket->status);
                                         if (strtolower($ticket->status) === 'pending') $statusClass = 'status-pending';
                                         elseif (strtolower($ticket->status) === 'used') $statusClass = 'status-used';
                                         
                                         $ticketType = $ticket->ticketAvailability->ticketType->name ?? 'General Admission';
                                         $visitDate = $ticket->ticketAvailability->visitSchedule->visit_date ?? null;

                                         // Generate QR PNG bytes manually using GD (so we don't require ext-imagick)
                                         $qrPngBytes = '';
                                         try {
                                             $qrObj = \BaconQrCode\Encoder\Encoder::encode($ticket->qr_code, \BaconQrCode\Common\ErrorCorrectionLevel::M());
                                             $matrix = $qrObj->getMatrix();
                                             $matrixWidth = $matrix->getWidth();
                                             $matrixHeight = $matrix->getHeight();
                                             
                                             $cellSize = 6;
                                             $margin = 1;
                                             $imgWidth = ($matrixWidth + 2 * $margin) * $cellSize;
                                             $imgHeight = ($matrixHeight + 2 * $margin) * $cellSize;
                                             
                                             $image = imagecreate($imgWidth, $imgHeight);
                                             $bg = imagecolorallocate($image, 255, 255, 255);
                                             $fg = imagecolorallocate($image, 0, 0, 0);
                                             
                                             for ($y = 0; $y < $matrixHeight; $y++) {
                                                 for ($x = 0; $x < $matrixWidth; $x++) {
                                                     if ($matrix->get($x, $y)) {
                                                         $x1 = ($x + $margin) * $cellSize;
                                                         $y1 = ($y + $margin) * $cellSize;
                                                         $x2 = $x1 + $cellSize - 1;
                                                         $y2 = $y1 + $cellSize - 1;
                                                         imagefilledrectangle($image, $x1, $y1, $x2, $y2, $fg);
                                                     }
                                                 }
                                             }
                                             
                                             ob_start();
                                             imagepng($image);
                                             $qrPngBytes = ob_get_clean();
                                             imagedestroy($image);
                                         } catch (\Throwable $e) {
                                             \Illuminate\Support\Facades\Log::error('GD QR generation failed', ['error' => $e->getMessage()]);
                                         }
                                     @endphp
                                     <div class="ticket-card">
                                         <div class="ticket-header">{{ $ticketType }} Ticket</div>
                                         <table class="ticket-layout" cellpadding="0" cellspacing="0" border="0">
                                             <tr>
                                                 <td class="ticket-info">
                                                     <p style="margin: 0 0 8px 0; word-break: break-all; overflow-wrap: break-word; font-family: monospace;"><strong>Ticket ID:</strong><br>{{ $ticket->qr_code }}</p>
                                                     <p style="margin: 0 0 8px 0;"><strong>Visit Date:</strong><br>{{ $visitDate ? \Carbon\Carbon::parse($visitDate)->format('l, F j, Y') : 'Valid any day' }}</p>
                                                     <p style="margin: 0 0 8px 0;"><strong>Location:</strong><br>OC Museum Avenue</p>
                                                     <p style="margin: 0;"><strong>Status:</strong><br><span class="status-badge {{ $statusClass }}">{{ $statusText }}</span></p>
                                                 </td>
                                                 <td class="ticket-qr">
                                                     <!-- Embedded SVG or PNG QR Code -->
                                                     <div class="qr-wrapper" style="width: 140px; height: 140px; margin: 0 auto; text-align: center;">
                                                         @if(!empty($qrPngBytes))
                                                             <img src="{{ $message->embedData($qrPngBytes, 'qr_' . $ticket->ticket_id . '.png', 'image/png') }}" width="140" height="140" alt="Ticket QR Code" style="display:block; margin: 0 auto;" />
                                                         @else
                                                             <img src="data:image/svg+xml;base64,{!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->margin(1)->generate($ticket->qr_code)) !!}" width="140" height="140" alt="Ticket QR Code" style="display:block; margin: 0 auto;" />
                                                         @endif
                                                     </div>
                                                 </td>
                                             </tr>
                                         </table>
                                     </div>
                                 @endforeach
                                
                                <!-- Visitor Information -->
                                <div class="info-panel">
                                    <h4>Visitor Information</h4>
                                    <ul>
                                        <li>Please present the QR code(s) on your mobile device at the entrance.</li>
                                        <li>Ensure your screen brightness is turned up for easy scanning.</li>
                                        <li>Galleries are cleared 15 minutes before closing.</li>
                                        <li>Large bags and backpacks are not permitted in the galleries.</li>
                                    </ul>
                                </div>
                                @endif
                                
                                <!-- Call to Action -->
                                <div class="btn-container">
                                    <a href="{{ url('/order/show/' . $order->order_id) }}" class="btn">View Order Details</a>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td class="footer">
                                <div class="logo">OC</div>
                                <p style="margin: 0 0 10px 0;">1000 Fifth Avenue, New York, NY 10028</p>
                                <p style="margin: 0 0 10px 0;">Need help? Contact us at <a href="mailto:support@ocmuseum.org">support@ocmuseum.org</a></p>
                                <p style="margin: 0; color: #1E293B;">&copy; {{ date('Y') }} OC Museum. All rights reserved.</p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>