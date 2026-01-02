<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; }
        .invoice-header { text-align: center; margin-bottom: 30px; }
        .invoice-details { margin-bottom: 30px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .details-row div { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .totals { text-align: right; margin-bottom: 30px; }
        .total-row { margin-bottom: 5px; }
        .footer { text-align: center; margin-top: 50px; color: #666; font-size: 12px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <h2>{{ $order->order_number }}</h2>
        </div>
        
        <div class="invoice-details">
            <div class="details-row">
                <div>
                    <strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('F d, Y') }}<br>
                    <strong>Status:</strong> {{ ucfirst($order->status) }}
                </div>
                <div>
                    <strong>Customer:</strong> {{ $order->customer ? $order->customer->name : 'Walk In Customer' }}<br>
                    <strong>Created By:</strong> {{ $order->staff->name ?? 'N/A' }}
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->service->name ?? 'Service' }}</td>
                    <td>{{ number_format($item->price, 2) }} USD</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->total, 2) }} USD</td>
                </tr>
                @endforeach
                
                @if($order->orderAddons->count() > 0)
                <tr>
                    <td colspan="4" style="background-color: #f2f2f2; font-weight: bold;">Add-ons</td>
                </tr>
                @foreach($order->orderAddons as $addon)
                <tr>
                    <td>{{ $addon->addon->name ?? 'Addon' }}</td>
                    <td>{{ number_format($addon->price, 2) }} USD</td>
                    <td>1</td>
                    <td>{{ number_format($addon->price, 2) }} USD</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
        
        <div class="totals">
            <div class="total-row">
                <strong>Subtotal:</strong> {{ number_format($order->subtotal, 2) }} USD
            </div>
            @if($order->discount > 0)
            <div class="total-row">
                <strong>Discount:</strong> -{{ number_format($order->discount, 2) }} USD
            </div>
            @endif
            <div class="total-row" style="font-size: 18px;">
                <strong>Grand Total:</strong> {{ number_format($order->total, 2) }} USD
            </div>
            
            @if($order->payments->count() > 0)
            <div class="total-row" style="margin-top: 20px;">
                <strong>Total Paid:</strong> {{ number_format($order->paid_amount, 2) }} USD
            </div>
            @if($order->paid_amount < $order->total)
            <div class="total-row">
                <strong>Balance Due:</strong> {{ number_format($order->total - $order->paid_amount, 2) }} USD
            </div>
            @endif
            @endif
        </div>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Invoice generated on {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Print Invoice
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                Close Window
            </button>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>