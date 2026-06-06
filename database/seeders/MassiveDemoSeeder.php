<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\CreditNote;
use App\Models\Currency;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\KbArticle;
use App\Models\KbArticleVote;
use App\Models\KbCategory;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Milestone;
use App\Models\NewsletterSubscriber;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Proposal;
use App\Models\ProviderCredential;
use App\Models\Setting;
use App\Models\SlaPolicy;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaxRate;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassiveDemoSeeder extends Seeder
{
    protected array $userIds = [];
    protected array $clientIds = [];
    protected array $contactIds = [];
    protected array $projectIds = [];
    protected array $taskIds = [];
    protected array $invoiceIds = [];
    protected array $itemIds = [];
    protected array $leadIds = [];
    protected array $ticketIds = [];
    protected array $currencyIds = [];
    protected array $taxRateIds = [];
    protected array $leadSourceIds = [];
    protected array $leadStatusIds = [];
    protected array $departmentIds = [];
    protected array $ticketPriorityIds = [];
    protected array $ticketStatusIds = [];
    protected array $expenseCategoryIds = [];
    protected array $milestoneIds = [];

    protected $faker;

    public function run(): void
    {
        $this->faker = fake('id_ID');

        DB::disableQueryLog();
        DB::transaction(function () {
            $this->loadReferences();
            $this->seedMasterData();
            $this->seedClientsAndContacts();
            $this->seedLeads();
            $this->seedProjects();
            $this->seedSales();
            $this->seedExpenses();
            $this->seedSupport();
            $this->seedMarketing();
            $this->seedSystem();
            $this->seedActivitiesAndNotes();
            $this->seedCustomFieldValues();
            $this->seedAuditLogs();
        });

        $this->command?->info('Done! 10,000+ demo records seeded across all modules.');
    }

    protected function loadReferences(): void
    {
        $this->userIds = User::pluck('id')->toArray();
        $this->currencyIds = Currency::pluck('id')->toArray();
        $this->taxRateIds = TaxRate::where('is_active', true)->pluck('id')->toArray();
        $this->leadSourceIds = LeadSource::pluck('id')->toArray();
        $this->leadStatusIds = LeadStatus::pluck('id')->toArray();
        $this->departmentIds = Department::pluck('id')->toArray();
        $this->ticketPriorityIds = TicketPriority::pluck('id')->toArray();
        $this->ticketStatusIds = TicketStatus::pluck('id')->toArray();

        if (empty($this->userIds) || empty($this->currencyIds)) {
            $this->command?->warn('Reference data missing. Run ReferenceDataSeeder first.');
        }
    }

    // ─── MASTER DATA ───────────────────────────────────────────

    protected function seedMasterData(): void
    {
        $this->command?->info('Seeding Master Data...');

        // Expense Categories (20)
        $catNames = ['Travel', 'Software', 'Office Supplies', 'Marketing', 'Subcontractor',
            'Training', 'Utilities', 'Rent', 'Insurance', 'Legal', 'Banking', 'Telecom',
            'Hardware', 'Cloud Services', 'Consulting', 'Recruitment', 'Events', 'Printing',
            'Logistics', 'Miscellaneous'];
        $ecats = [];
        foreach ($catNames as $i => $n) {
            $ecats[] = ['name' => $n, 'description' => "Kategori biaya $n", 'is_active' => true, 'created_at' => now(), 'updated_at' => now()];
        }
        ExpenseCategory::insert($ecats);
        $this->expenseCategoryIds = ExpenseCategory::pluck('id')->toArray();
        $this->command?->info('  20 Expense Categories');

        // Departments (5)
        $deptNames = ['Support', 'Sales', 'Engineering', 'Operations', 'Finance'];
        foreach ($deptNames as $n) {
            if (!Department::where('name', $n)->exists()) {
                Department::create(['name' => $n, 'is_active' => true]);
            }
        }
        $this->departmentIds = Department::pluck('id')->toArray();
        $this->command?->info('  5 Departments');

        // Items (150)
        $itemNames = [
            'Web Development', 'Mobile App Dev', 'UI/UX Design', 'Logo Design', 'Branding Package',
            'SEO Audit', 'SEO Monthly', 'Google Ads Management', 'Social Media Management',
            'Content Writing', 'Copywriting', 'Video Editing', 'Photo Editing', 'Illustration',
            'Server Setup', 'Server Maintenance', 'Cloud Migration', 'Database Optimization',
            'Security Audit', 'Penetration Testing', 'Code Review', 'API Development',
            'WordPress Theme', 'WordPress Plugin', 'Shopify Setup', 'Magento Development',
            'Laravel Development', 'Node.js Development', 'React Development', 'Vue.js Development',
            'Flutter App', 'React Native App', 'iOS App', 'Android App', 'PWA Development',
            'Domain Registration', 'SSL Certificate', 'Hosting Basic', 'Hosting Premium',
            'Email Hosting', 'CDN Setup', 'DNS Management', 'Backup Service',
            'IT Consulting', 'Digital Strategy', 'Market Research', 'Competitive Analysis',
            'Data Analytics', 'Dashboard Setup', 'CRM Implementation', 'ERP Implementation',
            'Email Marketing Setup', 'Email Newsletter', 'Landing Page', 'Sales Funnel',
            'A/B Testing', 'Conversion Optimization', 'Heatmap Analysis', 'User Testing',
            'Technical Writing', 'API Documentation', 'Training Video', 'Online Course',
            'Workshop Facilitation', 'Coaching Session', 'Mentoring Package', 'Staff Augmentation',
            'Project Management', 'QA Testing', 'Automation Script', 'Chatbot Development',
            'AI Integration', 'Machine Learning Model', 'Data Migration', 'System Integration',
            'Payment Gateway Setup', 'SMS Gateway Setup', 'WhatsApp Business API', 'Email Gateway Setup',
            'Maintenance Retainer', 'Emergency Support', 'Priority Support', 'SLA Premium',
            'Managed Services Basic', 'Managed Services Pro', 'Managed Services Enterprise',
            'Annual Maintenance', 'Quarterly Review', 'Monthly Reporting', 'Weekly Standup',
            'Custom Dashboard', 'Custom Report', 'Custom Integration', 'Custom Module',
            'Theme Customization', 'Plugin Development', 'Extension Development', 'Addon Development',
            'Performance Tuning', 'Load Testing', 'Stress Testing', 'Uptime Monitoring',
            'Disaster Recovery', 'Business Continuity', 'GDPR Compliance', 'ISO Readiness',
            'Network Setup', 'Firewall Config', 'VPN Setup', 'Remote Desktop Setup',
            'Cloud Backup', 'On-premise Backup', 'Hybrid Setup', 'Multi-cloud Strategy',
            'Microservices Arch', 'Monolith to Microservices', 'Legacy Modernization', 'Tech Debt Reduction',
            'Agile Coaching', 'Scrum Master', 'Sprint Planning', 'Retrospective Facilitation',
            'Product Roadmap', 'MVP Development', 'Prototype Design', 'Proof of Concept',
            'Feasibility Study', 'Technical Audit', 'Architecture Review', 'Code Quality Audit',
            'Accessibility Audit', 'Mobile Responsive', 'Cross-browser Test', 'Performance Audit',
            'White-label Solution', 'SaaS Development', 'Platform Development', 'Marketplace Setup',
            'Subscription Billing', 'Usage-based Billing', 'Invoice Automation', 'Payment Reconciliation',
            'Custom Workflow', 'Approval Workflow', 'Notification Engine', 'Alerting System',
            'Compliance Monitoring', 'Policy Engine', 'Role-based Access', 'Permission System',
            'Single Sign-On', 'OAuth Integration', 'SAML Integration', 'LDAP Integration',
        ];
        $items = [];
        foreach ($itemNames as $i => $n) {
            $items[] = [
                'name' => $n,
                'description' => $this->faker->sentence(6),
                'default_price' => $this->faker->randomFloat(2, 50, 25000),
                'default_tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'unit' => $this->faker->randomElement(['hour', 'unit', 'license', 'month', 'project']),
                'sku' => 'SKU-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Item::insert($items);
        $this->itemIds = Item::pluck('id')->toArray();
        $this->command?->info('  150 Items');

        // KB Categories (10)
        $kbCatNames = ['Getting Started', 'Billing', 'Projects', 'Clients', 'Support', 'Integrations',
            'Security', 'API', 'Mobile App', 'Troubleshooting'];
        $kbcats = [];
        foreach ($kbCatNames as $i => $n) {
            $kbcats[] = [
                'name' => $n,
                'slug' => Str::slug($n) . '-' . rand(1000, 9999),
                'description' => "Artikel seputar $n",
                'is_public' => true,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        KbCategory::insert($kbcats);
        $kbCatIds = KbCategory::pluck('id')->toArray();
        $this->command?->info('  10 KB Categories');

        // KB Articles (100)
        $articlePrefixes = ['Cara', 'Panduan', 'Tips', 'Mengenal', 'Memahami', 'Tutorial', 'Best Practice',
            'FAQ', 'Konfigurasi', 'Troubleshooting'];
        $articleSuffixes = ['untuk Pemula', 'Lengkap', 'Pro', 'Cepat', 'Otomatis', 'Manual', 'via API',
            'di CRM', 'dengan Mudah', 'Tanpa Coding'];
        $kbArticles = [];
        foreach (range(1, 100) as $i) {
            $title = $this->faker->randomElement($articlePrefixes) . ' ' .
                     ucfirst($this->faker->words(rand(2, 4), true)) . ' ' .
                     $this->faker->randomElement($articleSuffixes);
            $content = '<p>' . implode('</p><p>', $this->faker->paragraphs(rand(4, 8))) . '</p>';
            $kbArticles[] = [
                'category_id' => $this->faker->randomElement($kbCatIds),
                'title' => $title,
                'slug' => Str::slug($title) . '-' . $i,
                'excerpt' => $this->faker->sentence(15),
                'content' => $content,
                'is_published' => true,
                'view_count' => rand(0, 5000),
                'helpful_count' => rand(0, 200),
                'unhelpful_count' => rand(0, 50),
                'author_id' => $this->faker->randomElement($this->userIds),
                'published_at' => now()->subDays(rand(0, 365)),
                'created_at' => now()->subDays(rand(0, 365)),
                'updated_at' => now(),
            ];
        }
        KbArticle::insert($kbArticles);
        $kbArticleIds = KbArticle::pluck('id')->toArray();
        $this->command?->info('  100 KB Articles');

        // KB Article Votes (400) — fields: article_id, voter_ip, helpful, voted_at
        $kbVotes = [];
        foreach (range(1, 400) as $i) {
            $kbVotes[] = [
                'article_id' => $this->faker->randomElement($kbArticleIds),
                'voter_ip' => $this->faker->ipv4(),
                'helpful' => $this->faker->boolean(80),
                'voted_at' => now()->subDays(rand(0, 180)),
            ];
        }
        KbArticleVote::insert($kbVotes);
        $this->command?->info('  400 KB Article Votes');

        // SLA Policies (5)
        $slaData = [
            ['name' => 'Standard SLA', 'rules' => json_encode(['response_hours' => 24, 'resolve_hours' => 72]), 'is_active' => true],
            ['name' => 'Premium SLA', 'rules' => json_encode(['response_hours' => 8, 'resolve_hours' => 24]), 'is_active' => true],
            ['name' => 'Enterprise SLA', 'rules' => json_encode(['response_hours' => 4, 'resolve_hours' => 12]), 'is_active' => true],
            ['name' => 'Critical SLA', 'rules' => json_encode(['response_hours' => 1, 'resolve_hours' => 4]), 'is_active' => true],
            ['name' => 'Basic SLA', 'rules' => json_encode(['response_hours' => 48, 'resolve_hours' => 120]), 'is_active' => true],
        ];
        foreach ($slaData as $s) {
            if (!SlaPolicy::where('name', $s['name'])->exists()) {
                SlaPolicy::create($s);
            }
        }
        $this->command?->info('  5 SLA Policies');

        // Custom Fields (20)
        $cfEntities = ['client', 'lead', 'project', 'task', 'ticket', 'invoice', 'estimate'];
        $cfTypes = ['text', 'textarea', 'select', 'date', 'number', 'checkbox'];
        $cfs = [];
        foreach (range(1, 20) as $i) {
            $entity = $this->faker->randomElement($cfEntities);
            $cfs[] = [
                'entity' => $entity,
                'label' => "Custom Field $i - $entity",
                'field_key' => 'cf_' . Str::random(8),
                'type' => $this->faker->randomElement($cfTypes),
                'options' => json_encode($this->faker->randomElement([[], ['option_a' => 'Option A', 'option_b' => 'Option B']])),
                'is_required' => $this->faker->boolean(20),
                'is_visible_to_customer' => $this->faker->boolean(50),
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        CustomField::insert($cfs);
        $this->command?->info('  20 Custom Fields');
        // Custom Field Values seeded later after entities exist
    }

    // ─── CLIENTS & CONTACTS ────────────────────────────────────

    protected function seedClientsAndContacts(): void
    {
        $this->command?->info('Seeding Clients & Contacts...');

        $industries = ['agency', 'saas', 'retail', 'consulting', 'manufacturing', 'real-estate',
            'healthcare', 'education', 'finance', 'hospitality', 'logistics', 'e-commerce',
            'telecom', 'energy', 'government'];

        // Clients (150)
        $clients = [];
        foreach (range(1, 150) as $i) {
            $clients[] = [
                'company_name' => $this->faker->company(),
                'industry' => $this->faker->randomElement($industries),
                'website' => $this->faker->url(),
                'phone' => $this->faker->phoneNumber(),
                'billing_address' => $this->faker->streetAddress(),
                'billing_city' => $this->faker->city(),
                'billing_state' => $this->faker->state(),
                'billing_country' => 'ID',
                'billing_postal' => $this->faker->postcode(),
                'tax_id' => 'TAX-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'account_manager_id' => $this->faker->randomElement($this->userIds),
                'default_currency_id' => $this->faker->randomElement($this->currencyIds),
                'default_language' => 'id',
                'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
                'notes' => $this->faker->sentence(),
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];
        }
        Client::insert($clients);
        $this->clientIds = Client::pluck('id')->toArray();
        $this->command?->info('  150 Clients');

        // Contacts (600)
        $contacts = [];
        foreach (range(1, 600) as $i) {
            $contacts[] = [
                'client_id' => $this->faker->randomElement($this->clientIds),
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'email' => "contact{$i}@demo.local",
                'phone' => $this->faker->phoneNumber(),
                'position' => $this->faker->jobTitle(),
                'is_primary' => $i <= 150,
                'portal_access' => $this->faker->boolean(30),
                'password' => bcrypt('password'),
                'locale' => 'id',
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];
        }
        Contact::insert($contacts);
        $this->contactIds = Contact::pluck('id')->toArray();
        $this->command?->info('  600 Contacts');
    }

    // ─── LEADS ─────────────────────────────────────────────────

    protected function seedLeads(): void
    {
        $this->command?->info('Seeding Leads...');

        $leads = [];
        foreach (range(1, 400) as $i) {
            $statusId = $this->faker->randomElement($this->leadStatusIds);
            $leads[] = [
                'name' => $this->faker->name(),
                'company' => $this->faker->company(),
                'email' => "lead{$i}@demo.local",
                'phone' => $this->faker->phoneNumber(),
                'website' => $this->faker->url(),
                'city' => $this->faker->city(),
                'country' => 'ID',
                'estimated_value' => $this->faker->randomFloat(2, 500, 50000),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'lead_source_id' => $this->faker->randomElement($this->leadSourceIds),
                'lead_status_id' => $statusId,
                'assigned_to' => $this->faker->randomElement($this->userIds),
                'description' => $this->faker->paragraph(),
                'expected_close' => now()->addDays(rand(7, 90)),
                'last_activity_at' => now()->subDays(rand(0, 30)),
                'created_at' => now()->subDays(rand(1, 180)),
                'updated_at' => now(),
            ];
        }
        Lead::insert($leads);
        $this->leadIds = Lead::pluck('id')->toArray();
        $this->command?->info('  400 Leads');
    }

    // ─── PROJECTS ──────────────────────────────────────────────

    protected function seedProjects(): void
    {
        $this->command?->info('Seeding Projects...');

        // Projects (50)
        $projects = [];
        $projectStatuses = ['not_started', 'in_progress', 'completed', 'on_hold'];
        $billingMethods = ['fixed', 'hourly', 'milestone', 'non_billable'];
        foreach (range(1, 50) as $i) {
            $status = $this->faker->randomElement($projectStatuses);
            $startDate = now()->subDays(rand(0, 180));
            $projects[] = [
                'name' => ucfirst($this->faker->words(rand(2, 4), true)),
                'description' => $this->faker->paragraph(),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'project_manager_id' => $this->faker->randomElement($this->userIds),
                'start_date' => $startDate,
                'deadline' => (clone $startDate)->addDays(rand(30, 180)),
                'estimate_hours' => $this->faker->randomFloat(2, 10, 500),
                'billing_method' => $this->faker->randomElement($billingMethods),
                'fixed_price' => $this->faker->randomFloat(2, 1000, 50000),
                'hourly_rate' => $this->faker->randomFloat(2, 25, 200),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'status' => $status,
                'progress_pct' => $status === 'completed' ? 100 : $this->faker->numberBetween(0, 99),
                'is_visible_to_customer' => true,
                'created_at' => $startDate,
                'updated_at' => now(),
            ];
        }
        Project::insert($projects);
        $this->projectIds = Project::pluck('id')->toArray();
        $this->command?->info('  50 Projects');

        // Project Members (150) — no timestamps
        $pmembers = [];
        foreach ($this->projectIds as $pid) {
            $memberCount = rand(2, 4);
            $used = [];
            for ($j = 0; $j < $memberCount; $j++) {
                do {
                    $uid = $this->faker->randomElement($this->userIds);
                } while (in_array("{$pid}-{$uid}", $used));
                $used[] = "{$pid}-{$uid}";
                $pmembers[] = [
                    'project_id' => $pid,
                    'user_id' => $uid,
                    'role' => $this->faker->randomElement(['developer', 'designer', 'reviewer', 'manager']),
                    'added_at' => now()->subDays(rand(0, 60)),
                ];
            }
        }
        ProjectMember::insert($pmembers);
        $this->command?->info('  150 Project Members');

        // Milestones (200)
        $milestones = [];
        foreach ($this->projectIds as $pid) {
            for ($j = 1; $j <= 4; $j++) {
                $milestones[] = [
                    'project_id' => $pid,
                    'name' => "Milestone $j: " . ucfirst($this->faker->words(2, true)),
                    'description' => $this->faker->sentence(),
                    'due_date' => now()->addDays(rand(7, 120)),
                    'order' => $j,
                    'complete_pct' => $this->faker->numberBetween(0, 100),
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now(),
                ];
            }
        }
        Milestone::insert($milestones);
        $this->milestoneIds = Milestone::pluck('id')->toArray();
        $this->command?->info('  200 Milestones');

        // Tasks (400)
        $tasks = [];
        $taskStatuses = ['todo', 'in_progress', 'review', 'done'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        foreach ($this->projectIds as $pid) {
            $projectMilestones = Milestone::where('project_id', $pid)->pluck('id')->toArray();
            for ($j = 1; $j <= 8; $j++) {
                $status = $this->faker->randomElement($taskStatuses);
                $tasks[] = [
                    'project_id' => $pid,
                    'milestone_id' => $this->faker->randomElement($projectMilestones),
                    'title' => ucfirst($this->faker->words(rand(2, 5), true)),
                    'description' => $this->faker->paragraph(),
                    'priority' => $this->faker->randomElement($priorities),
                    'status' => $status,
                    'start_date' => now()->subDays(rand(0, 30)),
                    'due_date' => now()->addDays(rand(1, 60)),
                    'estimate_hours' => $this->faker->randomFloat(2, 1, 40),
                    'is_billable' => $this->faker->boolean(70),
                    'hourly_rate' => $this->faker->randomFloat(2, 25, 200),
                    'is_visible_to_customer' => $this->faker->boolean(30),
                    'order' => $j,
                    'completed_at' => $status === 'done' ? now() : null,
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now(),
                ];
            }
        }
        Task::insert($tasks);
        $this->taskIds = Task::pluck('id')->toArray();
        $this->command?->info('  400 Tasks');

        // Task Assignees (600)
        $tAssigns = [];
        foreach (array_slice($this->taskIds, 0, 300) as $tid) {
            $assigneeCount = rand(1, 2);
            $usedUids = [];
            for ($j = 0; $j < $assigneeCount; $j++) {
                do {
                    $uid = $this->faker->randomElement($this->userIds);
                } while (in_array($uid, $usedUids));
                $usedUids[] = $uid;
                $tAssigns[] = [
                    'task_id' => $tid,
                    'user_id' => $uid,
                    'assigned_at' => now()->subDays(rand(0, 60)),
                ];
            }
        }
        DB::table('task_assignees')->insert($tAssigns);
        $this->command?->info('  600 Task Assignees');

        // Task Checklist (1500) — fields: task_id, item, is_done, order, done_at (no timestamps)
        $tChecklist = [];
        foreach (array_slice($this->taskIds, 0, 300) as $tid) {
            $clCount = rand(2, 5);
            for ($j = 1; $j <= $clCount; $j++) {
                $isDone = $this->faker->boolean(60);
                $tChecklist[] = [
                    'task_id' => $tid,
                    'item' => 'Checklist ' . $j . ': ' . $this->faker->sentence(3),
                    'is_done' => $isDone,
                    'order' => $j,
                    'done_at' => $isDone ? now()->subDays(rand(0, 14)) : null,
                ];
            }
        }
        DB::table('task_checklist')->insert($tChecklist);
        $this->command?->info('  1500 Task Checklist Items');

        // Task Dependencies (200) — no timestamps, unique on (task_id, depends_on_task_id)
        $tDeps = [];
        $usedPairs = [];
        $taskCount = count($this->taskIds);
        $attempts = 0;
        $maxAttempts = 1000;
        while (count($tDeps) < 200 && $attempts < $maxAttempts) {
            $sourceIdx = rand(0, $taskCount - 1);
            $offset = rand(10, min(50, $taskCount - 1));
            $targetIdx = ($sourceIdx + $offset) % $taskCount;
            $pairKey = "{$this->taskIds[$sourceIdx]}-{$this->taskIds[$targetIdx]}";
            if (!in_array($pairKey, $usedPairs) && $this->taskIds[$sourceIdx] !== $this->taskIds[$targetIdx]) {
                $usedPairs[] = $pairKey;
                $tDeps[] = [
                    'task_id' => $this->taskIds[$sourceIdx],
                    'depends_on_task_id' => $this->taskIds[$targetIdx],
                    'type' => $this->faker->randomElement(['finish_to_start', 'start_to_start', 'finish_to_finish', 'start_to_finish']),
                ];
            }
            $attempts++;
        }
        DB::table('task_dependencies')->insert($tDeps);
        $this->command?->info('  200 Task Dependencies');

        // Discussions (200)
        $discussions = [];
        foreach (range(1, 200) as $i) {
            $discussions[] = [
                'project_id' => $this->faker->randomElement($this->projectIds),
                'subject' => ucfirst($this->faker->words(rand(3, 6), true)),
                'body' => $this->faker->paragraph(),
                'user_id' => $this->faker->randomElement($this->userIds),
                'is_visible_to_customer' => $this->faker->boolean(40),
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Discussion::insert($discussions);
        $discussionIds = Discussion::pluck('id')->toArray();
        $this->command?->info('  200 Discussions');

        // Discussion Replies (600) — fillable: discussion_id, body, user_id, contact_id
        $dReplies = [];
        foreach ($discussionIds as $did) {
            $replyCount = rand(1, 4);
            for ($j = 0; $j < $replyCount; $j++) {
                $dReplies[] = [
                    'discussion_id' => $did,
                    'body' => $this->faker->paragraph(),
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now(),
                ];
            }
        }
        DiscussionReply::insert($dReplies);
        $this->command?->info('  600 Discussion Replies');

        // Time Entries (800)
        $tEntries = [];
        foreach (range(1, 800) as $i) {
            $taskId = $this->faker->randomElement($this->taskIds);
            $task = Task::find($taskId);
            $start = now()->subHours(rand(1, 168));
            $minutes = rand(15, 480);
            $end = (clone $start)->addMinutes($minutes);
            $tEntries[] = [
                'task_id' => $taskId,
                'project_id' => $task?->project_id ?? $this->faker->randomElement($this->projectIds),
                'user_id' => $this->faker->randomElement($this->userIds),
                'start_at' => $start,
                'end_at' => $end,
                'minutes' => $minutes,
                'hourly_rate' => $this->faker->randomFloat(2, 25, 200),
                'is_billable' => $this->faker->boolean(80),
                'is_invoiced' => false,
                'note' => $this->faker->sentence(),
                'created_at' => $start,
                'updated_at' => $end,
            ];
        }
        TimeEntry::insert($tEntries);
        $this->command?->info('  800 Time Entries');
    }

    // ─── SALES ─────────────────────────────────────────────────

    protected function seedSales(): void
    {
        $this->command?->info('Seeding Sales...');

        // Estimates (150)
        $estimates = [];
        foreach (range(1, 150) as $i) {
            $subtotal = $this->faker->randomFloat(2, 100, 15000);
            $taxTotal = round($subtotal * 0.11, 2);
            $total = $subtotal + $taxTotal;
            $estimates[] = [
                'number' => 'EST-' . str_pad($i + 1000, 6, '0', STR_PAD_LEFT),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'estimate_date' => now()->subDays(rand(0, 90)),
                'expiry_date' => now()->addDays(rand(7, 30)),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => $taxTotal,
                'total' => $total,
                'status' => $this->faker->randomElement(['draft', 'sent', 'accepted', 'declined', 'expired']),
                'notes' => $this->faker->sentence(),
                'public_token' => Str::random(40),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Estimate::insert($estimates);
        $estimateIds = Estimate::pluck('id')->toArray();
        $this->command?->info('  150 Estimates');

        // Estimate Items (450) — no timestamps
        $estItems = [];
        foreach ($estimateIds as $eid) {
            $itemCount = rand(2, 4);
            for ($j = 1; $j <= $itemCount; $j++) {
                $qty = rand(1, 10);
                $price = $this->faker->randomFloat(2, 10, 5000);
                $estItems[] = [
                    'estimate_id' => $eid,
                    'item_id' => $this->faker->randomElement($this->itemIds),
                    'description' => $this->faker->sentence(),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                    'discount_pct' => $this->faker->randomFloat(4, 0, 20),
                    'line_total' => round($qty * $price, 2),
                    'order' => $j,
                ];
            }
        }
        EstimateItem::insert($estItems);
        $this->command?->info('  450 Estimate Items');

        // Proposals (100)
        $proposals = [];
        foreach (range(1, 100) as $i) {
            $status = $this->faker->randomElement(['draft', 'sent', 'accepted', 'declined']);
            $content = '<h2>' . $this->faker->sentence(4) . '</h2><p>' .
                       implode('</p><p>', $this->faker->paragraphs(rand(3, 6))) . '</p>';
            $proposals[] = [
                'number' => 'PROP-' . str_pad($i + 500, 6, '0', STR_PAD_LEFT),
                'subject' => 'Proposal: ' . ucfirst($this->faker->words(3, true)),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'lead_id' => $this->faker->boolean(30) ? $this->faker->randomElement($this->leadIds) : null,
                'content' => $content,
                'total' => $this->faker->randomFloat(2, 500, 50000),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'open_until' => now()->addDays(rand(7, 30)),
                'status' => $status,
                'is_template' => false,
                'public_token' => Str::random(40),
                'accepted_at' => $status === 'accepted' ? now()->subDays(rand(1, 30)) : null,
                'accepted_by_name' => $status === 'accepted' ? $this->faker->name() : null,
                'accepted_signature' => $status === 'accepted' ? $this->faker->sha256() : null,
                'accepted_ip' => $status === 'accepted' ? $this->faker->ipv4() : null,
                'declined_at' => $status === 'declined' ? now()->subDays(rand(1, 30)) : null,
                'decline_reason' => $status === 'declined' ? $this->faker->sentence() : null,
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Proposal::insert($proposals);
        $this->command?->info('  100 Proposals');

        // Contracts (80)
        $contracts = [];
        foreach (range(1, 80) as $i) {
            $startDate = now()->subDays(rand(0, 60));
            $contracts[] = [
                'number' => 'CON-' . str_pad($i + 300, 6, '0', STR_PAD_LEFT),
                'subject' => 'Contract: ' . ucfirst($this->faker->words(3, true)),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'content' => '<p>' . implode('</p><p>', $this->faker->paragraphs(4)) . '</p>',
                'start_date' => $startDate,
                'end_date' => (clone $startDate)->addMonths(rand(3, 24)),
                'contract_value' => $this->faker->randomFloat(2, 1000, 100000),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'status' => $this->faker->randomElement(['draft', 'sent', 'signed', 'expired', 'terminated']),
                'is_template' => false,
                'public_token' => Str::random(40),
                'notify_expiry_days_before' => 30,
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => $startDate,
                'updated_at' => now(),
            ];
        }
        Contract::insert($contracts);
        $this->command?->info('  80 Contracts');

        // Invoices (300)
        $invoices = [];
        foreach (range(1, 300) as $i) {
            $subtotal = $this->faker->randomFloat(2, 100, 20000);
            $taxTotal = round($subtotal * 0.11, 2);
            $total = $subtotal + $taxTotal;
            $status = $this->faker->randomElement(['draft', 'sent', 'partial', 'paid', 'overdue']);
            $paidTotal = match($status) {
                'paid' => $total,
                'partial' => round($total * $this->faker->randomFloat(2, 0.2, 0.8), 2),
                default => 0,
            };
            $invoiceDate = now()->subDays(rand(0, 120));
            $invoices[] = [
                'number' => 'INV-' . str_pad($i + 2000, 6, '0', STR_PAD_LEFT),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'project_id' => $this->faker->boolean(40) ? $this->faker->randomElement($this->projectIds) : null,
                'invoice_date' => $invoiceDate,
                'due_date' => (clone $invoiceDate)->addDays(rand(14, 30)),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => $taxTotal,
                'total' => $total,
                'paid_total' => $paidTotal,
                'balance_due' => $total - $paidTotal,
                'status' => $status,
                'is_recurring' => $this->faker->boolean(10),
                'recurring_period' => $this->faker->boolean(10) ? $this->faker->randomElement(['monthly', 'quarterly', 'yearly']) : null,
                'recurring_count' => $this->faker->boolean(10) ? rand(3, 12) : null,
                'recurring_remaining' => $this->faker->boolean(10) ? rand(1, 12) : null,
                'next_recurring_date' => $this->faker->boolean(10) ? now()->addMonth() : null,
                'notes' => $this->faker->sentence(),
                'terms' => 'Pembayaran dalam 14 hari',
                'public_token' => Str::random(40),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => $invoiceDate,
                'updated_at' => now(),
            ];
        }
        Invoice::insert($invoices);
        $this->invoiceIds = Invoice::pluck('id')->toArray();
        $this->command?->info('  300 Invoices');

        // Invoice Items (900) — no timestamps
        $invItems = [];
        foreach ($this->invoiceIds as $iid) {
            $itemCount = rand(2, 4);
            for ($j = 1; $j <= $itemCount; $j++) {
                $qty = rand(1, 10);
                $price = $this->faker->randomFloat(2, 10, 5000);
                $invItems[] = [
                    'invoice_id' => $iid,
                    'item_id' => $this->faker->randomElement($this->itemIds),
                    'description' => $this->faker->sentence(),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                    'discount_pct' => $this->faker->randomFloat(4, 0, 15),
                    'line_total' => round($qty * $price, 2),
                    'order' => $j,
                ];
            }
        }
        InvoiceItem::insert($invItems);
        $this->command?->info('  900 Invoice Items');

        // Payments (250)
        $payments = [];
        foreach (range(1, 250) as $i) {
            $payments[] = [
                'invoice_id' => $this->faker->randomElement($this->invoiceIds),
                'amount' => $this->faker->randomFloat(2, 100, 10000),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'paid_at' => now()->subDays(rand(0, 90)),
                'method' => $this->faker->randomElement(['bank_transfer', 'cash', 'card', 'qris', 'check']),
                'transaction_id' => 'TXN-' . Str::upper(Str::random(12)),
                'status' => 'completed',
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Payment::insert($payments);
        $this->command?->info('  250 Payments');

        // Credit Notes (50)
        $cnotes = [];
        foreach (range(1, 50) as $i) {
            $total = $this->faker->randomFloat(2, 50, 5000);
            $applied = round($total * $this->faker->randomFloat(2, 0.3, 1), 2);
            $cnotes[] = [
                'number' => 'CN-' . str_pad($i + 100, 5, '0', STR_PAD_LEFT),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'issue_date' => now()->subDays(rand(0, 60)),
                'total' => $total,
                'applied_total' => $applied,
                'refunded_total' => $this->faker->randomFloat(2, 0, $total - $applied),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'status' => $this->faker->randomElement(['draft', 'issued', 'applied', 'refunded', 'closed']),
                'reason' => $this->faker->sentence(),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now(),
            ];
        }
        CreditNote::insert($cnotes);
        $creditNoteIds = CreditNote::pluck('id')->toArray();
        $this->command?->info('  50 Credit Notes');

        // Credit Note Invoices (80)
        $cnInvs = [];
        foreach (range(1, 80) as $i) {
            $cnInvs[] = [
                'credit_note_id' => $this->faker->randomElement($creditNoteIds),
                'invoice_id' => $this->faker->randomElement($this->invoiceIds),
                'amount_applied' => $this->faker->randomFloat(2, 50, 2000),
                'applied_at' => now()->subDays(rand(0, 30)),
            ];
        }
        DB::table('credit_note_invoices')->insert($cnInvs);
        $this->command?->info('  80 Credit Note Invoices');
    }

    // ─── EXPENSES ──────────────────────────────────────────────

    protected function seedExpenses(): void
    {
        $this->command?->info('Seeding Expenses...');

        $expenses = [];
        foreach (range(1, 400) as $i) {
            $expenses[] = [
                'expense_category_id' => $this->faker->randomElement($this->expenseCategoryIds),
                'client_id' => $this->faker->boolean(50) ? $this->faker->randomElement($this->clientIds) : null,
                'project_id' => $this->faker->boolean(30) ? $this->faker->randomElement($this->projectIds) : null,
                'vendor' => $this->faker->company(),
                'description' => $this->faker->sentence(),
                'amount' => $this->faker->randomFloat(2, 10, 5000),
                'currency_id' => $this->faker->randomElement($this->currencyIds),
                'tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                'expense_date' => now()->subDays(rand(0, 180)),
                'is_billable' => $this->faker->boolean(40),
                'is_invoiced' => false,
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 180)),
                'updated_at' => now(),
            ];
        }
        Expense::insert($expenses);
        $this->command?->info('  400 Expenses');
    }

    // ─── SUPPORT ───────────────────────────────────────────────

    protected function seedSupport(): void
    {
        $this->command?->info('Seeding Support...');

        // Tickets (200)
        $tickets = [];
        $slaPolicyIds = SlaPolicy::pluck('id')->toArray();
        foreach (range(1, 200) as $i) {
            $tickets[] = [
                'number' => 'T-' . str_pad($i + 1000, 5, '0', STR_PAD_LEFT),
                'subject' => ucfirst($this->faker->words(rand(3, 6), true)),
                'body' => $this->faker->paragraph(),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'contact_id' => $this->faker->boolean(40) ? $this->faker->randomElement($this->contactIds) : null,
                'department_id' => $this->faker->randomElement($this->departmentIds),
                'priority_id' => $this->faker->randomElement($this->ticketPriorityIds),
                'status_id' => $this->faker->randomElement($this->ticketStatusIds),
                'sla_policy_id' => $this->faker->randomElement([null, ...$slaPolicyIds]),
                'assigned_to' => $this->faker->randomElement($this->userIds),
                'related_project_id' => $this->faker->boolean(20) ? $this->faker->randomElement($this->projectIds) : null,
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Ticket::insert($tickets);
        $this->ticketIds = Ticket::pluck('id')->toArray();
        $this->command?->info('  200 Tickets');

        // Ticket Replies (600) — fillable: ticket_id, body, user_id, contact_id, email_from, is_internal, source, email_message_id
        $tReplies = [];
        foreach ($this->ticketIds as $tid) {
            $replyCount = rand(1, 4);
            for ($j = 0; $j < $replyCount; $j++) {
                $tReplies[] = [
                    'ticket_id' => $tid,
                    'body' => $this->faker->paragraph(),
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'is_internal' => $this->faker->boolean(20),
                    'source' => $this->faker->randomElement(['web', 'email', 'api']),
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now(),
                ];
            }
        }
        TicketReply::insert($tReplies);
        $this->command?->info('  600 Ticket Replies');

        // Ticket Attachments: skipped — requires real file records in files table
        $this->command?->info('  0 Ticket Attachments (skipped - requires file records)');
    }

    // ─── MARKETING ─────────────────────────────────────────────

    protected function seedMarketing(): void
    {
        $this->command?->info('Seeding Marketing...');

        // Announcements (30)
        $announcements = [];
        foreach (range(1, 30) as $i) {
            $announcements[] = [
                'title' => 'Pengumuman: ' . ucfirst($this->faker->words(3, true)),
                'body' => $this->faker->paragraphs(2, true),
                'audience' => $this->faker->randomElement(['all', 'staff', 'customers']),
                'author_id' => $this->faker->randomElement($this->userIds),
                'publish_at' => now()->subDays(rand(0, 30)),
                'expires_at' => now()->addDays(rand(7, 90)),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ];
        }
        Announcement::insert($announcements);
        $this->command?->info('  30 Announcements');

        // Surveys (20)
        $surveys = [];
        $surveyTokens = [];
        foreach (range(1, 20) as $i) {
            $token = Str::random(32);
            $surveyTokens[] = $token;
            $surveys[] = [
                'title' => 'Survey: ' . ucfirst($this->faker->words(3, true)),
                'description' => $this->faker->sentence(),
                'audience' => $this->faker->randomElement(['all_clients', 'recent_clients', 'all']),
                'public_token' => $token,
                'is_active' => true,
                'starts_at' => now()->subDays(rand(0, 30)),
                'ends_at' => now()->addDays(rand(7, 60)),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ];
        }
        Survey::insert($surveys);
        $surveyIds = Survey::pluck('id')->toArray();
        $this->command?->info('  20 Surveys');

        // Survey Questions (100) — no timestamps
        $sQuestions = [];
        foreach ($surveyIds as $sid) {
            for ($j = 1; $j <= 5; $j++) {
                $sQuestions[] = [
                    'survey_id' => $sid,
                    'question' => 'Pertanyaan ' . $j . ': ' . $this->faker->sentence(4) . '?',
                    'type' => $this->faker->randomElement(['text', 'rating', 'multiple_choice', 'yes_no']),
                    'options' => json_encode($this->faker->randomElement([[], ['A', 'B', 'C', 'D']])),
                    'is_required' => $this->faker->boolean(70),
                    'order' => $j,
                ];
            }
        }
        SurveyQuestion::insert($sQuestions);
        $surveyQuestionIds = SurveyQuestion::pluck('id')->toArray();
        $this->command?->info('  100 Survey Questions');

        // Survey Responses (150) — no timestamps, fillable: survey_id, contact_id, anonymous_token, ip_address, submitted_at
        $sResponses = [];
        foreach (range(1, 150) as $i) {
            $sResponses[] = [
                'survey_id' => $this->faker->randomElement($surveyIds),
                'contact_id' => $this->faker->boolean(50) ? $this->faker->randomElement($this->contactIds) : null,
                'anonymous_token' => Str::random(16),
                'ip_address' => $this->faker->ipv4(),
                'submitted_at' => now()->subDays(rand(0, 30)),
            ];
        }
        SurveyResponse::insert($sResponses);
        $surveyResponseIds = SurveyResponse::pluck('id')->toArray();
        $this->command?->info('  150 Survey Responses');

        // Survey Answers (450) — no timestamps, fillable: response_id, question_id, answer
        $sAnswers = [];
        foreach ($surveyResponseIds as $srid) {
            $answerCount = rand(3, 5);
            $questions = $this->faker->randomElements($surveyQuestionIds, $answerCount);
            foreach ($questions as $qid) {
                $sAnswers[] = [
                    'response_id' => $srid,
                    'question_id' => $qid,
                    'answer' => $this->faker->sentence(3),
                ];
            }
        }
        SurveyAnswer::insert($sAnswers);
        $this->command?->info('  450 Survey Answers');

        // Newsletter Subscribers (100)
        $nls = [];
        foreach (range(1, 100) as $i) {
            $nls[] = [
                'email' => "subscriber{$i}@demo.local",
                'name' => $this->faker->name(),
                'source' => $this->faker->randomElement(['website', 'landing_page', 'referral', 'event']),
                'is_active' => $this->faker->boolean(80),
                'confirmed_at' => now()->subDays(rand(1, 90)),
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now(),
            ];
        }
        NewsletterSubscriber::insert($nls);
        $this->command?->info('  100 Newsletter Subscribers');
    }

    // ─── SYSTEM ────────────────────────────────────────────────

    protected function seedSystem(): void
    {
        $this->command?->info('Seeding System...');

        // Goals (50)
        $goals = [];
        foreach (range(1, 50) as $i) {
            $target = $this->faker->randomFloat(2, 1000, 100000);
            $goals[] = [
                'user_id' => $this->faker->randomElement($this->userIds),
                'name' => 'Goal: ' . ucfirst($this->faker->words(3, true)),
                'description' => $this->faker->sentence(),
                'metric' => $this->faker->randomElement(['revenue', 'leads', 'projects', 'tickets_resolved', 'hours_logged']),
                'target' => $target,
                'current' => round($target * $this->faker->randomFloat(2, 0.1, 0.9), 2),
                'start_date' => now()->subDays(rand(0, 60)),
                'end_date' => now()->addDays(rand(30, 180)),
                'status' => $this->faker->randomElement(['not_started', 'in_progress', 'completed']),
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now(),
            ];
        }
        Goal::insert($goals);
        $this->command?->info('  50 Goals');

        // Calendar Events (200)
        $events = [];
        foreach (range(1, 200) as $i) {
            $allDay = $this->faker->boolean(20);
            $start = now()->subDays(rand(0, 30))->addHours(rand(0, 23));
            $events[] = [
                'user_id' => $this->faker->randomElement($this->userIds),
                'title' => ucfirst($this->faker->words(rand(2, 4), true)),
                'description' => $this->faker->boolean(60) ? $this->faker->sentence() : null,
                'starts_at' => $start,
                'ends_at' => $allDay ? (clone $start)->addDay() : (clone $start)->addHours(rand(1, 4)),
                'all_day' => $allDay,
                'color' => $this->faker->randomElement(['#3b82f6', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#eab308']),
                'reminder_minutes_before' => $this->faker->randomElement([10, 15, 30, 60, null]),
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now(),
            ];
        }
        CalendarEvent::insert($events);
        $eventIds = CalendarEvent::pluck('id')->toArray();
        $this->command?->info('  200 Calendar Events');

        // Calendar Event Invitees (400)
        $invitees = [];
        foreach (array_slice($eventIds, 0, 100) as $eid) {
            $inviteeCount = rand(2, 5);
            $usedUids = [];
            for ($j = 0; $j < $inviteeCount; $j++) {
                do {
                    $uid = $this->faker->randomElement($this->userIds);
                } while (in_array($uid, $usedUids));
                $usedUids[] = $uid;
                $invitees[] = [
                    'event_id' => $eid,
                    'user_id' => $uid,
                    'response' => $this->faker->randomElement(['accepted', 'declined', 'tentative', 'pending']),
                ];
            }
        }
        DB::table('calendar_event_invitees')->insert($invitees);
        $this->command?->info('  400 Calendar Event Invitees');

        // Provider Credentials (10) — no timestamps, fillable: provider_id, key, value_encrypted, is_secret
        $providerIds = \App\Models\Provider::pluck('id')->toArray();
        $pcreds = [];
        foreach ($providerIds as $pid) {
            $provider = \App\Models\Provider::find($pid);
            if ($provider && $provider->type !== 'storage') {
                $pcreds[] = [
                    'provider_id' => $pid,
                    'key' => 'api_key',
                    'value_encrypted' => encrypt('test-' . Str::random(16)),
                    'is_secret' => true,
                ];
            }
        }
        if (!empty($pcreds)) {
            ProviderCredential::insert($pcreds);
        }
        $this->command?->info('  ' . count($pcreds) . ' Provider Credentials');

        // Webhooks (15)
        $webhooks = [];
        $webhookEvents = ['invoice.paid', 'invoice.overdue', 'ticket.created', 'ticket.resolved',
            'lead.created', 'project.completed', 'payment.received', 'estimate.accepted',
            'contract.signed', 'task.completed'];
        foreach (range(1, 15) as $i) {
            $webhooks[] = [
                'event' => $this->faker->randomElement($webhookEvents),
                'url' => 'https://webhook.demo.local/' . Str::random(8),
                'secret' => Str::random(32),
                'is_active' => $this->faker->boolean(80),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Webhook::insert($webhooks);
        $webhookIds = Webhook::pluck('id')->toArray();
        $this->command?->info('  15 Webhooks');

        // Webhook Deliveries (200) — no timestamps, fillable: webhook_id, payload, status_code, response_body, attempt, delivered_at, next_retry_at
        $wDeliveries = [];
        foreach (range(1, 200) as $i) {
            $now = now()->subDays(rand(0, 30));
            $wDeliveries[] = [
                'webhook_id' => $this->faker->randomElement($webhookIds),
                'payload' => json_encode(['event' => 'test', 'data' => ['id' => rand(1, 1000)]]),
                'status_code' => $this->faker->randomElement([200, 200, 200, 201, 400, 500]),
                'response_body' => '{"status":"ok"}',
                'attempt' => 1,
                'delivered_at' => $now,
                'next_retry_at' => null,
            ];
        }
        WebhookDelivery::insert($wDeliveries);
        $this->command?->info('  200 Webhook Deliveries');
    }

    // ─── ACTIVITIES & NOTES ────────────────────────────────────

    protected function seedActivitiesAndNotes(): void
    {
        $this->command?->info('Seeding Activities & Notes...');

        $subjectTypes = [
            'App\Models\Client' => $this->clientIds,
            'App\Models\Lead' => $this->leadIds,
            'App\Models\Project' => $this->projectIds,
            'App\Models\Task' => $this->taskIds,
            'App\Models\Ticket' => $this->ticketIds,
            'App\Models\Invoice' => $this->invoiceIds,
        ];

        // Activities (800)
        $activities = [];
        $activityTypes = ['created', 'updated', 'status_changed', 'commented', 'assigned',
            'sent', 'viewed', 'accepted', 'declined', 'paid', 'converted'];
        foreach (range(1, 800) as $i) {
            $typeKey = array_rand($subjectTypes);
            $activities[] = [
                'subject_type' => $typeKey,
                'subject_id' => $this->faker->randomElement($subjectTypes[$typeKey]),
                'type' => $this->faker->randomElement($activityTypes),
                'subject' => $this->faker->sentence(4),
                'description' => $this->faker->sentence(),
                'user_id' => $this->faker->randomElement($this->userIds),
                'occurred_at' => now()->subDays(rand(0, 90)),
                'duration_minutes' => $this->faker->boolean(20) ? rand(5, 120) : null,
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Activity::insert($activities);
        $this->command?->info('  800 Activities');

        // Notes (500)
        $notes = [];
        $notableTypes = [
            'App\Models\Client' => $this->clientIds,
            'App\Models\Lead' => $this->leadIds,
            'App\Models\Project' => $this->projectIds,
            'App\Models\Task' => $this->taskIds,
        ];
        foreach (range(1, 500) as $i) {
            $typeKey = array_rand($notableTypes);
            $notes[] = [
                'notable_type' => $typeKey,
                'notable_id' => $this->faker->randomElement($notableTypes[$typeKey]),
                'body' => $this->faker->paragraph(),
                'user_id' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 90)),
                'updated_at' => now(),
            ];
        }
        Note::insert($notes);
        $this->command?->info('  500 Notes');
    }

    // ─── CUSTOM FIELD VALUES ──────────────────────────────────

    protected function seedCustomFieldValues(): void
    {
        $this->command?->info('Seeding Custom Field Values...');

        $cfIds = CustomField::pluck('id')->toArray();
        if (empty($cfIds)) return;

        $cfSubjects = [
            ['App\Models\Client', $this->clientIds],
            ['App\Models\Project', $this->projectIds],
            ['App\Models\Task', $this->taskIds],
            ['App\Models\Lead', $this->leadIds],
        ];
        $cfv = [];
        $usedCombos = [];
        $attempts = 0;
        $maxAttempts = 1000;
        while (count($cfv) < 200 && $attempts < $maxAttempts) {
            $subj = $this->faker->randomElement($cfSubjects);
            if (!empty($subj[1])) {
                $cfId = $this->faker->randomElement($cfIds);
                $sId = $this->faker->randomElement($subj[1]);
                $combo = "{$cfId}-{$subj[0]}-{$sId}";
                if (!in_array($combo, $usedCombos)) {
                    $usedCombos[] = $combo;
                    $cfv[] = [
                        'custom_field_id' => $cfId,
                        'subject_type' => $subj[0],
                        'subject_id' => $sId,
                        'value' => $this->faker->sentence(2),
                    ];
                }
            }
            $attempts++;
        }
        if (!empty($cfv)) {
            CustomFieldValue::insert($cfv);
        }
        $this->command?->info('  ' . count($cfv) . ' Custom Field Values');
    }

    // ─── AUDIT LOGS ────────────────────────────────────────────

    protected function seedAuditLogs(): void
    {
        $this->command?->info('Seeding Audit Logs...');

        $audits = [];
        $auditActions = ['created', 'updated', 'deleted', 'login', 'logout', 'export', 'view'];
        $auditableTypes = [
            'App\Models\Client' => $this->clientIds,
            'App\Models\Invoice' => $this->invoiceIds,
            'App\Models\Project' => $this->projectIds,
            'App\Models\User' => $this->userIds,
            'App\Models\Task' => $this->taskIds,
            'App\Models\Ticket' => $this->ticketIds,
        ];
        foreach (range(1, 300) as $i) {
            $typeKey = array_rand($auditableTypes);
            $audits[] = [
                'user_id' => $this->faker->randomElement($this->userIds),
                'action' => $this->faker->randomElement($auditActions),
                'subject_type' => $typeKey,
                'subject_id' => $this->faker->randomElement($auditableTypes[$typeKey]),
                'before' => json_encode(['status' => 'old_value']),
                'after' => json_encode(['status' => 'new_value']),
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'created_at' => now()->subDays(rand(0, 90)),
            ];
        }
        AuditLog::insert($audits);
        $this->command?->info('  300 Audit Logs');
    }
}
