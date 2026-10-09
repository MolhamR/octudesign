<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShamCashService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey  = get_settings('sham_cash_api_key');
        $this->baseUrl = get_settings('sham_cash_base_url');

        if (!$this->apiKey) {
            $this->apiKey = config('services.shamcash.key');
        }
        if (!$this->baseUrl) {
            $this->baseUrl = config('services.shamcash.base_url');
        }
    }

    public function isConfigured()
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    public function getHistory()
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(300)
                ->retry(3, 100)
                ->get($this->baseUrl . '/api/account/history');

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json('data');
            return $data['log'] ?? [];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Collect transactions whose peerAccountAddress matches the given account
     * within the last 30 days, in SYP. When minimumAmount is greater than 0 the
     * amount must be at least that value, otherwise every amount is returned
     * (used when the student does not specify an amount).
     */
    public function matchingTransactions($history, $accountAddress, $minimumAmount = 0)
    {
        $matched = [];
        if (!is_array($history)) {
            return $matched;
        }

        foreach ($history as $transaction) {
            if (($transaction['peerAccountAddress'] ?? null) != $accountAddress) {
                continue;
            }
            if (($transaction['currencyName'] ?? '') != 'SYP') {
                continue;
            }

            $tranDate = isset($transaction['tranDate'])
                ? \Carbon\Carbon::parse($transaction['tranDate'])
                : null;

            if ($tranDate && $tranDate->gte(now()->subDays(30)->startOfDay())
                && (float) ($transaction['amount'] ?? 0) >= (float) $minimumAmount) {
                $matched[] = $transaction;
            }
        }

        return $matched;
    }

    public function sendMoney($accountNumber, $amount)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key'     => $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
                ->timeout(30)
                ->retry(3, 100)
                ->post($this->baseUrl . '/api/transaction/new', [
                    'amount'        => $amount,
                    'currency'      => 'SYP',
                    'accountAddress' => $accountNumber,
                ]);

            return $response->ok() && $response->json('succeeded');
        } catch (\Exception $e) {
            return false;
        }
    }
}