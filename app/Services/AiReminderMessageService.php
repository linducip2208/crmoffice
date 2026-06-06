<?php

namespace App\Services;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class AiReminderMessageService
{
    public function generateReminder(Invoice $invoice, string $tone = 'friendly'): string
    {
        $client = $invoice->client;
        $context = $this->buildContext($invoice);

        if ($this->isLlmAvailable()) {
            try {
                $llmMessage = $this->aiGenerate($invoice, $context, $tone);
                if ($llmMessage) {
                    return $llmMessage;
                }
            } catch (\Throwable $e) {
                Log::warning('AiReminderMessageService LLM failed, using template', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->templatedMessage($invoice, $context, $tone);
    }

    public function generateForAllOverdue(): array
    {
        $invoices = Invoice::query()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', now())
            ->get();

        $results = [];

        foreach ($invoices as $invoice) {
            $daysOverdue = (int) $invoice->due_date->diffInDays(now());
            $tone = match (true) {
                $daysOverdue >= 30 => 'urgent',
                $daysOverdue >= 8 => 'firm',
                default => 'friendly',
            };

            $results[$invoice->id] = [
                'invoice_number' => $invoice->number,
                'client' => $invoice->client?->company_name,
                'days_overdue' => $daysOverdue,
                'tone' => $tone,
                'message' => $this->generateReminder($invoice, $tone),
            ];
        }

        return [
            'total' => $invoices->count(),
            'results' => $results,
        ];
    }

    private function buildContext(Invoice $invoice): array
    {
        $client = $invoice->client;

        $paymentCount = $invoice->payments()->count();
        $lastPayment = $invoice->payments()->latest('paid_at')->first();
        $previousInvoices = $client
            ? Invoice::where('client_id', $client->id)
                ->where('id', '!=', $invoice->id)
                ->where('status', 'paid')
                ->count()
            : 0;

        $daysOverdue = (int) $invoice->due_date->diffInDays(now());
        $reminderCount = 0;
        if ($invoice->last_reminded_at) {
            $reminderCount = 1;
        }

        return [
            'invoice_number' => $invoice->number,
            'client_name' => $client?->company_name ?? 'Client',
            'amount' => number_format((float) $invoice->total, 0, ',', '.'),
            'balance_due' => number_format((float) $invoice->balance_due, 0, ',', '.'),
            'due_date' => $invoice->due_date?->format('d F Y'),
            'days_overdue' => $daysOverdue,
            'payment_history' => "{$previousInvoices} previous invoices paid, {$paymentCount} payments on this invoice",
            'last_payment_date' => $lastPayment?->paid_at?->format('d F Y'),
            'reminder_count' => $reminderCount,
            'late_fee_applied' => (bool) $invoice->late_fee_charged_at,
        ];
    }

    private function isLlmAvailable(): bool
    {
        return LlmAdapterFactory::active() !== null;
    }

    private function aiGenerate(Invoice $invoice, array $context, string $tone): ?string
    {
        $adapter = LlmAdapterFactory::active();
        if (! $adapter) {
            return null;
        }

        $toneInstruction = match ($tone) {
            'friendly' => 'Gunakan nada ramah dan pengertian. Ini pengingat pertama. Assume niat baik dari client — mungkin lupa atau ada kendala teknis. Tawarkan bantuan jika ada pertanyaan.',
            'firm' => 'Gunakan nada tegas tapi tetap profesional dan sopan. Ini pengingat kedua/ketiga. Ingatkan tentang konsekuensi keterlambatan (late fee jika applicable) tanpa mengancam. Dorong pembayaran segera.',
            'urgent' => 'Gunakan nada serius dan mendesak. Ini pengingat final. Jelaskan bahwa akun akan ditindaklanjuti serius jika tidak dibayar segera (suspend layanan, tindakan hukum). Tetap jaga profesionalitas.',
            default => 'Gunakan nada profesional dan sopan.',
        };

        $lateFeeInfo = $context['late_fee_applied']
            ? "Biaya keterlambatan sudah dikenakan."
            : '';

        $messages = [
            ['role' => 'system', 'content' => "Anda adalah asisten keuangan profesional untuk CRM Indonesia. Tugas Anda adalah menulis pesan pengingat pembayaran invoice yang personal, sopan, dan efektif dalam Bahasa Indonesia.

{$toneInstruction}

Aturan:
- Sapa client dengan nama perusahaan mereka
- Sebutkan nomor invoice, jumlah, dan tanggal jatuh tempo
- Jelaskan berapa hari sudah terlambat
- Berikan cara pembayaran (transfer bank)
- Sertakan kontak untuk pertanyaan
- Jangan gunakan markdown atau HTML
- Maksimal 3 paragraf
- Return HANYA teks pesan, tanpa prefix, tanpa tanda kutip"],
            ['role' => 'user', 'content' => "Buat pesan pengingat pembayaran untuk:\n\nClient: {$context['client_name']}\nInvoice: #{$context['invoice_number']}\nTotal: Rp {$context['amount']}\nSisa Tagihan: Rp {$context['balance_due']}\nJatuh Tempo: {$context['due_date']}\nTerlambat: {$context['days_overdue']} hari\nRiwayat Pembayaran: {$context['payment_history']}\n{$lateFeeInfo}\n\nTone: {$tone}"],
        ];

        try {
            $response = $adapter->chat($messages, [
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            $content = trim($response->content);
            if (strlen($content) > 10) {
                return $content;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('AiReminderMessageService AI generation error: ' . $e->getMessage());

            return null;
        }
    }

    private function templatedMessage(Invoice $invoice, array $context, string $tone): string
    {
        $clientName = $context['client_name'];
        $number = $context['invoice_number'];
        $amount = $context['amount'];
        $balance = $context['balance_due'];
        $dueDate = $context['due_date'];
        $daysOverdue = $context['days_overdue'];

        $templates = [
            'friendly' => "Yth. Tim Keuangan {$clientName},\n\nSemoga email ini menemui Anda dalam keadaan baik. Kami ingin mengingatkan bahwa invoice #{$number} senilai Rp {$balance} dari total Rp {$amount} telah jatuh tempo pada {$dueDate} ({$daysOverdue} hari yang lalu).\n\nKemungkinan ini hanya keterlambatan administrasi dan kami memahami kesibukan Anda. Mohon untuk melakukan pembayaran melalui transfer bank ke rekening kami yang tertera di invoice. Jika Anda sudah melakukan pembayaran, mohon abaikan pesan ini dan kirimkan bukti transfer untuk kami verifikasi.\n\nJika ada pertanyaan atau kendala terkait pembayaran, jangan ragu untuk menghubungi kami. Kami siap membantu.\n\nTerima kasih atas perhatian dan kerjasamanya.",

            'firm' => "Yth. Tim Keuangan {$clientName},\n\nKami menindaklanjuti invoice #{$number} senilai Rp {$balance} yang jatuh tempo pada {$due_date} dan hingga saat ini belum kami terima pembayarannya. Keterlambatan sudah mencapai {$daysOverdue} hari.\n\nKami mohon agar pembayaran segera diselesaikan dalam 3 hari kerja ke depan untuk menghindari biaya keterlambatan tambahan. Mohon infokan jika ada kendala atau dokumen yang diperlukan dari pihak kami.\n\nJika pembayaran sudah dilakukan, mohon kirimkan bukti transfer sebagai konfirmasi. Kami menghargai kerjasama Anda dan berharap dapat menyelesaikan hal ini dengan baik.\n\nTerima kasih.",

            'urgent' => "PERHATIAN - Yth. Tim Keuangan {$clientName},\n\nIni adalah pemberitahuan final terkait invoice #{$number} senilai Rp {$balance} yang sudah terlambat {$daysOverdue} hari sejak jatuh tempo {$due_date}.\n\nKami sangat menyesalkan belum adanya penyelesaian hingga saat ini meskipun beberapa pengingat sudah dikirimkan. Mohon lakukan pembayaran segera dalam 2 hari kerja. Jika tidak ada pembayaran atau konfirmasi, dengan sangat menyesal kami akan mengambil langkah selanjutnya termasuk penangguhan layanan.\n\nKami masih membuka kesempatan untuk berdiskusi dan mencari solusi terbaik. Silakan hubungi kami segera jika ada hal yang perlu dibicarakan.\n\nHormat kami,\nTim Keuangan",
        ];

        return $templates[$tone] ?? $templates['friendly'];
    }
}
