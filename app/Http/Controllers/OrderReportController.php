<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $orders = $this->query($filters)
            ->with(['customer:id,name', 'items.service:id,name'])
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('report-orders', [
            'orders' => $orders,
            'summary' => $this->summary($filters, $orders),
            'filters' => $filters,
        ]);
    }

    public function download(Request $request)
    {
        $filters = $this->filters($request);

        $orders = $this->query($filters)
            ->with(['customer:id,name', 'items.service:id,name'])
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        $summary = $this->summary($filters);

        $rows = (function () use ($orders, $filters, $summary) {
            yield ["Order Report - {$filters['start_date']} to {$filters['end_date']}"];
            yield [];
            yield ['Order Number', 'Date', 'Customer', 'Services', 'Amount', 'Paid', 'Balance', 'Status'];

            foreach ($orders as $order) {
                yield [
                    $order->order_number,
                    $order->order_date?->format('Y-m-d'),
                    $order->customer->name ?? 'Walk-in Customer',
                    $order->items->map(fn ($item) => ($item->service->name ?? 'Deleted service')
                        . ($item->qty > 1 ? " (x{$item->qty})" : ''))->implode(', '),
                    number_format((float) $order->total, 2, '.', ''),
                    number_format((float) $order->paid_amount, 2, '.', ''),
                    number_format($order->balance, 2, '.', ''),
                    $order->status_label,
                ];
            }

            yield [];
            yield ['Summary'];
            yield ['Total Orders', $summary['total_orders']];
            yield ['Total Amount', number_format($summary['total_amount'], 2, '.', '')];
            yield ['Completed', $summary['completed_count']];
            yield ['Pending', $summary['pending_count']];
            yield ['Processing', $summary['processing_count']];
            yield ['Cancelled', $summary['cancelled_count']];
            yield [];
            yield ['Generated on', now()->format('Y-m-d H:i:s')];
        })();

        return $this->csvDownload("order_report_{$filters['start_date']}_to_{$filters['end_date']}.csv", $rows);
    }

    private function filters(Request $request): array
    {
        $start = $this->dateInput($request, 'start_date', date('Y-m-01'));
        $end = $this->dateInput($request, 'end_date', date('Y-m-t'));

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $status = (string) $request->input('status', '');
        if (!in_array($status, Order::STATUSES, true)) {
            $status = '';
        }

        return ['start_date' => $start, 'end_date' => $end, 'status' => $status];
    }

    private function query(array $filters)
    {
        return Order::betweenDates($filters['start_date'], $filters['end_date'])
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']));
    }

    /**
     * Totals for the whole filtered period (not just the current page), in one query.
     */
    private function summary(array $filters, $page = null): array
    {
        $row = $this->query($filters)
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(total), 0) as total_amount')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count")
            ->first();

        return [
            'total_orders' => (int) $row->total_orders,
            'total_amount' => (float) $row->total_amount,
            'completed_count' => (int) $row->completed_count,
            'pending_count' => (int) $row->pending_count,
            'processing_count' => (int) $row->processing_count,
            'cancelled_count' => (int) $row->cancelled_count,
            'page_orders' => $page ? $page->count() : 0,
            'page_amount' => $page ? (float) $page->sum('total') : 0,
        ];
    }
}
