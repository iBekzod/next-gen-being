<?php

namespace App\Services\Invoice;

use App\Models\BloggerEarning;
use App\Models\PayoutRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class InvoiceService
{
    /**
     * Generate invoice for a single payout
     */
    public function generatePayoutInvoice(PayoutRequest $payout): string
    {
        try {
            $data = [
                'invoice_number' => $this->generateInvoiceNumber($payout),
                'payout' => $payout,
                'user' => $payout->user,
                'issued_date' => now()->toDateString(),
                'company' => [
                    'name' => config('app.name'),
                    'address' => config('invoice.company_address'),
                    'phone' => config('invoice.company_phone'),
                    'email' => config('invoice.company_email'),
                ],
                'item_description' => "Content creation and platform engagement payout",
            ];

            $pdf = Pdf::loadView('invoices.payout', $data);
            return $pdf->output();

        } catch (Exception $e) {
            \Log::error("Invoice generation failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Generate invoice for earnings period
     */
    public function generateEarningsInvoice(User $user, Carbon $startDate, Carbon $endDate): string
    {
        try {
            $earnings = $user->earnings()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            if ($earnings->isEmpty()) {
                throw new Exception('No earnings found for the specified period');
            }

            $data = [
                'invoice_number' => $this->generateEarningsInvoiceNumber($user, $startDate),
                'user' => $user,
                'earnings' => $earnings,
                'period_start' => $startDate->toDateString(),
                'period_end' => $endDate->toDateString(),
                'total_earnings' => $earnings->sum('amount'),
                'issued_date' => now()->toDateString(),
                'company' => [
                    'name' => config('app.name'),
                    'address' => config('invoice.company_address'),
                    'phone' => config('invoice.company_phone'),
                    'email' => config('invoice.company_email'),
                ],
            ];

            $pdf = Pdf::loadView('invoices.earnings', $data);
            return $pdf->output();

        } catch (Exception $e) {
            \Log::error("Earnings invoice generation failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Generate tax form (1099-NEC equivalent)
     */
    public function generateTaxForm(User $user, int $year): string
    {
        try {
            $startDate = Carbon::createFromFormat('Y', $year)->startOfYear();
            $endDate = $startDate->copy()->endOfYear();

            $earnings = $user->earnings()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalNonEmployeeCompensation = $earnings->sum('amount');

            // Don't generate if under threshold (typically $600)
            if ($totalNonEmployeeCompensation < 600) {
                throw new Exception("Total earnings ($totalNonEmployeeCompensation) below reporting threshold");
            }

            $data = [
                'form_year' => $year,
                'user' => $user,
                'company' => [
                    'name' => config('app.name'),
                    'ein' => config('invoice.company_ein'),
                    'address' => config('invoice.company_address'),
                ],
                'non_employee_compensation' => $totalNonEmployeeCompensation,
                'earnings' => $earnings,
                'generated_date' => now()->toDateString(),
            ];

            $pdf = Pdf::loadView('invoices.tax-form-1099', $data);
            return $pdf->output();

        } catch (Exception $e) {
            \Log::error("Tax form generation failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Generate bulk invoices for multiple users/payouts
     */
    public function generateBulkInvoices(Collection $payouts): Collection
    {
        $invoices = collect();

        foreach ($payouts as $payout) {
            try {
                $invoice = [
                    'payout_id' => $payout->id,
                    'user_id' => $payout->user_id,
                    'content' => $this->generatePayoutInvoice($payout),
                    'filename' => $this->getInvoiceFilename($payout),
                ];

                $invoices->push($invoice);

            } catch (Exception $e) {
                \Log::error("Bulk invoice generation failed for payout {$payout->id}: {$e->getMessage()}");
            }
        }

        return $invoices;
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(PayoutRequest $payout): string
    {
        $year = $payout->created_at->year;
        $month = str_pad($payout->created_at->month, 2, '0', STR_PAD_LEFT);
        return "INV-{$year}-{$month}-" . str_pad($payout->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate earnings invoice number
     */
    private function generateEarningsInvoiceNumber(User $user, Carbon $date): string
    {
        $year = $date->year;
        $month = str_pad($date->month, 2, '0', STR_PAD_LEFT);
        return "EARN-{$year}-{$month}-" . str_pad($user->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get invoice filename
     */
    private function getInvoiceFilename(PayoutRequest $payout): string
    {
        $invoiceNumber = $this->generateInvoiceNumber($payout);
        $userName = str_slug($payout->user->name);
        return "{$invoiceNumber}-{$userName}.pdf";
    }

    /**
     * Get tax form filename
     */
    public function getTaxFormFilename(User $user, int $year): string
    {
        return "1099-NEC-{$year}-{$user->id}-" . str_slug($user->name) . ".pdf";
    }

    /**
     * Calculate invoice totals
     */
    public function calculateInvoiceTotals(PayoutRequest $payout): array
    {
        $subtotal = $payout->amount;
        $tax = 0; // Tax handling depends on jurisdiction and configuration
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Get invoice preview data
     */
    public function getInvoicePreviewData(PayoutRequest $payout): array
    {
        return [
            'invoice_number' => $this->generateInvoiceNumber($payout),
            'issued_date' => now()->toDateString(),
            'user' => [
                'name' => $payout->user->name,
                'email' => $payout->user->email,
            ],
            'amount' => $payout->amount,
            'status' => $payout->status,
            'method' => $payout->method,
            'totals' => $this->calculateInvoiceTotals($payout),
        ];
    }

    /**
     * Check if invoice exists in storage
     */
    public function invoiceExists(PayoutRequest $payout): bool
    {
        $filename = $this->getInvoiceFilename($payout);
        $path = storage_path("invoices/{$filename}");
        return file_exists($path);
    }

    /**
     * Store invoice to disk
     */
    public function storeInvoice(PayoutRequest $payout, string $content): string
    {
        $filename = $this->getInvoiceFilename($payout);
        $directory = storage_path('invoices');

        // Create directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = "{$directory}/{$filename}";
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * Get stored invoice
     */
    public function getStoredInvoice(PayoutRequest $payout): ?string
    {
        $filename = $this->getInvoiceFilename($payout);
        $path = storage_path("invoices/{$filename}");

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    /**
     * Delete stored invoice
     */
    public function deleteStoredInvoice(PayoutRequest $payout): bool
    {
        $filename = $this->getInvoiceFilename($payout);
        $path = storage_path("invoices/{$filename}");

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    /**
     * Archive invoices for a period
     */
    public function archiveInvoices(Carbon $startDate, Carbon $endDate): array
    {
        $payouts = PayoutRequest::whereBetween('created_at', [$startDate, $endDate])->get();
        $archived = [];

        foreach ($payouts as $payout) {
            try {
                $content = $this->generatePayoutInvoice($payout);
                $path = $this->storeInvoice($payout, $content);
                $archived[] = [
                    'payout_id' => $payout->id,
                    'path' => $path,
                    'status' => 'archived',
                ];
            } catch (Exception $e) {
                \Log::error("Failed to archive invoice for payout {$payout->id}: {$e->getMessage()}");
                $archived[] = [
                    'payout_id' => $payout->id,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $archived;
    }

    /**
     * Get earnings summary for period
     */
    public function getEarningsSummary(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $earnings = $user->earnings()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $earningsByType = $earnings->groupBy('type')
            ->mapWithKeys(function ($group, $type) {
                return [$type => $group->sum('amount')];
            });

        return [
            'period' => "{$startDate->toDateString()} to {$endDate->toDateString()}",
            'total_earnings' => $earnings->sum('amount'),
            'earnings_count' => $earnings->count(),
            'earnings_by_type' => $earningsByType->toArray(),
            'average_earning' => $earnings->count() > 0 ? $earnings->avg('amount') : 0,
        ];
    }
}
