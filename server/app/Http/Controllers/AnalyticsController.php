<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Source;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    private Balance $balance;
    private Source $source;

    public function __construct(Balance $balance, Source $source)
    {
        $this->balance = $balance;
        $this->source = $source;
    }

    public function overview(string $lang): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        $user = Auth::user();

        $totalIncome = $this->balance->getTotalIncome($user->id);
        $incomeLastMonth = $this->balance->getIncomeLastMonth($user->id);

        $totalOutgoing = $this->balance->getTotalOutgoing($user->id);
        $outgoingLastMonth = $this->balance->getOutgoingLastMonth($user->id);

        $totalPortfolio = $this->balance->getTotalPortfolio($user->id);
        $portfolioLastMonth = $this->balance->getPortfolioLastMonth($user->id);

        return response()->json([
            'income' => [
                'total' => $totalIncome,
                'lastMonth' => $incomeLastMonth,
            ],
            'spending' => [
                'total' => $totalOutgoing,
                'lastMonth' => $outgoingLastMonth,
            ],
            'cashFlow' => [
                'total' => $totalPortfolio - $totalOutgoing,
                'lastMonth' => $incomeLastMonth - $outgoingLastMonth,
            ],
            'portfolio' => [
                'total' => $totalPortfolio,
                'lastMonth' => $portfolioLastMonth,
            ],
        ], 200);
    }

    public function statistics(string $lang): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        $user = Auth::user();

        $incomeHistory = $this->balance->getIncomeHistory($user->id);
        $spendingHistory = $this->balance->getSpendingHistory($user->id);
        $cashFlowHistory = $this->balance->getCashFlowHistory($user->id);
        $portfolioHistory = $this->balance->getPortfolioHistory($user->id);

        return response()->json([
            'income' => $incomeHistory,
            'spending' => $spendingHistory,
            'cashFlow' => $cashFlowHistory,
            'portfolio' => $portfolioHistory,
        ], 200);
    }

    public function overviewOf(string $lang, int $sourceId): \Illuminate\Http\JsonResponse
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        if (!$this->source->find($sourceId)) {
            return Translation::NotFoundResponse($lang);
        }

        $user = Auth::user();

        $totalIncome = $this->balance->getTotalIncomeOf($user->id, $sourceId);
        $incomeLastMonth = $this->balance->getIncomeLastMonthOf($user->id, $sourceId);

        $totalOutgoing = $this->balance->getTotalOutgoingOf($user->id, $sourceId);
        $outgoingLastMonth = $this->balance->getOutgoingLastMonthOf($user->id, $sourceId);

        $totalPortfolio = $this->balance->getTotalPortfolioOf($user->id, $sourceId);
        $portfolioLastMonth = $this->balance->getPortfolioLastMonthOf($user->id, $sourceId);

        return response()->json([
            'income' => [
                'total' => $totalIncome,
                'lastMonth' => $incomeLastMonth,
            ],
            'spending' => [
                'total' => $totalOutgoing,
                'lastMonth' => $outgoingLastMonth,
            ],
            'cashFlow' => [
                'total' => $totalPortfolio - $totalOutgoing,
                'lastMonth' => $incomeLastMonth - $outgoingLastMonth,
            ],
            'portfolio' => [
                'total' => $totalPortfolio,
                'lastMonth' => $portfolioLastMonth,
            ],
        ], 200);
    }

    public function statisticsOf(string $lang, int $sourceId)
    {
        if (!Auth::check()) {
            return Translation::UnauthorizedResponse($lang);
        }

        if (!$this->source->find($sourceId)) {
            return Translation::NotFoundResponse($lang);
        }

        $user = Auth::user();

        $incomeHistory = $this->balance->getIncomeHistoryOf($user->id, $sourceId);
        $spendingHistory = $this->balance->getSpendingHistoryOf($user->id, $sourceId);
        $cashFlowHistory = $this->balance->getCashFlowHistoryOf($user->id, $sourceId);
        $portfolioHistory = $this->balance->getPortfolioHistoryOf($user->id, $sourceId);

        return response()->json([
            'income' => $incomeHistory,
            'spending' => $spendingHistory,
            'cashFlow' => $cashFlowHistory,
            'portfolio' => $portfolioHistory,
        ], 200);
    }
}
