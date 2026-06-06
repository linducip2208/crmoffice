<?php

namespace App\Http\Controllers;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Invoice;
use App\Models\KbArticle;
use App\Models\Lead;
use App\Models\Ticket;
use App\Services\AiInsightService;
use App\Services\AiLeadScoringService;
use App\Services\AiReminderMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    private function checkLlmConfigured(): ?\App\Adapters\Llm\LlmAdapterContract
    {
        $llm = LlmAdapterFactory::active();
        if (! $llm) {
            abort(400, 'No active LLM provider configured. Set one up in Settings → Providers (type=llm).');
        }

        return $llm;
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|string|max:500',
        ]);

        $message = $request->input('message');
        $user = $request->user();

        $insightService = app(AiInsightService::class);
        $insight = $insightService->query($message, $user);

        if ($insight['data'] !== null && ! empty($insight['data'])) {
            return response()->json([
                'answer' => $insight['answer'],
                'chart_data' => [
                    'labels' => $insight['data']['labels'] ?? [],
                    'values' => $insight['data']['values'] ?? [],
                    'type' => $insight['chart_type'] ?? 'bar',
                    'label' => null,
                ],
                'source' => 'insight',
            ]);
        }

        if ($insight['answer'] && $insight['chart_type'] === null) {
            return response()->json([
                'answer' => $insight['answer'],
                'chart_data' => null,
                'source' => 'insight',
            ]);
        }

        $llm = LlmAdapterFactory::active();
        if (! $llm) {
            return response()->json([
                'answer' => 'AI tidak tersedia. Silakan konfigurasi LLM provider di Settings → Providers (type=llm).',
                'chart_data' => null,
                'source' => 'fallback',
            ]);
        }

        try {
            $context = $request->input('context', '/admin');
            $systemPrompt = $this->chatSystemPrompt($user, $context);
            $response = $llm->chat([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message],
            ], [
                'temperature' => 0.6,
                'max_tokens' => 800,
            ]);

            return response()->json([
                'answer' => $response->content ?? 'Maaf, tidak dapat merespon saat ini.',
                'chart_data' => null,
                'source' => 'llm',
            ]);
        } catch (\Throwable $e) {
            Log::error('AI chat failed', ['error' => $e->getMessage(), 'user' => $user->id]);

            return response()->json([
                'answer' => 'Maaf, terjadi kesalahan. Coba lagi nanti.',
                'chart_data' => null,
                'source' => 'error',
            ], 500);
        }
    }

    public function insight(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:2000',
        ]);

        $question = $request->input('question');
        $user = $request->user();

        $service = app(AiInsightService::class);
        $result = $service->query($question, $user);

        return response()->json($result);
    }

    public function summarizeTicket(Ticket $ticket): JsonResponse
    {
        $llm = $this->checkLlmConfigured();

        $ticket->load('replies.user', 'replies.contact', 'client', 'priority', 'status', 'department');

        $conversation = $this->buildConversationText($ticket);

        $messages = [
            ['role' => 'system', 'content' => 'You are a support ticket summarizer. Write a clear, concise summary in Indonesian. Format with bullet points for key issues, actions taken, and current status. Max 300 words.'],
            ['role' => 'user', 'content' => "Summarize this support ticket conversation:\n\nSubject: {$ticket->subject}\nDepartment: {$ticket->department?->name}\nPriority: {$ticket->priority?->name}\nStatus: {$ticket->status?->name}\n\nConversation:\n{$conversation}"],
        ];

        try {
            $response = $llm->chat($messages, [
                'temperature' => 0.3,
                'max_tokens' => 600,
            ]);

            return response()->json(['summary' => $response->content]);
        } catch (\Throwable $e) {
            Log::error('AI summarizeTicket failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Gagal menghasilkan ringkasan. Coba lagi.'], 500);
        }
    }

    public function suggestKb(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

        $articles = KbArticle::where('is_published', true)
            ->with('category')
            ->select('id', 'category_id', 'title', 'excerpt', 'slug')
            ->get();

        $llm = LlmAdapterFactory::active();

        $suggestions = [];

        if ($llm) {
            try {
                $articleList = $articles->map(fn ($a, $i) => "[{$i}] {$a->title}: {$a->excerpt}")->implode("\n");

                $messages = [
                    ['role' => 'system', 'content' => 'You are a KB matcher. Given a support ticket and a list of KB articles, return a JSON array of the 3 most relevant article indices: {"indices": [0, 2, 5]}. Only return indices of articles that are genuinely relevant — return empty array if nothing matches.'],
                    ['role' => 'user', 'content' => "Ticket Subject: {$request->input('subject')}\n\nTicket Body: {$request->input('body')}\n\nKB Articles:\n{$articleList}"],
                ];

                $response = $llm->chat($messages, [
                    'temperature' => 0.2,
                    'max_tokens' => 100,
                ]);

                $json = json_decode($response->content, true);
                if ($json && isset($json['indices'])) {
                    $indices = array_slice(array_map('intval', $json['indices']), 0, 3);
                    $suggestions = $articles->only($indices)->values()->toArray();
                }
            } catch (\Throwable $e) {
                Log::warning('AI suggestKb failed, falling back to local search', ['error' => $e->getMessage()]);
            }
        }

        if (empty($suggestions)) {
            $query = $request->input('subject') . ' ' . $request->input('body');
            $keywords = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($query))));
            $scored = $articles->map(function ($a) use ($keywords) {
                $text = strtolower($a->title . ' ' . $a->excerpt);
                $score = 0;
                foreach ($keywords as $kw) {
                    if (strlen($kw) > 2 && str_contains($text, $kw)) {
                        $score++;
                    }
                }

                return ['article' => $a, 'score' => $score];
            })->filter(fn ($s) => $s['score'] > 0)
              ->sortByDesc('score')
              ->take(3)
              ->map(fn ($s) => $s['article']->toArray())
              ->values()
              ->toArray();

            $suggestions = $scored;
        }

        return response()->json(['articles' => $suggestions]);
    }

    public function draftReply(Ticket $ticket): JsonResponse
    {
        $llm = $this->checkLlmConfigured();

        $ticket->load('replies.user', 'replies.contact', 'client', 'contact');

        $conversation = $this->buildConversationText($ticket);

        $messages = [
            ['role' => 'system', 'content' => "You are a professional customer support agent. Draft a helpful, empathetic reply in Indonesian. The reply should:
- Greet the customer by name if available
- Acknowledge their issue specifically
- Provide clear next steps or resolution
- Maintain a professional yet warm tone
- Include a closing with the agent's willingness to help further

Return ONLY the reply body text. No markdown, no HTML, no prefix."],
            ['role' => 'user', 'content' => "Draft a reply for this support ticket:\n\nSubject: {$ticket->subject}\nCustomer: {$ticket->client?->company_name}\nContact: {$ticket->contact?->full_name}\n\nConversation:\n{$conversation}"],
        ];

        try {
            $response = $llm->chat($messages, [
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            return response()->json(['draft' => $response->content]);
        } catch (\Throwable $e) {
            Log::error('AI draftReply failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Gagal membuat draft balasan. Coba lagi.'], 500);
        }
    }

    public function scoreLead(Lead $lead): JsonResponse
    {
        $service = app(AiLeadScoringService::class);
        $result = $service->scoreLead($lead);

        return response()->json($result);
    }

    public function generateInvoiceReminder(Invoice $invoice, Request $request): JsonResponse
    {
        $tone = $request->input('tone', 'friendly');

        if (! in_array($tone, ['friendly', 'firm', 'urgent'])) {
            $tone = 'friendly';
        }

        $service = app(AiReminderMessageService::class);
        $message = $service->generateReminder($invoice, $tone);

        return response()->json([
            'message' => $message,
            'tone' => $tone,
            'invoice_number' => $invoice->number,
            'client' => $invoice->client?->company_name,
        ]);
    }

    private function chatSystemPrompt($user, string $context): string
    {
        $leadsTotal = \App\Models\Lead::count();
        $leadsWon = \App\Models\Lead::whereHas('status', fn ($q) => $q->where('is_won', true))->count();
        $invoicesOverdue = \App\Models\Invoice::where('due_date', '<', now())->where('balance_due', '>', 0)->whereNotIn('status', ['paid', 'cancelled', 'void'])->count();
        $tasksOverdue = \App\Models\Task::where('due_date', '<', now())->whereNotIn('status', ['done', 'completed', 'cancelled'])->count();
        $ticketsOpen = \App\Models\Ticket::whereHas('status', fn ($q) => $q->whereNotIn('name', ['Resolved', 'Closed', 'Cancelled']))->count();
        $projectsActive = \App\Models\Project::whereNotIn('status', ['completed', 'cancelled'])->count();

        $myTasks = \App\Models\Task::whereHas('assignees', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->count();

        return "Kamu adalah Asisten AI untuk aplikasi CRM bernama crmoffice. "
            . "Kamu membantu pengguna menjawab pertanyaan bisnis, memberikan insight, dan membantu navigasi. "
            . "Jawab dalam Bahasa Indonesia yang ramah, ringkas, dan informatif. "
            . "Gunakan **bold** untuk penekanan. "
            . "Jika ditanya data yang tidak kamu tahu, sarankan untuk membuka halaman terkait di admin panel.\n\n"
            . "Data CRM saat ini:\n"
            . "- User: {$user->name} (role: " . ($user->roles->first()?->name ?? 'staff') . ")\n"
            . "- Halaman saat ini: {$context}\n"
            . "- Total leads: {$leadsTotal} (won: {$leadsWon})\n"
            . "- Invoice overdue: {$invoicesOverdue}\n"
            . "- Task overdue: {$tasksOverdue}\n"
            . "- Ticket terbuka: {$ticketsOpen}\n"
            . "- Project aktif: {$projectsActive}\n"
            . "- Task saya pending: {$myTasks}";
    }

    public function meetingNotes(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:generate,save',
            'raw_text' => 'required|string|max:50000',
            'related_type' => 'required|string|in:lead,client,project',
            'related_id' => 'required|integer',
            'structured_notes' => 'nullable|array',
            'selected_action_indices' => 'nullable|array',
            'create_tasks' => 'nullable|boolean',
        ]);

        $service = app(\App\Services\AiMeetingNotesService::class);

        if ($request->input('action') === 'generate') {
            try {
                $result = $service->transcribeToNotes($request->input('raw_text'), [
                    'related_type' => $request->input('related_type'),
                    'related_id' => $request->input('related_id'),
                ]);

                return response()->json($result);
            } catch (\Throwable $e) {
                Log::error('AI meetingNotes generate failed', ['error' => $e->getMessage()]);

                return response()->json(['error' => 'Gagal generate catatan. Coba lagi.'], 500);
            }
        }

        if ($request->input('action') === 'save') {
            try {
                $structuredNotes = $request->input('structured_notes', []);
                $selectedIndices = $request->input('selected_action_indices', []);

                if (! empty($selectedIndices)) {
                    $filteredActionItems = [];
                    foreach ($selectedIndices as $idx) {
                        if (isset($structuredNotes['action_items'][$idx])) {
                            $filteredActionItems[] = $structuredNotes['action_items'][$idx];
                        }
                    }
                    $structuredNotes['action_items'] = $filteredActionItems;
                }

                $activity = $service->createFromMeetingNotes(
                    rawText: $request->input('raw_text'),
                    relatedType: $request->input('related_type'),
                    relatedId: (int) $request->input('related_id'),
                    structuredNotes: $structuredNotes,
                    options: ['create_tasks' => (bool) $request->input('create_tasks')]
                );

                return response()->json([
                    'success' => true,
                    'activity_id' => $activity->id,
                    'notification' => [
                        'title' => 'Meeting recorded',
                        'body' => 'Catatan meeting telah disimpan.',
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::error('AI meetingNotes save failed', ['error' => $e->getMessage()]);

                return response()->json(['error' => 'Gagal menyimpan catatan.'], 500);
            }
        }

        return response()->json(['error' => 'Invalid action.'], 400);
    }

    private function buildConversationText(Ticket $ticket): string
    {
        $lines = [];

        $lines[] = "[OPENING] {$ticket->body}";

        foreach ($ticket->replies as $reply) {
            $author = $reply->user?->name ?? $reply->contact?->full_name ?? $reply->email_from ?? 'Unknown';
            $lines[] = "[{$author}] {$reply->body}";
        }

        return implode("\n\n", $lines);
    }
}
