<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    use HasFactory;

    protected $table = 'balances';
    protected $primaryKey = 'id';

    public function create(
        int  $source,
        int  $user,
        int  $in,
        int  $out,
        bool $subscription
    ): Balance
    {
        $this->source_id = $source;
        $this->user_id = $user;
        $this->in = $in;
        $this->out = $out;
        $this->subscription = $subscription;
        $this->save();
        return $this;
    }

    public function getAllFrom(int $userId, int $page, int $limit)
    {
        return Balance::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getTotalIncome($id): float
    {
        return Balance::where('user_id', $id)
            ->where('in', '>', 0)
            ->sum('in');
    }

    public function getIncomeLastMonth($id): float
    {
        return Balance::where('user_id', $id)
            ->where('in', '>', 0)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('in');
    }

    public function getTotalOutgoing($id): float
    {
        return Balance::where('user_id', $id)
            ->where('out', '>', 0)
            ->sum('out');
    }

    public function getOutgoingLastMonth($id): float
    {
        return Balance::where('user_id', $id)
            ->where('out', '>', 0)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('out');
    }

    public function getTotalPortfolio($id): float
    {
        return Balance::where('user_id', $id)
                ->whereIn('source_id', function ($query) {
                    $query->select('id')
                        ->from('sources')
                        ->where('type', 'portfolio');
                })
                ->sum('in')
            + Balance::where('user_id', $id)
                ->whereIn('source_id', function ($query) {
                    $query->select('id')
                        ->from('sources')
                        ->where('type', 'portfolio');
                })
                ->sum('out');
    }

    public function getPortfolioLastMonth($id)
    {
        return Balance::where('user_id', $id)
                ->whereIn('source_id', function ($query) {
                    $query->select('id')
                        ->from('sources')
                        ->where('type', 'portfolio');
                })
                ->where('created_at', '>=', now()->subMonth())
                ->sum('in')
            + Balance::where('user_id', $id)
                ->whereIn('source_id', function ($query) {
                    $query->select('id')
                        ->from('sources')
                        ->where('type', 'portfolio');
                })
                ->where('created_at', '>=', now()->subMonth())
                ->sum('out');
    }

    public function getIncomeHistory($id): array
    {
        return Balance::where('user_id', $id)
            ->orderBy('created_at', 'asc')
            ->select('in', 'out')
            ->pluck('in')
            ->toArray();
    }

    public function getSpendingHistory($id): array
    {
        return Balance::where('user_id', $id)
            ->orderBy('created_at', 'asc')
            ->select('in', 'out')
            ->pluck('out')
            ->toArray();
    }

    private function calculateCashFlowHistory($values): array {
        $currentIn = 0;
        $currentOut = 0;
        $cashFlowHistory = [];

        foreach ($values as $value) {
            $currentIn += $value['in'];
            $currentOut += $value['out'];
            $cashFlowHistory[] = [
                'in' => $currentIn,
                'out' => $currentOut,
                'created_at' => $value['created_at']
            ];
        }

        $cashFlow = [];

        foreach ($cashFlowHistory as $value) {
            $cashFlow[] = $value['in'] - $value['out'];
        }
        return $cashFlow;
    }

    public function getCashFlowHistory($id): array
    {
        $values = Balance::where('user_id', $id)
            ->orderBy('created_at', 'asc')
            ->select('in', 'out', 'created_at')
            ->get()
            ->toArray();

        return $this->calculateCashFlowHistory($values);
    }

    private function calculatePortfolioHistory($portfolio, $allCreations): array {
        $sums = [];
        $portfolioIdx = 0;
        $portfolioWorth = 0;

        foreach ($allCreations as $creation) {
            if ($portfolioIdx >= count($portfolio)) {
                $sums[] = end($sums) ?: 0;
                continue;
            }
            $portfolioDate = Carbon::parse($portfolio[$portfolioIdx]['created_at']);
            $creationDate = Carbon::parse($creation);

            if ($portfolioDate->isBefore($creationDate)) {
                $sums[] = end($sums) ?: 0;
                continue;
            }

            $portfolioAdd = $portfolio[$portfolioIdx]['in'] + $portfolio[$portfolioIdx]['out'];
            $portfolioWorth = $portfolioWorth + $portfolioAdd;
            $sums[] = $portfolioWorth;
            $portfolioIdx++;
        }

        return $sums;
    }

    public function getPortfolioHistory($id): array
    {
        $portfolio = Balance::where('user_id', $id)
            ->whereIn('source_id', function ($query) {
                $query->select('id')
                    ->from('sources')
                    ->where('type', 'portfolio');
            })
            ->orderBy('created_at', 'asc')
            ->select('in', 'out', 'created_at')
            ->get()
            ->toArray();

        $allCreations = Balance::where('user_id', $id)
            ->orderBy('created_at', 'asc')
            ->select('created_at')
            ->pluck('created_at')
            ->toArray();

        return $this->calculatePortfolioHistory($portfolio, $allCreations);
    }

    public function getTotalIncomeOf($id, int $sourceId): float
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->where('in', '>', 0)
            ->sum('in');
    }

    public function getIncomeLastMonthOf($id, int $sourceId): float
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->where('in', '>', 0)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('in');
    }

    public function getTotalOutgoingOf($id, int $sourceId): float
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->where('out', '>', 0)
            ->sum('out');
    }

    public function getOutgoingLastMonthOf($id, int $sourceId): float
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->where('out', '>', 0)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('out');
    }

    public function getTotalPortfolioOf($id, int $sourceId): float
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->sum('in')
            + Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->sum('out');
    }

    public function getPortfolioLastMonthOf($id, int $sourceId): float
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('in')
            + Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('out');
    }

    public function getIncomeHistoryOf($id, int $sourceId): array
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->orderBy('created_at', 'asc')
            ->select('in', 'out')
            ->pluck('in')
            ->toArray();
    }

    public function getSpendingHistoryOf($id, int $sourceId): array
    {
        return Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->orderBy('created_at', 'asc')
            ->select('in', 'out')
            ->pluck('out')
            ->toArray();
    }

    public function getCashFlowHistoryOf($id, int $sourceId): array
    {
        $values = Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->orderBy('created_at', 'asc')
            ->select('in', 'out', 'created_at')
            ->get()
            ->toArray();

        return $this->calculateCashFlowHistory($values);
    }

    public function getPortfolioHistoryOf($id, int $sourceId): array
    {
        $portfolio = Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->whereIn('source_id', function ($query) use ($id) {
                $query->select('id')
                    ->from('sources')
                    ->where('type', 'portfolio');
            })
            ->orderBy('created_at', 'asc')
            ->select('in', 'out', 'created_at')
            ->get()
            ->toArray();

        $allCreations = Balance::where('user_id', $id)
            ->where('source_id', $sourceId)
            ->orderBy('created_at', 'asc')
            ->select('created_at')
            ->pluck('created_at')
            ->toArray();

        return $this->calculatePortfolioHistory($portfolio, $allCreations);
    }
}
