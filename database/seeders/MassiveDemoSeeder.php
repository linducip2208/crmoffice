<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CalendarEvent;
use App\Models\CannedResponse;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\CreditNote;
use App\Models\Currency;
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
use App\Models\KbCategory;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Milestone;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Proposal;
use App\Models\SlaPolicy;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Task;
use App\Models\TaxRate;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassiveDemoSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create('id_ID');
    }
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
    protected array $kbCatIds = [];
    protected array $blogCatIds = [];

    const CHUNK = 500;

    protected $faker;

    public function run(): void
    {
        $this->faker = $this->faker;

        DB::disableQueryLog();

        $this->loadReferences();

        $this->seedIdempotent('Master Data & Items', fn() => $this->seedMasterData());
        $this->seedIdempotent('Clients & Contacts', fn() => $this->seedClientsAndContacts());
        $this->seedIdempotent('Leads', fn() => $this->seedLeads());
        $this->seedIdempotent('Projects, Tasks & Time', fn() => $this->seedProjects());
        $this->seedIdempotent('Sales (Estimates, Proposals, Contracts, Invoices, Payments, Credit Notes)', fn() => $this->seedSales());
        $this->seedIdempotent('Tickets & Support', fn() => $this->seedTickets());
        $this->seedIdempotent('Marketing (KB, Blog, Announcements, Surveys)', fn() => $this->seedMarketing());
        $this->seedIdempotent('Activities & Notes', fn() => $this->seedActivitiesAndNotes());
        $this->seedIdempotent('Calendar Events', fn() => $this->seedCalendarEvents());
        $this->seedIdempotent('Goals', fn() => $this->seedGoals());
        $this->seedIdempotent('Canned Responses', fn() => $this->seedCannedResponses());
        $this->seedIdempotent('Expenses', fn() => $this->seedExpenses());

        $this->command?->info('MassiveDemoSeeder: ~12.000+ records seeded across all modules.');
    }

    // ─── HELPERS ─────────────────────────────────────────────────

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

        if (empty($this->userIds)) {
            $this->command?->warn('No users found. Run OwnerUserSeeder and DemoUsersSeeder first.');
        }
    }

    protected function seedIdempotent(string $label, callable $fn): void
    {
        if ($this->command) {
            $this->command->getOutput()->write("  $label...");
        }
        $fn();
        if ($this->command) {
            $this->command->getOutput()->writeln(' <info>✓</info>');
        }
    }

    protected function chunk(string $model, array $rows): void
    {
        if (empty($rows)) return;
        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            $model::insert($chunk);
        }
    }

    protected function progressStart(int $max): void
    {
        if ($this->command) {
            $this->command->getOutput()->progressStart($max);
        }
    }

    protected function progressAdvance(int $step = 1): void
    {
        if ($this->command) {
            $this->command->getOutput()->progressAdvance($step);
        }
    }

    protected function progressFinish(): void
    {
        if ($this->command) {
            $this->command->getOutput()->progressFinish();
        }
    }

    protected function randomIdr(float $min = 1_000_000, float $max = 500_000_000): float
    {
        return round($this->faker->randomFloat(2, $min / 1_000_000, $max / 1_000_000) * 1_000_000, 2);
    }

    protected function idrAmount(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    // ─── MASTER DATA ─────────────────────────────────────────────

    protected function seedMasterData(): void
    {
        // Expense Categories
        if (ExpenseCategory::count() < 20) {
            $catNames = [
                'Perjalanan Dinas', 'Software & Lisensi', 'ATK', 'Marketing', 'Subkontraktor',
                'Pelatihan', 'Utilitas', 'Sewa Kantor', 'Asuransi', 'Legal',
                'Biaya Bank', 'Telekomunikasi', 'Hardware', 'Cloud Hosting', 'Konsultan',
                'Rekrutmen', 'Event', 'Percetakan', 'Logistik', 'Lain-lain',
            ];
            $ecats = [];
            foreach ($catNames as $n) {
                $ecats[] = ['name' => $n, 'description' => "Kategori: $n", 'is_active' => true, 'created_at' => now(), 'updated_at' => now()];
            }
            $existing = ExpenseCategory::count();
            while (count($ecats) + $existing > 20) array_pop($ecats);
            if (!empty($ecats)) ExpenseCategory::insert($ecats);
        }
        $this->expenseCategoryIds = ExpenseCategory::pluck('id')->toArray();

        // Departments — ensure minimum
        $deptDefaults = ['Support', 'Sales', 'Engineering', 'Operasional', 'Finance', 'Marketing', 'HR', 'Legal', 'R&D', 'QA'];
        $existingDepts = Department::count();
        foreach (array_slice($deptDefaults, $existingDepts) as $n) {
            Department::create(['name' => $n, 'is_active' => true]);
        }
        $this->departmentIds = Department::pluck('id')->toArray();

        // Items — 200 service/products
        if (Item::count() < 200) {
            $itemNames = [
                'Pengembangan Website', 'Pembuatan Aplikasi Mobile', 'Desain UI/UX', 'Desain Logo', 'Branding Kit',
                'Audit SEO', 'SEO Bulanan', 'Google Ads', 'Social Media Management',
                'Content Writing', 'Copywriting', 'Video Editing', 'Ilustrasi Digital',
                'Setup Server', 'Maintenance Server', 'Cloud Migration', 'Optimasi Database',
                'Security Audit', 'Penetration Testing', 'Code Review', 'API Development',
                'Tema WordPress', 'Plugin WordPress', 'Setup Shopify', 'Laravel Development',
                'Node.js Development', 'React Frontend', 'Vue.js Frontend', 'Flutter App',
                'React Native App', 'iOS App', 'Android App', 'PWA Development',
                'Registrasi Domain', 'SSL Certificate', 'Hosting Basic', 'Hosting Premium',
                'Email Hosting', 'CDN Setup', 'DNS Management', 'Backup Service',
                'IT Consulting', 'Digital Strategy', 'Riset Pasar', 'Analisis Kompetitor',
                'Data Analytics', 'Dashboard Setup', 'Implementasi CRM', 'Implementasi ERP',
                'Email Marketing', 'Newsletter Setup', 'Landing Page', 'Sales Funnel',
                'A/B Testing', 'Conversion Optimization', 'Heatmap Analysis', 'User Testing',
                'Technical Writing', 'API Documentation', 'Video Training', 'Online Course',
                'Workshop Facilitation', 'Coaching Session', 'Staff Augmentation',
                'Project Management', 'QA Testing', 'Automation Script', 'Chatbot Development',
                'AI Integration', 'Machine Learning', 'Data Migration', 'System Integration',
                'Payment Gateway Setup', 'SMS Gateway Setup', 'WhatsApp API Setup', 'Email Gateway',
                'Maintenance Retainer', 'Emergency Support', 'Priority Support', 'SLA Premium',
                'Managed Services Basic', 'Managed Services Pro', 'Custom Dashboard',
                'Custom Report', 'Custom Integration', 'Custom Module Development',
                'Theme Customization', 'Plugin Development', 'Extension Development',
                'Performance Tuning', 'Load Testing', 'Stress Testing', 'Uptime Monitoring',
                'Disaster Recovery Plan', 'Business Continuity', 'GDPR Compliance', 'ISO Readiness',
                'Network Setup', 'Firewall Config', 'VPN Setup', 'Remote Desktop Setup',
                'Cloud Backup', 'On-Premise Backup', 'Hybrid Cloud Setup',
                'Microservices Architecture', 'Legacy Modernization', 'Tech Debt Reduction',
                'Agile Coaching', 'Scrum Master', 'Sprint Planning',
                'Product Roadmap', 'MVP Development', 'Prototype', 'Proof of Concept',
                'Feasibility Study', 'Technical Audit', 'Architecture Review',
                'Accessibility Audit', 'Responsive Testing', 'Cross-browser Testing',
                'White-label Solution', 'SaaS Development', 'Marketplace Setup',
                'Subscription Billing', 'Invoice Automation', 'Payment Reconciliation',
                'Custom Workflow', 'Approval Workflow', 'Notification Engine',
                'Compliance Monitoring', 'Role-Based Access', 'Permission System',
                'Single Sign-On', 'OAuth Integration', 'SAML Integration',
            ];
            $existingItems = Item::count();
            $needed = max(0, 200 - $existingItems);
            $items = [];
            foreach (range(0, $needed - 1) as $i) {
                $n = $itemNames[$i % count($itemNames)] . ' ' . ($i + 1);
                $items[] = [
                    'name' => $n,
                    'description' => 'Layanan: ' . $n,
                    'default_price' => $this->faker->randomFloat(2, 250_000, 50_000_000),
                    'default_tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                    'currency_id' => $this->currencyIds[0],
                    'unit' => $this->faker->randomElement(['hour', 'unit', 'license', 'month', 'project', 'session']),
                    'sku' => 'ITM-' . str_pad($existingItems + $i + 1, 5, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($items)) {
                $this->chunk(Item::class, $items);
            }
        }
        $this->itemIds = Item::pluck('id')->toArray();

        // KB Categories — 10
        if (KbCategory::count() < 10) {
            $kbCatNames = [
                'Memulai', 'Penagihan', 'Proyek', 'Klien', 'Support',
                'Integrasi', 'Keamanan', 'API', 'Mobile', 'Troubleshooting',
            ];
            $existing = KbCategory::count();
            $kbcats = [];
            foreach (array_slice($kbCatNames, $existing) as $i => $n) {
                $kbcats[] = [
                    'name' => $n, 'slug' => Str::slug($n), 'description' => "Artikel seputar $n",
                    'is_public' => true, 'order' => $existing + $i, 'created_at' => now(), 'updated_at' => now(),
                ];
            }
            if (!empty($kbcats)) KbCategory::insert($kbcats);
        }
        $this->kbCatIds = KbCategory::pluck('id')->toArray();

        // Blog Categories — 5
        if (BlogCategory::count() < 5) {
            $blogCatData = [
                ['name' => 'Bisnis', 'slug' => 'bisnis', 'description' => 'Artikel tentang bisnis dan strategi'],
                ['name' => 'Teknologi', 'slug' => 'teknologi', 'description' => 'Teknologi terkini dan implementasinya'],
                ['name' => 'Tips', 'slug' => 'tips', 'description' => 'Tips praktis untuk produktivitas'],
                ['name' => 'Produktivitas', 'slug' => 'produktivitas', 'description' => 'Meningkatkan produktivitas tim'],
                ['name' => 'Update', 'slug' => 'update', 'description' => 'Update fitur dan berita terbaru'],
            ];
            $existing = BlogCategory::count();
            foreach (array_slice($blogCatData, $existing) as $d) {
                BlogCategory::create($d);
            }
        }
        $this->blogCatIds = BlogCategory::pluck('id')->toArray();

        // SLA Policies — ensure min 5
        $slaDefaults = [
            ['name' => 'Basic SLA', 'rules' => json_encode(['response_hours' => 48, 'resolve_hours' => 120]), 'is_active' => true],
            ['name' => 'Standard SLA', 'rules' => json_encode(['response_hours' => 24, 'resolve_hours' => 72]), 'is_active' => true],
            ['name' => 'Premium SLA', 'rules' => json_encode(['response_hours' => 8, 'resolve_hours' => 24]), 'is_active' => true],
            ['name' => 'Enterprise SLA', 'rules' => json_encode(['response_hours' => 4, 'resolve_hours' => 12]), 'is_active' => true],
            ['name' => 'Kritis SLA', 'rules' => json_encode(['response_hours' => 1, 'resolve_hours' => 4]), 'is_active' => true],
        ];
        foreach ($slaDefaults as $s) {
            SlaPolicy::firstOrCreate(['name' => $s['name']], $s);
        }
    }

    // ─── CLIENTS & CONTACTS ──────────────────────────────────────

    protected function seedClientsAndContacts(): void
    {
        // Clients — 1000
        if (Client::count() >= 1000) {
            $this->clientIds = Client::pluck('id')->toArray();
        } else {
            $industries = [
                'Teknologi', 'Manufaktur', 'Ritel', 'Konstruksi', 'Konsultan',
                'Pendidikan', 'Kesehatan', 'Keuangan', 'Logistik', 'E-commerce',
                'Telekomunikasi', 'Energi', 'Hospitality', 'Agrikultur', 'Media',
                'Otomotif', 'F&B', 'Farmasi', 'Properti', 'Pemerintahan',
            ];
            $cities = [
                'Jakarta Selatan', 'Jakarta Pusat', 'Jakarta Barat', 'Jakarta Timur', 'Jakarta Utara',
                'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Yogyakarta',
                'Tangerang', 'Bekasi', 'Depok', 'Bogor', 'Makassar',
                'Palembang', 'Pekanbaru', 'Denpasar', 'Malang', 'Solo',
            ];

            $existing = Client::count();
            $target = 1000;
            $clients = [];

            $this->progressStart($target - $existing);

            $prefixes = ['PT', 'CV', 'UD', 'PT', 'PT', 'CV', 'CV', 'UD'];
            $companySuffixes = [
                'Teknologi', 'Solusi', 'Mandiri', 'Sejahtera', 'Digital', 'Kreatif',
                'Nusantara', 'Global', 'Prima', 'Jaya', 'Sentosa', 'Abadi', 'Makmur',
                'Bersama', 'Unggul', 'Internasional', 'Perkasa', 'Cemerlang', 'Utama', 'Mitra',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $prefix = $prefixes[array_rand($prefixes)];
                $companyName = $prefix . ' ' . $this->faker->word . ' ' . $companySuffixes[array_rand($companySuffixes)];
                $city = $cities[array_rand($cities)];
                $status = $this->faker->randomElement(['active', 'active', 'active', 'active', 'inactive']);

                $clients[] = [
                    'company_name' => $companyName,
                    'industry' => $industries[array_rand($industries)],
                    'website' => 'https://' . Str::slug($companyName) . '.co.id',
                    'phone' => '021-' . rand(1000, 9999) . rand(1000, 9999),
                    'billing_address' => 'Jl. ' . $this->faker->streetName . ' No. ' . rand(1, 200),
                    'billing_city' => $city,
                    'billing_state' => $this->faker->randomElement(['DKI Jakarta', 'Jawa Barat', 'Jawa Timur', 'Jawa Tengah', 'Bali', 'Sumatera Utara']),
                    'billing_country' => 'ID',
                    'billing_postal' => (string) $this->faker->numberBetween(10000, 99999),
                    'tax_id' => '0' . rand(10, 99) . '.' . rand(100, 999) . '.' . rand(100, 999) . '.' . rand(1, 9) . '-000.' . rand(100, 999),
                    'account_manager_id' => $this->faker->randomElement($this->userIds),
                    'default_currency_id' => $this->currencyIds[0],
                    'default_language' => 'id',
                    'status' => $status,
                    'notes' => $status === 'active' ? 'Klien aktif sejak ' . date('Y', strtotime('-' . rand(1, 5) . ' years')) : 'Klien non-aktif',
                    'created_at' => now()->subDays(rand(30, 1095)),
                    'updated_at' => now(),
                ];

                if (count($clients) >= self::CHUNK || $i === $target) {
                    $this->chunk(Client::class, $clients);
                    $this->progressAdvance(count($clients));
                    $clients = [];
                }
            }
            $this->progressFinish();
            $this->clientIds = Client::pluck('id')->toArray();
        }

        // Contacts — 2000 (2 per client)
        if (Contact::count() >= 2000) {
            $this->contactIds = Contact::pluck('id')->toArray();
        } else {
            $existingContacts = Contact::count();
            $targetContacts = 2000;
            $contacts = [];
            $contactNum = $existingContacts;

            $this->progressStart($targetContacts - $existingContacts);

            $firstNames = ['Ahmad', 'Budi', 'Citra', 'Dewi', 'Eko', 'Fitri', 'Gunawan', 'Hendra',
                'Indah', 'Joko', 'Kartika', 'Lina', 'Maya', 'Nina', 'Omar', 'Putri',
                'Rina', 'Sari', 'Tono', 'Umar', 'Vina', 'Wawan', 'Yuni', 'Zainal',
                'Adi', 'Bagus', 'Cindy', 'Dian', 'Eva', 'Fajar', 'Gilang', 'Hadi',
                'Irfan', 'Joni', 'Kiki', 'Lia', 'Mira', 'Nando', 'Oki', 'Pipit'];
            $lastNames = ['Santoso', 'Wijaya', 'Hartono', 'Kusuma', 'Pratama', 'Putra', 'Hidayat',
                'Saputra', 'Nugroho', 'Wibowo', 'Setiawan', 'Susanto', 'Gunawan', 'Mahendra',
                'Pangestu', 'Hermawan', 'Kurniawan', 'Syahputra', 'Ramadhan', 'Purnama'];
            $positions = ['Direktur', 'Manager', 'Staff IT', 'Finance', 'Marketing', 'HRD', 'Operasional',
                'Customer Service', 'Admin', 'Supervisor', 'VP Sales', 'CTO', 'CFO', 'COO'];

            foreach ($this->clientIds as $cid) {
                for ($j = 0; $j < 2; $j++) {
                    $contactNum++;
                    if ($contactNum <= $existingContacts) continue;

                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];
                    $email = strtolower(Str::slug($firstName . '.' . $lastName)) . $contactNum . '@demo.local';

                    $contacts[] = [
                        'client_id' => $cid,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => '08' . rand(100000000, 999999999),
                        'position' => $j === 0 ? $positions[array_rand($positions)] : $positions[array_rand($positions)],
                        'is_primary' => $j === 0,
                        'portal_access' => $this->faker->boolean(45),
                        'password' => bcrypt('password'),
                        'locale' => 'id',
                        'created_at' => now()->subDays(rand(30, 1095)),
                        'updated_at' => now(),
                    ];

                    if (count($contacts) >= self::CHUNK) {
                        $this->chunk(Contact::class, $contacts);
                        $this->progressAdvance(count($contacts));
                        $contacts = [];
                    }
                }
            }
            if (!empty($contacts)) {
                $this->chunk(Contact::class, $contacts);
                $this->progressAdvance(count($contacts));
            }
            $this->progressFinish();
            $this->contactIds = Contact::pluck('id')->toArray();
        }
    }

    // ─── LEADS ───────────────────────────────────────────────────

    protected function seedLeads(): void
    {
        if (Lead::count() >= 1000) {
            $this->leadIds = Lead::pluck('id')->toArray();
            return;
        }

        $existing = Lead::count();
        $target = 1000;
        $leads = [];

        $this->progressStart($target - $existing);

        $companies = [
            'PT Digital Kreatif', 'CV Solusi Tekno', 'UD Prima Jaya', 'PT Inovasi Nusa',
            'CV Global Media', 'PT Cahaya Teknologi', 'UD Sumber Rezeki', 'PT Mega Solusi',
            'CV Karya Cipta', 'PT Bintang Digital',
        ];

        for ($i = $existing + 1; $i <= $target; $i++) {
            $statusId = $this->faker->randomElement($this->leadStatusIds);
            $leadScore = rand(0, 100);
            $leadScoreLevel = $leadScore >= 80 ? 'hot' : ($leadScore >= 50 ? 'warm' : 'cold');

            $leads[] = [
                'name' => $this->faker->name,
                'company' => $this->faker->randomElement($companies),
                'email' => 'lead' . $i . '@demo.local',
                'phone' => '08' . rand(100000000, 999999999),
                'website' => 'https://prospek-' . $i . '.co.id',
                'address' => $this->faker->address,
                'city' => $this->faker->city,
                'country' => 'ID',
                'estimated_value' => $this->randomIdr(5_000_000, 250_000_000),
                'currency_id' => $this->currencyIds[0],
                'lead_source_id' => $this->faker->randomElement($this->leadSourceIds),
                'lead_status_id' => $statusId,
                'assigned_to' => $this->faker->randomElement($this->userIds),
                'description' => 'Prospek dari ' . $this->faker->randomElement(['website', 'iklan FB', 'LinkedIn', 'event', 'referral', 'WhatsApp']) . '. Tertarik dengan layanan CRM dan integrasi sistem.',
                'expected_close' => now()->addDays(rand(5, 120)),
                'last_activity_at' => now()->subDays(rand(0, 30)),
                'lead_score' => $leadScore,
                'lead_score_level' => $leadScoreLevel,
                'lead_score_factors' => json_encode(['engagement' => rand(10, 50), 'budget' => rand(0, 30), 'authority' => rand(0, 20)]),
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];

            if (count($leads) >= self::CHUNK || $i === $target) {
                $this->chunk(Lead::class, $leads);
                $this->progressAdvance(count($leads));
                $leads = [];
            }
        }
        $this->progressFinish();
        $this->leadIds = Lead::pluck('id')->toArray();
    }

    // ─── PROJECTS, TASKS & TIME ──────────────────────────────────

    protected function seedProjects(): void
    {
        // Projects — 100
        if (Project::count() >= 100) {
            $this->projectIds = Project::pluck('id')->toArray();
        } else {
            $existing = Project::count();
            $target = 100;
            $projects = [];
            $projectStatuses = ['not_started', 'in_progress', 'on_hold', 'completed'];
            $billingMethods = ['fixed', 'hourly', 'milestone', 'non_billable'];

            $this->progressStart($target - $existing);

            $projectNames = [
                'Website Company Profile', 'Sistem Inventory Gudang', 'Mobile App E-commerce',
                'Dashboard Analytics', 'Integrasi Payment Gateway', 'Aplikasi HR Management',
                'Customer Portal', 'Sistem POS', 'Mobile App Reservasi', 'ERP Module Finance',
                'Landing Page Campaign', 'Sistem Antrian Online', 'E-learning Platform',
                'Chatbot AI Customer Service', 'Sistem Absensi Digital',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $status = $this->faker->randomElement($projectStatuses);
                $billingMethod = $this->faker->randomElement($billingMethods);
                $startDate = now()->subDays(rand(0, 365));
                $projects[] = [
                    'name' => $projectNames[array_rand($projectNames)] . ' #' . $i,
                    'description' => 'Project pengembangan ' . $this->faker->words(3, true) . ' untuk klien. Mencakup analisis kebutuhan, implementasi, testing, dan deployment.',
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'project_manager_id' => $this->faker->randomElement($this->userIds),
                    'start_date' => $startDate,
                    'deadline' => (clone $startDate)->addDays(rand(30, 180)),
                    'estimate_hours' => $this->faker->randomFloat(2, 20, 600),
                    'billing_method' => $billingMethod,
                    'fixed_price' => $billingMethod === 'fixed' ? $this->randomIdr(10_000_000, 200_000_000) : null,
                    'hourly_rate' => $billingMethod === 'hourly' ? $this->faker->randomFloat(2, 100_000, 500_000) : null,
                    'currency_id' => $this->currencyIds[0],
                    'status' => $status,
                    'progress_pct' => match ($status) {
                        'completed' => 100,
                        'in_progress' => rand(25, 95),
                        'on_hold' => rand(10, 60),
                        default => rand(0, 10),
                    },
                    'is_visible_to_customer' => true,
                    'created_at' => $startDate,
                    'updated_at' => now(),
                ];

                if (count($projects) >= self::CHUNK || $i === $target) {
                    $this->chunk(Project::class, $projects);
                    $this->progressAdvance(count($projects));
                    $projects = [];
                }
            }
            $this->progressFinish();
            $this->projectIds = Project::pluck('id')->toArray();
        }

        // Project Members
        if (DB::table('project_members')->count() < 250) {
            $pmembers = [];
            foreach ($this->projectIds as $pid) {
                $memberCount = rand(2, 4);
                $used = [];
                for ($j = 0; $j < $memberCount; $j++) {
                    do { $uid = $this->faker->randomElement($this->userIds); } while (isset($used[$uid]));
                    $used[$uid] = true;
                    $pmembers[] = [
                        'project_id' => $pid,
                        'user_id' => $uid,
                        'role' => $this->faker->randomElement(['developer', 'designer', 'reviewer', 'manager', 'tester']),
                        'added_at' => now()->subDays(rand(0, 90)),
                    ];
                }
            }
            $this->chunk(ProjectMember::class, $pmembers);
        }

        // Milestones — 200 (avg 2 per project)
        if (Milestone::count() < 200) {
            $milestones = [];
            foreach ($this->projectIds as $pid) {
                for ($j = 1; $j <= 2; $j++) {
                    $milestones[] = [
                        'project_id' => $pid,
                        'name' => ($j === 1 ? 'Fase Awal: ' : 'Fase Akhir: ') . ucfirst($this->faker->words(2, true)),
                        'description' => 'Milestone ke-' . $j . ' project',
                        'due_date' => now()->addDays(rand(7, 120)),
                        'order' => $j,
                        'complete_pct' => $this->faker->numberBetween(0, 100),
                        'created_at' => now()->subDays(rand(0, 60)),
                        'updated_at' => now(),
                    ];
                }
            }
            $this->chunk(Milestone::class, $milestones);
        }
        $this->milestoneIds = Milestone::pluck('id')->toArray();

        // Tasks — 500
        if (Task::count() < 500) {
            $tasks = [];
            $taskStatuses = ['todo', 'in_progress', 'review', 'done'];
            $priorities = ['low', 'medium', 'high', 'urgent'];
            $verbNouns = [
                'Buat', 'Desain', 'Implementasi', 'Testing', 'Dokumentasi', 'Review', 'Setup', 'Konfigurasi',
                'Integrasi', 'Optimasi', 'Debug', 'Refactor', 'Migrasi', 'Deploy', 'Monitor',
            ];
            $objects = [
                'halaman dashboard', 'API endpoint', 'database schema', 'autentikasi user',
                'fitur pencarian', 'laporan PDF', 'email notifikasi', 'CI/CD pipeline',
                'form input data', 'upload file', 'chart analytics', 'export Excel',
                'role permission', 'payment flow', 'push notification',
            ];

            foreach ($this->projectIds as $pid) {
                $projectMilestones = Milestone::where('project_id', $pid)->pluck('id')->toArray();
                $taskCount = rand(4, 6);
                for ($j = 1; $j <= $taskCount; $j++) {
                    $status = $this->faker->randomElement($taskStatuses);
                    $tasks[] = [
                        'project_id' => $pid,
                        'milestone_id' => $this->faker->randomElement($projectMilestones),
                        'title' => $verbNouns[array_rand($verbNouns)] . ' ' . $objects[array_rand($objects)],
                        'description' => 'Task detail untuk implementasi dan testing.',
                        'priority' => $this->faker->randomElement($priorities),
                        'status' => $status,
                        'start_date' => now()->subDays(rand(0, 30)),
                        'due_date' => now()->addDays(rand(1, 60)),
                        'estimate_hours' => $this->faker->randomFloat(2, 1, 40),
                        'is_billable' => $this->faker->boolean(70),
                        'hourly_rate' => $this->faker->randomFloat(2, 100_000, 500_000),
                        'is_visible_to_customer' => $this->faker->boolean(30),
                        'order' => $j,
                        'completed_at' => $status === 'done' ? now()->subDays(rand(1, 14)) : null,
                        'created_by' => $this->faker->randomElement($this->userIds),
                        'created_at' => now()->subDays(rand(0, 90)),
                        'updated_at' => now(),
                    ];
                }
            }
            $this->chunk(Task::class, $tasks);
        }
        $this->taskIds = Task::pluck('id')->toArray();

        // Task Assignees
        if (DB::table('task_assignees')->count() < 600) {
            $tAssigns = [];
            foreach ($this->taskIds as $tid) {
                $assigneeCount = rand(1, 2);
                $usedUids = [];
                for ($j = 0; $j < $assigneeCount; $j++) {
                    do { $uid = $this->faker->randomElement($this->userIds); } while (in_array($uid, $usedUids));
                    $usedUids[] = $uid;
                    $tAssigns[] = ['task_id' => $tid, 'user_id' => $uid, 'assigned_at' => now()->subDays(rand(0, 60))];
                }
            }
            foreach (array_chunk($tAssigns, self::CHUNK) as $chunk) {
                DB::table('task_assignees')->insert($chunk);
            }
        }

        // Time Entries — 1000
        if (TimeEntry::count() < 1000) {
            $existing = TimeEntry::count();
            $target = 1000;
            $tEntries = [];
            $this->progressStart($target - $existing);

            $descriptions = [
                'Analisis requirement dan diskusi dengan tim',
                'Implementasi fitur backend API',
                'Development frontend dashboard',
                'Testing dan bug fixing',
                'Code review dan refactoring',
                'Meeting dengan klien',
                'Dokumentasi teknis',
                'Setup environment development',
                'Integrasi third-party API',
                'Optimasi performa database',
                'Desain wireframe halaman baru',
                'Konfigurasi server production',
                'Penulisan unit test',
                'Deploy ke staging environment',
                'Sprint planning meeting',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $taskId = $this->faker->randomElement($this->taskIds);
                $task = Task::find($taskId);
                $start = now()->subHours(rand(1, 500));
                $minutes = rand(15, 480);
                $end = (clone $start)->addMinutes($minutes);

                $tEntries[] = [
                    'task_id' => $taskId,
                    'project_id' => $task?->project_id ?? $this->faker->randomElement($this->projectIds),
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'start_at' => $start,
                    'end_at' => $end,
                    'minutes' => $minutes,
                    'hourly_rate' => $this->faker->randomFloat(2, 100_000, 500_000),
                    'is_billable' => $this->faker->boolean(75),
                    'is_invoiced' => false,
                    'note' => $this->faker->randomElement($descriptions),
                    'created_at' => $start,
                    'updated_at' => $end,
                ];

                if (count($tEntries) >= self::CHUNK || $i === $target) {
                    $this->chunk(TimeEntry::class, $tEntries);
                    $this->progressAdvance(count($tEntries));
                    $tEntries = [];
                }
            }
            $this->progressFinish();
        }

        // Discussions — 50
        if (Discussion::count() < 50) {
            $discussions = [];
            foreach ($this->projectIds as $pid) {
                if (Discussion::count() >= 50) break;
                $discussions[] = [
                    'project_id' => $pid,
                    'subject' => 'Diskusi: ' . ucfirst($this->faker->words(rand(3, 6), true)),
                    'body' => $this->faker->paragraphs(rand(2, 4), true),
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'is_visible_to_customer' => $this->faker->boolean(40),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now(),
                ];
            }
            if (!empty($discussions)) Discussion::insert($discussions);
        }
        $discussionIds = Discussion::pluck('id')->toArray();

        // Discussion Replies
        if (DiscussionReply::count() < 200) {
            $dReplies = [];
            foreach ($discussionIds as $did) {
                $replyCount = rand(2, 5);
                for ($j = 0; $j < $replyCount; $j++) {
                    $dReplies[] = [
                        'discussion_id' => $did,
                        'body' => $this->faker->paragraph,
                        'user_id' => $this->faker->randomElement($this->userIds),
                        'created_at' => now()->subDays(rand(0, 60)),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($dReplies)) $this->chunk(DiscussionReply::class, $dReplies);
        }
    }

    // ─── SALES ───────────────────────────────────────────────────

    protected function seedSales(): void
    {
        // Estimates — 200
        if (Estimate::count() < 200) {
            $existing = Estimate::count();
            $target = 200;
            $estimates = [];
            $this->progressStart($target - $existing);

            for ($i = $existing + 1; $i <= $target; $i++) {
                $subtotal = $this->randomIdr(2_000_000, 150_000_000);
                $discount = $this->faker->boolean(30) ? round($subtotal * $this->faker->randomFloat(2, 0.05, 0.20), 2) : 0;
                $taxTotal = round(($subtotal - $discount) * 0.11, 2);
                $total = $subtotal - $discount + $taxTotal;
                $estimateDate = now()->subDays(rand(0, 180));

                $estimates[] = [
                    'number' => 'EST-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'estimate_date' => $estimateDate,
                    'expiry_date' => (clone $estimateDate)->addDays(rand(7, 30)),
                    'currency_id' => $this->currencyIds[0],
                    'subtotal' => $subtotal,
                    'discount_total' => $discount,
                    'tax_total' => $taxTotal,
                    'total' => $total,
                    'status' => $this->faker->randomElement(['draft', 'draft', 'sent', 'sent', 'sent', 'accepted', 'accepted', 'declined', 'expired']),
                    'notes' => 'Estimasi biaya untuk ' . $this->faker->randomElement(['Pengembangan Website', 'Aplikasi Mobile', 'Sistem ERP', 'Dashboard Admin', 'Integrasi API', 'Konsultasi IT']),
                    'terms' => 'Berlaku 14 hari sejak tanggal estimasi',
                    'public_token' => Str::random(40),
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => $estimateDate,
                    'updated_at' => now(),
                ];

                if (count($estimates) >= self::CHUNK || $i === $target) {
                    $this->chunk(Estimate::class, $estimates);
                    $this->progressAdvance(count($estimates));
                    $estimates = [];
                }
            }
            $this->progressFinish();
        }
        $estimateIds = Estimate::pluck('id')->toArray();

        // Estimate Items
        if (EstimateItem::count() < 600) {
            $estItems = [];
            foreach ($estimateIds as $eid) {
                $itemCount = rand(2, 4);
                for ($j = 1; $j <= $itemCount; $j++) {
                    $qty = rand(1, 10);
                    $price = $this->faker->randomFloat(2, 500_000, 25_000_000);
                    $estItems[] = [
                        'estimate_id' => $eid,
                        'item_id' => $this->faker->randomElement($this->itemIds),
                        'description' => 'Item estimasi: ' . $this->faker->sentence(3),
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                        'discount_pct' => $this->faker->randomFloat(4, 0, 15),
                        'line_total' => round($qty * $price, 2),
                        'order' => $j,
                    ];
                }
            }
            $this->chunk(EstimateItem::class, $estItems);
        }

        // Proposals — 100
        if (Proposal::count() < 100) {
            $proposals = [];
            for ($i = 1; $i <= 100; $i++) {
                $status = $this->faker->randomElement(['draft', 'sent', 'accepted', 'declined']);
                $content = '<h2>Proposal Layanan</h2><p>' . implode('</p><p>', $this->faker->paragraphs(rand(4, 8))) . '</p>';
                $proposals[] = [
                    'number' => 'PROP-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'subject' => 'Proposal: ' . ucfirst($this->faker->words(rand(3, 6), true)),
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'lead_id' => $this->faker->boolean(30) ? $this->faker->randomElement($this->leadIds) : null,
                    'content' => $content,
                    'total' => $this->randomIdr(5_000_000, 300_000_000),
                    'currency_id' => $this->currencyIds[0],
                    'open_until' => now()->addDays(rand(14, 60)),
                    'status' => $status,
                    'is_template' => false,
                    'public_token' => Str::random(40),
                    'accepted_at' => $status === 'accepted' ? now()->subDays(rand(1, 60)) : null,
                    'accepted_by_name' => $status === 'accepted' ? $this->faker->name : null,
                    'accepted_signature' => $status === 'accepted' ? $this->faker->sha256 : null,
                    'accepted_ip' => $status === 'accepted' ? $this->faker->ipv4 : null,
                    'declined_at' => $status === 'declined' ? now()->subDays(rand(1, 30)) : null,
                    'decline_reason' => $status === 'declined' ? 'Harga terlalu tinggi dan tidak sesuai budget' : null,
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now(),
                ];
            }
            $this->chunk(Proposal::class, $proposals);
        }

        // Contracts — 50
        if (Contract::count() < 50) {
            $contracts = [];
            for ($i = 1; $i <= 50; $i++) {
                $startDate = now()->subDays(rand(0, 180));
                $contracts[] = [
                    'number' => 'CON-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'subject' => 'Kontrak: ' . ucfirst($this->faker->words(rand(3, 6), true)),
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'content' => '<h2>Perjanjian Kerjasama</h2><p>' . implode('</p><p>', $this->faker->paragraphs(rand(5, 10))) . '</p>',
                    'start_date' => $startDate,
                    'end_date' => (clone $startDate)->addMonths(rand(3, 24)),
                    'contract_value' => $this->randomIdr(10_000_000, 500_000_000),
                    'currency_id' => $this->currencyIds[0],
                    'status' => $this->faker->randomElement(['draft', 'sent', 'signed', 'signed', 'signed']),
                    'is_template' => false,
                    'public_token' => Str::random(40),
                    'notify_expiry_days_before' => 30,
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => $startDate,
                    'updated_at' => now(),
                ];
            }
            $this->chunk(Contract::class, $contracts);
        }

        // Invoices — 500
        if (Invoice::count() < 500) {
            $existing = Invoice::count();
            $target = 500;
            $invoices = [];
            $this->progressStart($target - $existing);

            $services = [
                'Pengembangan Website', 'Aplikasi Mobile', 'Konsultasi IT', 'Maintenance Bulanan',
                'Desain UI/UX', 'SEO & Marketing', 'Cloud Hosting', 'Managed Services',
                'Training & Workshop', 'Support Premium',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $subtotal = $this->randomIdr(1_000_000, 200_000_000);
                $discount = $this->faker->boolean(20) ? round($subtotal * $this->faker->randomFloat(2, 0.05, 0.15), 2) : 0;
                $taxTotal = round(($subtotal - $discount) * 0.11, 2);
                $total = $subtotal - $discount + $taxTotal;
                $status = $this->faker->randomElement(['draft', 'sent', 'sent', 'paid', 'paid', 'paid', 'overdue', 'partial']);
                $paidTotal = match ($status) {
                    'paid' => $total,
                    'partial' => round($total * $this->faker->randomFloat(2, 0.25, 0.75), 2),
                    default => 0,
                };
                $invoiceDate = now()->subDays(rand(0, 180));

                $invoices[] = [
                    'number' => 'INV-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'project_id' => $this->faker->boolean(35) ? $this->faker->randomElement($this->projectIds) : null,
                    'invoice_date' => $invoiceDate,
                    'due_date' => (clone $invoiceDate)->addDays(rand(14, 30)),
                    'currency_id' => $this->currencyIds[0],
                    'subtotal' => $subtotal,
                    'discount_total' => $discount,
                    'tax_total' => $taxTotal,
                    'total' => $total,
                    'paid_total' => $paidTotal,
                    'balance_due' => $total - $paidTotal,
                    'status' => $status,
                    'is_recurring' => $this->faker->boolean(8),
                    'notes' => 'Pembayaran untuk ' . $this->faker->randomElement($services),
                    'terms' => 'Pembayaran dalam 14 hari sejak tanggal faktur',
                    'public_token' => Str::random(40),
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => $invoiceDate,
                    'updated_at' => now(),
                ];

                if (count($invoices) >= self::CHUNK || $i === $target) {
                    $this->chunk(Invoice::class, $invoices);
                    $this->progressAdvance(count($invoices));
                    $invoices = [];
                }
            }
            $this->progressFinish();
        }
        $this->invoiceIds = Invoice::pluck('id')->toArray();

        // Invoice Items — ~1500 (avg 3 per invoice)
        if (InvoiceItem::count() < 1500) {
            $invItems = [];
            foreach ($this->invoiceIds as $iid) {
                $itemCount = rand(2, 5);
                for ($j = 1; $j <= $itemCount; $j++) {
                    $qty = rand(1, 20);
                    $price = $this->faker->randomFloat(2, 250_000, 50_000_000);
                    $invItems[] = [
                        'invoice_id' => $iid,
                        'item_id' => $this->faker->randomElement($this->itemIds),
                        'description' => 'Layanan: ' . $this->faker->sentence(3),
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                        'discount_pct' => $this->faker->randomFloat(4, 0, 10),
                        'line_total' => round($qty * $price, 2),
                        'order' => $j,
                    ];
                }
            }
            $this->chunk(InvoiceItem::class, $invItems);
        }

        // Payments — 500
        if (Payment::count() < 500) {
            $payments = [];
            for ($i = 1; $i <= 500; $i++) {
                $payments[] = [
                    'invoice_id' => $this->faker->randomElement($this->invoiceIds),
                    'amount' => $this->randomIdr(500_000, 150_000_000),
                    'currency_id' => $this->currencyIds[0],
                    'paid_at' => now()->subDays(rand(0, 180)),
                    'method' => $this->faker->randomElement(['bank_transfer', 'bank_transfer', 'bank_transfer', 'cash', 'ewallet', 'ewallet', 'qris']),
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(16)),
                    'status' => $this->faker->randomElement(['completed', 'completed', 'completed', 'pending', 'failed']),
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now(),
                ];
            }
            $this->chunk(Payment::class, $payments);
        }

        // Credit Notes — 100
        if (CreditNote::count() < 100) {
            $cnotes = [];
            for ($i = 1; $i <= 100; $i++) {
                $total = $this->randomIdr(500_000, 50_000_000);
                $applied = round($total * $this->faker->randomFloat(2, 0.3, 1.0), 2);
                $refunded = round(max(0, $total - $applied) * $this->faker->randomFloat(2, 0, 1), 2);
                $cnotes[] = [
                    'number' => 'CN-' . date('Y') . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'issue_date' => now()->subDays(rand(0, 120)),
                    'total' => $total,
                    'applied_total' => $applied,
                    'refunded_total' => $refunded,
                    'currency_id' => $this->currencyIds[0],
                    'status' => $this->faker->randomElement(['draft', 'issued', 'applied', 'closed']),
                    'reason' => $this->faker->randomElement([
                        'Kelebihan pembayaran', 'Diskon retrospektif', 'Koreksi faktur',
                        'Pembatalan layanan', 'Garansi refund', 'Kompensasi keterlambatan',
                    ]),
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 120)),
                    'updated_at' => now(),
                ];
            }
            $this->chunk(CreditNote::class, $cnotes);
        }
        $creditNoteIds = CreditNote::pluck('id')->toArray();

        // Credit Note Invoices
        if (DB::table('credit_note_invoices')->count() < 150) {
            $cnInvs = [];
            foreach ($creditNoteIds as $cnId) {
                $cnInvs[] = [
                    'credit_note_id' => $cnId,
                    'invoice_id' => $this->faker->randomElement($this->invoiceIds),
                    'amount_applied' => $this->randomIdr(250_000, 25_000_000),
                    'applied_at' => now()->subDays(rand(0, 60)),
                ];
            }
            foreach (array_chunk($cnInvs, self::CHUNK) as $chunk) {
                DB::table('credit_note_invoices')->insert($chunk);
            }
        }
    }

    // ─── TICKETS ─────────────────────────────────────────────────

    protected function seedTickets(): void
    {
        if (Ticket::count() < 500) {
            $existing = Ticket::count();
            $target = 500;
            $tickets = [];
            $this->progressStart($target - $existing);

            $slaPolicyIds = SlaPolicy::pluck('id')->toArray();

            $ticketSubjects = [
                'Tidak bisa login ke portal', 'Invoice tidak muncul di dashboard',
                'Error saat upload file', 'Performa lambat di jam sibuk',
                'Permintaan reset password', 'Fitur export tidak berfungsi',
                'Integrasi email gagal', 'Data client tidak tersimpan',
                'Notifikasi tidak masuk', 'Permintaan penambahan user baru',
                'Bug di halaman laporan', 'Permintaan custom report',
                'Server down', 'Migrasi data dari CRM lama',
                'Training untuk tim baru', 'Update payment method',
                'Akses API ditolak', 'Domain whitelist error',
                'Backup database gagal', 'Gangguan koneksi database',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $tickets[] = [
                    'number' => 'T-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'subject' => $this->faker->randomElement($ticketSubjects) . ' #' . $i,
                    'body' => $this->faker->paragraphs(rand(2, 4), true),
                    'client_id' => $this->faker->randomElement($this->clientIds),
                    'contact_id' => $this->faker->boolean(50) ? $this->faker->randomElement($this->contactIds) : null,
                    'department_id' => $this->faker->randomElement($this->departmentIds),
                    'priority_id' => $this->faker->randomElement($this->ticketPriorityIds),
                    'status_id' => $this->faker->randomElement($this->ticketStatusIds),
                    'sla_policy_id' => $this->faker->randomElement([null, ...$slaPolicyIds]),
                    'assigned_to' => $this->faker->randomElement($this->userIds),
                    'related_project_id' => $this->faker->boolean(15) ? $this->faker->randomElement($this->projectIds) : null,
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now(),
                ];

                if (count($tickets) >= self::CHUNK || $i === $target) {
                    $this->chunk(Ticket::class, $tickets);
                    $this->progressAdvance(count($tickets));
                    $tickets = [];
                }
            }
            $this->progressFinish();
        }
        $this->ticketIds = Ticket::pluck('id')->toArray();

        // Ticket Replies — avg 2 per ticket
        if (TicketReply::count() < 1000) {
            $tReplies = [];
            foreach ($this->ticketIds as $tid) {
                $replyCount = rand(1, 3);
                for ($j = 0; $j < $replyCount; $j++) {
                    $tReplies[] = [
                        'ticket_id' => $tid,
                        'body' => $this->faker->paragraphs(rand(1, 3), true),
                        'user_id' => $this->faker->randomElement([...$this->userIds, null]),
                        'contact_id' => $j % 2 === 0 ? $this->faker->randomElement($this->contactIds) : null,
                        'is_internal' => $this->faker->boolean(25),
                        'source' => $this->faker->randomElement(['web', 'email', 'api']),
                        'created_at' => now()->subDays(rand(0, 90)),
                        'updated_at' => now(),
                    ];
                }
            }
            $this->chunk(TicketReply::class, $tReplies);
        }
    }

    // ─── MARKETING ───────────────────────────────────────────────

    protected function seedMarketing(): void
    {
        // KB Articles — 100 (Bahasa Indonesia)
        if (KbArticle::count() < 100) {
            $existing = KbArticle::count();
            $target = 100;
            $kbArticles = [];
            $this->progressStart($target - $existing);

            $articleTemplates = [
                ['Cara Mengelola Klien di CRM', 'Panduan lengkap mengelola data klien, kontak, dan preferensi di CRM Office. Mulai dari menambahkan klien baru hingga mengatur segmentasi.'],
                ['Panduan Membuat Faktur Profesional', 'Cara membuat faktur dengan template profesional. Mencakup pengaturan item, pajak PPN 11%, dan pengiriman via email.'],
                ['Tips Mengoptimalkan Pipeline Prospek', 'Strategi mengelola prospek dari lead hingga closing. Tips mengatur lead status, follow-up otomatis, dan monitoring konversi.'],
                ['Mengenal Fitur Manajemen Proyek', 'Overview fitur manajemen proyek: milestone, task board, Gantt chart, time tracking, dan diskusi tim.'],
                ['Setup Helpdesk & SLA Policy', 'Panduan konfigurasi helpdesk: department, prioritas ticket, SLA policy, dan canned responses.'],
                ['Tutorial Integrasi Payment Gateway', 'Panduan menghubungkan payment gateway seperti Midtrans dan Xendit ke CRM Office.'],
                ['Export Data ke Excel & PDF', 'Cara mengekspor data ke Excel dan PDF untuk laporan, backup, dan audit.'],
                ['Mengelola Knowledge Base', 'Tips membuat artikel knowledge base yang efektif untuk customer self-service.'],
                ['Keamanan & Manajemen User', 'Panduan mengelola user, role, permission, dan fitur keamanan seperti 2FA.'],
                ['Optimasi Performa CRM', 'Tips mengoptimalkan performa CRM Office untuk loading cepat dan responsif.'],
                ['Cara Membuat Proposal Bisnis', 'Panduan membuat proposal profesional dengan template dan tracking status.'],
                ['Panduan Lengkap Survei Pelanggan', 'Cara membuat, mengirim, dan menganalisis survei kepuasan pelanggan.'],
                ['Automasi Follow-up Prospek', 'Mengatur automasi follow-up email dan WhatsApp untuk prospek baru.'],
                ['Membuat Dashboard Kustom', 'Tutorial membuat dashboard kustom dengan widget yang relevan untuk role Anda.'],
                ['Integrasi Email & Notifikasi', 'Setup integrasi email SMTP, template notifikasi, dan webhook.'],
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $template = $articleTemplates[($i - 1) % count($articleTemplates)];
                $title = $template[0] . ' (' . chr(65 + ($i % 26)) . ')';
                $content = '<h2>' . $title . '</h2>';
                $content .= '<p>' . $template[1] . '</p>';
                $content .= '<p>CRM Office dirancang untuk membantu bisnis Indonesia mengelola hubungan pelanggan secara efisien. Artikel ini adalah bagian dari panduan lengkap penggunaan CRM Office.</p>';
                $content .= '<h3>Langkah-langkah</h3><ol>';
                for ($j = 1; $j <= rand(4, 7); $j++) {
                    $content .= '<li>Langkah ke-' . $j . ': ' . $this->faker->sentence . '</li>';
                }
                $content .= '</ol>';
                $content .= '<p>Jika ada pertanyaan lebih lanjut, hubungi tim support kami melalui ticket atau email support@crmoffice.id.</p>';

                $kbArticles[] = [
                    'category_id' => $this->faker->randomElement($this->kbCatIds),
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . $i,
                    'excerpt' => $template[1],
                    'content' => $content,
                    'is_published' => true,
                    'view_count' => rand(10, 5000),
                    'helpful_count' => rand(5, 300),
                    'unhelpful_count' => rand(0, 40),
                    'author_id' => $this->faker->randomElement($this->userIds),
                    'published_at' => now()->subDays(rand(1, 365)),
                    'meta_title' => $title . ' | CRM Office Knowledge Base',
                    'meta_description' => $template[1],
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ];

                if (count($kbArticles) >= self::CHUNK || $i === $target) {
                    $this->chunk(KbArticle::class, $kbArticles);
                    $this->progressAdvance(count($kbArticles));
                    $kbArticles = [];
                }
            }
            $this->progressFinish();
        }

        // Blog Posts — 100 (Bahasa Indonesia)
        if (BlogPost::count() < 100) {
            $existing = BlogPost::count();
            $target = 100;
            $blogPosts = [];
            $this->progressStart($target - $existing);

            $blogTitles = [
                '5 Strategi CRM untuk Meningkatkan Penjualan Bisnis Anda',
                'Kenapa Bisnis Kecil Perlu CRM di Era Digital',
                'Cara Memilih Software CRM yang Tepat untuk Tim Anda',
                'Panduan Lengkap Digitalisasi Proses Bisnis',
                'Manfaat Self-Hosted CRM untuk Keamanan Data',
                'Tips Meningkatkan Produktivitas Tim dengan CRM',
                'Bagaimana CRM Membantu Retensi Pelanggan',
                'Tren Teknologi Bisnis 2026 yang Perlu Anda Tahu',
                'Cara Membangun Hubungan Pelanggan Jangka Panjang',
                'Mengapa Excel Tidak Lagi Cukup untuk Manajemen Data',
                'Rahasia Sukses Tim Sales dengan Pipeline Management',
                'Pentingnya Satu Source of Truth dalam Bisnis',
                'Cara Efektif Mengelola Prospek dan Deal',
                'Studi Kasus: Transformasi Digital UKM dengan CRM',
                '5 Fitur CRM yang Paling Sering Diabaikan',
                'Panduan Lengkap Customer Success Management',
                'Cara Membangun Tim Sales yang Produktif',
                'Keuntungan Whitelabel CRM untuk Bisnis Agency',
                'Tips Mengelola Database Pelanggan dengan Efisien',
                'Peran AI dalam CRM Modern',
            ];

            $blogContents = [
                '<p>Di era digital yang kompetitif, mengelola hubungan pelanggan menjadi kunci kesuksesan bisnis. CRM (Customer Relationship Management) bukan lagi alat eksklusif perusahaan besar — bisnis kecil dan menengah justru mendapat manfaat paling besar.</p><p>Dengan CRM yang tepat, Anda bisa melacak setiap interaksi pelanggan, mengotomatisasi follow-up, dan mendapatkan insight dari data penjualan. Hasilnya: tim lebih produktif, pelanggan lebih puas, dan revenue meningkat.</p><p>Artikel ini membahas strategi praktis yang bisa langsung Anda terapkan untuk memaksimalkan penggunaan CRM di bisnis Anda.</p>',
                '<p>Banyak pemilik bisnis masih mengandalkan Excel dan catatan manual untuk mengelola data pelanggan. Meskipun terlihat sederhana, pendekatan ini rentan terhadap kehilangan data, duplikasi, dan missed follow-up.</p><p>CRM modern seperti CRM Office menyatukan semua informasi dalam satu platform: kontak, prospek, faktur, proyek, dan dukungan pelanggan. Data selalu up-to-date dan bisa diakses seluruh tim secara real-time.</p><p>Artikel ini akan membahas alasan kenapa bisnis kecil perlu beralih ke CRM dan bagaimana memulainya dengan anggaran terbatas.</p>',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $title = $blogTitles[($i - 1) % count($blogTitles)] . ' (' . chr(65 + ($i % 26)) . ')';
                $slug = Str::slug($title);
                $publishDate = now()->subDays(rand(1, 730));

                $blogPosts[] = [
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $this->faker->randomElement($blogContents),
                    'excerpt' => 'Pelajari ' . strtolower(substr($title, 0, 80)) . '... dalam artikel lengkap yang membahas strategi praktis untuk bisnis Anda.',
                    'category_id' => $this->faker->randomElement($this->blogCatIds),
                    'author_id' => $this->faker->randomElement($this->userIds),
                    'published_at' => $publishDate,
                    'is_published' => true,
                    'meta_title' => $title,
                    'meta_description' => 'Artikel tentang ' . strtolower($title) . '. Tips praktis untuk bisnis Indonesia.',
                    'created_at' => $publishDate,
                    'updated_at' => now(),
                ];

                if (count($blogPosts) >= self::CHUNK || $i === $target) {
                    $this->chunk(BlogPost::class, $blogPosts);
                    $this->progressAdvance(count($blogPosts));
                    $blogPosts = [];
                }
            }
            $this->progressFinish();
        }

        // Announcements — 10
        if (Announcement::count() < 10) {
            $announcements = [
                ['Pemeliharaan Sistem Terjadwal', 'Kami akan melakukan pemeliharaan sistem pada hari Sabtu, pukul 02:00-04:00 WIB. Beberapa layanan mungkin tidak tersedia.', 'all'],
                ['Fitur Baru: Export PDF', 'Kini Anda bisa mengekspor laporan ke format PDF langsung dari dashboard. Tersedia untuk semua modul laporan.', 'staff'],
                ['Update Kebijakan Privasi', 'Kebijakan privasi telah diperbarui sesuai UU PDP. Silakan baca di halaman Kebijakan Privasi.', 'customers'],
                ['Selamat Datang Tim Baru!', 'Kami menyambut anggota tim baru di departemen support. Respon ticket kini lebih cepat!', 'staff'],
                ['Promo: Diskon Setup 20%', 'Dapatkan diskon 20% untuk setup dan migrasi data. Berlaku hingga akhir bulan ini.', 'customers'],
                ['Webinar: Maksimalkan CRM', 'Ikuti webinar gratis "Maksimalkan CRM untuk Bisnis Anda" setiap Kamis pukul 14:00 WIB.', 'all'],
                ['Update Versi 2.5.0', 'Versi terbaru menghadirkan fitur dark mode, peningkatan performa, dan perbaikan bug.', 'staff'],
                ['Libur Nasional', 'Kantor tutup pada hari libur nasional. Support darurat tetap tersedia via WhatsApp.', 'all'],
                ['Fitur Baru: Time Tracking', 'Kini Anda bisa mencatat jam kerja per task. Terintegrasi langsung dengan billing.', 'staff'],
                ['Survei Kepuasan Pengguna', 'Bantu kami meningkatkan layanan dengan mengisi survei singkat 3 menit.', 'customers'],
            ];

            foreach ($announcements as $i => [$title, $body, $audience]) {
                Announcement::create([
                    'title' => $title,
                    'body' => $body,
                    'audience' => $audience,
                    'author_id' => $this->faker->randomElement($this->userIds),
                    'publish_at' => now()->subDays(rand(0, 30)),
                    'expires_at' => now()->addDays(rand(7, 90)),
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
            }
        }

        // Surveys — 20
        if (Survey::count() < 20) {
            $surveys = [];
            for ($i = 1; $i <= 20; $i++) {
                $surveys[] = [
                    'title' => 'Survei: ' . $this->faker->randomElement([
                        'Kepuasan Pelanggan', 'Feedback Layanan', 'Kebutuhan Fitur',
                        'Pengalaman Support', 'Kualitas Produk', 'Minat Fitur Baru',
                    ]) . ' Q' . ceil($i / 4),
                    'description' => $this->faker->sentence,
                    'audience' => $this->faker->randomElement(['all_clients', 'recent_clients', 'all']),
                    'public_token' => Str::random(32),
                    'is_active' => $this->faker->boolean(80),
                    'starts_at' => now()->subDays(rand(0, 60)),
                    'ends_at' => now()->addDays(rand(7, 90)),
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now(),
                ];
            }
            Survey::insert($surveys);
        }
        $surveyIds = Survey::pluck('id')->toArray();

        // Survey Questions
        if (SurveyQuestion::count() < 100) {
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
            foreach (array_chunk($sQuestions, self::CHUNK) as $chunk) {
                SurveyQuestion::insert($chunk);
            }
        }
        $surveyQuestionIds = SurveyQuestion::pluck('id')->toArray();

        // Survey Responses
        if (SurveyResponse::count() < 100) {
            $sResponses = [];
            for ($i = 1; $i <= 100; $i++) {
                $sResponses[] = [
                    'survey_id' => $this->faker->randomElement($surveyIds),
                    'contact_id' => $this->faker->boolean(60) ? $this->faker->randomElement($this->contactIds) : null,
                    'anonymous_token' => Str::random(16),
                    'ip_address' => $this->faker->ipv4,
                    'submitted_at' => now()->subDays(rand(0, 30)),
                ];
            }
            foreach (array_chunk($sResponses, self::CHUNK) as $chunk) {
                SurveyResponse::insert($chunk);
            }
        }
        $surveyResponseIds = SurveyResponse::pluck('id')->toArray();

        // Survey Answers
        if (SurveyAnswer::count() < 400) {
            $sAnswers = [];
            foreach ($surveyResponseIds as $srid) {
                $answerCount = rand(2, 5);
                $questions = $this->faker->randomElements($surveyQuestionIds, $answerCount);
                foreach ($questions as $qid) {
                    $sAnswers[] = [
                        'response_id' => $srid,
                        'question_id' => $qid,
                        'answer' => $this->faker->sentence(rand(1, 4)),
                    ];
                }
            }
            foreach (array_chunk($sAnswers, self::CHUNK) as $chunk) {
                SurveyAnswer::insert($chunk);
            }
        }
    }

    // ─── ACTIVITIES & NOTES ──────────────────────────────────────

    protected function seedActivitiesAndNotes(): void
    {
        // Activities — 500
        if (Activity::count() < 500) {
            $existing = Activity::count();
            $target = 500;
            $activities = [];
            $this->progressStart($target - $existing);

            $subjectTypes = [
                'App\Models\Client' => $this->clientIds,
                'App\Models\Lead' => $this->leadIds,
                'App\Models\Project' => $this->projectIds,
                'App\Models\Task' => $this->taskIds,
                'App\Models\Ticket' => $this->ticketIds,
                'App\Models\Invoice' => $this->invoiceIds,
            ];
            $activityTypes = ['call', 'meeting', 'email', 'note', 'task', 'created', 'updated', 'status_changed'];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $typeKey = array_rand($subjectTypes);
                $subjects = $subjectTypes[$typeKey];
                $activities[] = [
                    'subject_type' => $typeKey,
                    'subject_id' => $this->faker->randomElement($subjects),
                    'type' => $this->faker->randomElement($activityTypes),
                    'subject' => $this->faker->sentence(rand(3, 6)),
                    'description' => $this->faker->sentence,
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'occurred_at' => now()->subDays(rand(0, 180)),
                    'duration_minutes' => $this->faker->boolean(25) ? rand(5, 180) : null,
                    'metadata' => json_encode(['source' => $this->faker->randomElement(['web', 'mobile', 'api', 'email'])]),
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now(),
                ];

                if (count($activities) >= self::CHUNK || $i === $target) {
                    $this->chunk(Activity::class, $activities);
                    $this->progressAdvance(count($activities));
                    $activities = [];
                }
            }
            $this->progressFinish();
        }

        // Notes — 500
        if (Note::count() < 500) {
            $existing = Note::count();
            $target = 500;
            $notes = [];
            $this->progressStart($target - $existing);

            $notableTypes = [
                'App\Models\Client' => $this->clientIds,
                'App\Models\Lead' => $this->leadIds,
                'App\Models\Project' => $this->projectIds,
                'App\Models\Task' => $this->taskIds,
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $typeKey = array_rand($notableTypes);
                $noteBody = $this->faker->paragraphs(rand(1, 3), true);

                $notes[] = [
                    'notable_type' => $typeKey,
                    'notable_id' => $this->faker->randomElement($notableTypes[$typeKey]),
                    'body' => $noteBody,
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now(),
                ];

                if (count($notes) >= self::CHUNK || $i === $target) {
                    $this->chunk(Note::class, $notes);
                    $this->progressAdvance(count($notes));
                    $notes = [];
                }
            }
            $this->progressFinish();
        }
    }

    // ─── CALENDAR EVENTS ─────────────────────────────────────────

    protected function seedCalendarEvents(): void
    {
        if (CalendarEvent::count() < 100) {
            $events = [];
            $colors = ['#4f46e5', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#eab308', '#06b6d4', '#ec4899'];

            $eventLabels = [
                'Meeting Mingguan', 'Review Proyek', 'Presentasi Klien', 'Training Staff',
                'Deadline Proyek', 'Standup Harian', 'Webinar', 'Workshop',
                'Sprint Planning', 'Retrospektif', 'One-on-One', 'Interview',
                'Demo Produk', 'Pitching', 'Konferensi', 'Team Building',
            ];

            for ($i = 1; $i <= 100; $i++) {
                $allDay = $this->faker->boolean(15);
                $isUpcoming = $this->faker->boolean(50);
                $start = $isUpcoming ? now()->addDays(rand(1, 60))->setHour(rand(7, 17))->setMinute(0) :
                    now()->subDays(rand(0, 60))->setHour(rand(7, 17))->setMinute(0);

                $events[] = [
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'title' => $this->faker->randomElement($eventLabels) . ' #' . $i,
                    'description' => $this->faker->boolean(60) ? $this->faker->sentence : null,
                    'starts_at' => $start,
                    'ends_at' => $allDay ? (clone $start)->addHours(8) : (clone $start)->addHours(rand(1, 3)),
                    'all_day' => $allDay,
                    'color' => $colors[array_rand($colors)],
                    'reminder_minutes_before' => $this->faker->randomElement([10, 15, 30, 60, 1440, null]),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now(),
                ];
            }
            $this->chunk(CalendarEvent::class, $events);
        }
    }

    // ─── GOALS ───────────────────────────────────────────────────

    protected function seedGoals(): void
    {
        if (Goal::count() < 50) {
            $goals = [];
            for ($i = 1; $i <= 50; $i++) {
                $target = $this->randomIdr(5_000_000, 500_000_000);
                $goals[] = [
                    'user_id' => $this->faker->randomElement($this->userIds),
                    'name' => 'Target ' . ucfirst($this->faker->words(rand(2, 4), true)),
                    'description' => 'Mencapai target ' . strtolower($this->faker->words(2, true)) . ' dalam periode yang ditentukan.',
                    'metric' => $this->faker->randomElement(['revenue', 'leads', 'projects', 'tickets_resolved', 'hours_logged', 'deals_closed']),
                    'target' => $target,
                    'current' => round($target * $this->faker->randomFloat(2, 0.10, 0.95), 2),
                    'start_date' => now()->subDays(rand(0, 90)),
                    'end_date' => now()->addDays(rand(30, 270)),
                    'status' => $this->faker->randomElement(['in_progress', 'in_progress', 'in_progress', 'completed', 'not_started']),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now(),
                ];
            }
            $this->chunk(Goal::class, $goals);
        }
    }

    // ─── CANNED RESPONSES ────────────────────────────────────────

    protected function seedCannedResponses(): void
    {
        if (CannedResponse::count() < 50) {
            $responses = [];

            $templates = [
                'Salam Pembuka' => "Halo,\n\nTerima kasih telah menghubungi tim support kami. Kami akan segera menindaklanjuti pertanyaan Anda.\n\nSalam,\nTim Support",
                'Follow-up Ticket' => "Halo,\n\nKami ingin memastikan apakah masalah Anda sudah terselesaikan. Jika masih ada kendala, silakan balas ticket ini.\n\nTerima kasih.",
                'Reset Password' => "Halo,\n\nBerikut langkah untuk reset password:\n1. Klik 'Lupa Password' di halaman login\n2. Masukkan email terdaftar\n3. Cek email untuk link reset\n4. Buat password baru\n\nJika masih kesulitan, hubungi kami.",
                'Invoice Overdue' => "Kepada Yth. Bapak/Ibu,\n\nKami informasikan bahwa faktur {{invoice_number}} dengan jumlah {{total}} telah melewati jatuh tempo. Mohon segera dilakukan pembayaran.\n\nTerima kasih.",
                'Terima Kasih' => "Halo,\n\nTerima kasih atas feedback Anda. Kami sangat menghargai masukan untuk meningkatkan layanan kami.\n\nSalam.",
                'Tiket Selesai' => "Halo,\n\nTiket ini telah kami selesaikan. Jika masih ada pertanyaan, jangan ragu untuk membuka tiket baru.\n\nTerima kasih.",
                'Info Produk' => "Halo,\n\nBerikut informasi produk yang Anda tanyakan:\n\nCRM Office adalah solusi CRM self-hosted yang mencakup manajemen klien, prospek, faktur, proyek, support ticket, dan knowledge base.\n\nKunjungi website kami untuk informasi lebih lengkap.",
                'Demo Request' => "Halo,\n\nTerima kasih atas ketertarikan Anda. Kami akan menghubungi Anda dalam 1x24 jam untuk menjadwalkan demo.\n\nSalam,\nTim Sales",
                'Kendala Teknis' => "Halo,\n\nMohon maaf atas ketidaknyamanan yang terjadi. Tim teknis kami sedang menyelidiki masalah ini dan akan memberikan update segera.\n\nSalam.",
                'Onboarding Klien Baru' => "Selamat datang di CRM Office!\n\nBerikut langkah awal untuk memulai:\n1. Lengkapi profil perusahaan\n2. Tambahkan anggota tim\n3. Import data klien\n4. Atur preferensi dan workflow\n\nHubungi kami jika perlu bantuan.",
            ];

            $categories = ['Support', 'Sales', 'Billing', 'Onboarding', 'Technical'];
            $titles = array_keys($templates);
            $contents = array_values($templates);

            for ($i = 0; $i < 50; $i++) {
                $idx = $i % count($titles);
                $suffix = $i >= count($titles) ? ' (v' . ceil(($i + 1) / count($titles)) . ')' : '';

                $responses[] = [
                    'title' => $titles[$idx] . $suffix,
                    'content' => $contents[$idx],
                    'category' => $categories[$idx % count($categories)],
                    'department_id' => $this->faker->randomElement([...$this->departmentIds, null]),
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'is_shared' => $this->faker->boolean(70),
                    'created_at' => now()->subDays(rand(0, 90)),
                    'updated_at' => now(),
                ];
            }
            $this->chunk(CannedResponse::class, $responses);
        }
    }

    // ─── EXPENSES ────────────────────────────────────────────────

    protected function seedExpenses(): void
    {
        if (Expense::count() < 500) {
            $existing = Expense::count();
            $target = 500;
            $expenses = [];
            $this->progressStart($target - $existing);

            $vendors = [
                'PT Telkom Indonesia', 'PT PLN Persero', 'Google Cloud', 'Amazon AWS',
                'DigitalOcean', 'PT Pos Indonesia', 'GoTo Group', 'PT JNE Express',
                'PT Unilever', 'PT Indofood', 'PT Astra International',
            ];

            for ($i = $existing + 1; $i <= $target; $i++) {
                $expenses[] = [
                    'expense_category_id' => $this->faker->randomElement($this->expenseCategoryIds),
                    'client_id' => $this->faker->boolean(40) ? $this->faker->randomElement($this->clientIds) : null,
                    'project_id' => $this->faker->boolean(25) ? $this->faker->randomElement($this->projectIds) : null,
                    'vendor' => $this->faker->randomElement($vendors),
                    'description' => 'Biaya: ' . $this->faker->sentence,
                    'amount' => $this->randomIdr(50_000, 50_000_000),
                    'currency_id' => $this->currencyIds[0],
                    'tax_rate_id' => $this->faker->randomElement([null, ...$this->taxRateIds]),
                    'expense_date' => now()->subDays(rand(0, 365)),
                    'is_billable' => $this->faker->boolean(35),
                    'is_invoiced' => false,
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(0, 365)),
                    'updated_at' => now(),
                ];

                if (count($expenses) >= self::CHUNK || $i === $target) {
                    $this->chunk(Expense::class, $expenses);
                    $this->progressAdvance(count($expenses));
                    $expenses = [];
                }
            }
            $this->progressFinish();
        }
    }
}
