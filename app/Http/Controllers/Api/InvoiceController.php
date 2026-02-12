<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Services\Invoice\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Download payout invoice
     * GET /api/invoices/payout/{payout}
     */
    public function downloadPayoutInvoice(PayoutRequest $payout, Request $request): StreamedResponse|JsonResponse
    {
        try {
            // Authorization: user can only access their own invoices
            if ($payout->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $content = $this->invoiceService->generatePayoutInvoice($payout);
            $filename = $this->invoiceService->getInvoiceFilename($payout);

            return response()->streamDownload(
                function () use ($content) { echo $content; },
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payout invoice preview
     * GET /api/invoices/payout/{payout}/preview
     */
    public function getPayoutInvoicePreview(PayoutRequest $payout, Request $request): JsonResponse
    {
        try {
            // Authorization: user can only access their own invoices
            if ($payout->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $preview = $this->invoiceService->getInvoicePreviewData($payout);

            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving invoice preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download earnings invoice for period
     * GET /api/invoices/earnings
     */
    public function downloadEarningsInvoice(Request $request): StreamedResponse|JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $user = $request->user();
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $content = $this->invoiceService->generateEarningsInvoice($user, $startDate, $endDate);
            $filename = "Earnings-Invoice-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.pdf";

            return response()->streamDownload(
                function () use ($content) { echo $content; },
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating earnings invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get earnings summary
     * GET /api/invoices/earnings/summary
     */
    public function getEarningsSummary(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $user = $request->user();
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $summary = $this->invoiceService->getEarningsSummary($user, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving earnings summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download tax form (1099-NEC)
     * GET /api/invoices/tax-form
     */
    public function downloadTaxForm(Request $request): StreamedResponse|JsonResponse
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:' . now()->year,
            ]);

            $user = $request->user();
            $year = $validated['year'];

            $content = $this->invoiceService->generateTaxForm($user, $year);
            $filename = $this->invoiceService->getTaxFormFilename($user, $year);

            return response()->streamDownload(
                function () use ($content) { echo $content; },
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating tax form: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's payouts for invoicing
     * GET /api/invoices/payouts
     */
    public function getUserPayouts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = (int) $request->get('per_page', 15);

            $payouts = $user->payoutRequests()
                ->latest()
                ->paginate($perPage);

            $data = $payouts->map(function ($payout) {
                return [
                    'id' => $payout->id,
                    'amount' => $payout->amount,
                    'status' => $payout->status,
                    'method' => $payout->method,
                    'created_at' => $payout->created_at,
                    'preview' => $this->invoiceService->getInvoicePreviewData($payout),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $payouts->total(),
                    'per_page' => $payouts->perPage(),
                    'current_page' => $payouts->currentPage(),
                    'last_page' => $payouts->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payouts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invoice statistics
     * GET /api/invoices/statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $totalPayouts = $user->payoutRequests()->count();
            $totalAmount = $user->payoutRequests()->sum('amount');
            $pendingAmount = $user->payoutRequests()
                ->where('status', 'pending')
                ->sum('amount');
            $paidAmount = $user->payoutRequests()
                ->where('status', 'paid')
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_payouts' => $totalPayouts,
                    'total_amount' => $totalAmount,
                    'pending_amount' => $pendingAmount,
                    'paid_amount' => $paidAmount,
                    'average_payout' => $totalPayouts > 0 ? $totalAmount / $totalPayouts : 0,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
