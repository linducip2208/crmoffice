<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Task;
use App\Models\TaxRate;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    use WithoutModelEvents;

    protected array $userIds = [];
    protected array $clientIds = [];
    protected array $contactIds = [];
    protected array $projectIds = [];
    protected array $taskIds = [];
    protected array $currencyIds = [];
    protected array $taxRateIds = [];
    protected array $leadSourceIds = [];
    protected array $leadStatusIds = [];
    protected array $departmentIds = [];
    protected array $ticketPriorityIds = [];
    protected array $ticketStatusIds = [];
    protected array $milestoneIds = [];

    protected $faker;

    public function run(): void
    {
        if (!env('APP_DEMO_SEED', false)) {
            if ($this->command) {
                $this->command->warn('Skipping demo content. Set APP_DEMO_SEED=true in .env to enable.');
            }
            return;
        }

        $this->faker = fake('id_ID');

        DB::disableQueryLog();

        DB::transaction(function () {
            $this->loadUsers();
            $this->ensureReferenceData();
            $this->seedClientsAndContacts();
            $this->seedLeads();
            $this->seedInvoices();
            $this->seedEstimates();
            $this->seedProjects();
            $this->seedTickets();
            $this->seedKbArticles();
            $this->seedBlogPosts();
            $this->seedSurveys();
            $this->seedAnnouncements();
            $this->seedCalendarEvents();
            $this->seedTimeEntries();
        });

        if ($this->command) {
            $this->command->info('DemoContentSeeder: 10 clients, 20 contacts, 50 leads, 30 invoices, 10 estimates, 5 projects, 20 tickets, 10 KB articles, 3 blog posts, 3 surveys, 5 announcements, 10 events, time entries seeded.');
        }
    }

    protected function loadUsers(): void
    {
        $this->userIds = User::pluck('id')->toArray();
        if (empty($this->userIds)) {
            throw new \RuntimeException('No users found. Run OwnerUserSeeder and DemoUsersSeeder first.');
        }
    }

    protected function ensureReferenceData(): void
    {
        if (Currency::count() === 0) {
            Currency::create(['code' => 'IDR', 'symbol' => 'Rp', 'name' => 'Rupiah', 'is_base' => true]);
            Currency::create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar']);
        }
        $this->currencyIds = Currency::pluck('id')->toArray();

        if (TaxRate::count() === 0) {
            TaxRate::create(['name' => 'PPN 11%', 'percentage' => 11, 'is_active' => true]);
        }
        $this->taxRateIds = TaxRate::where('is_active', true)->pluck('id')->toArray();

        if (LeadStatus::count() === 0) {
            foreach ([
                ['name' => 'Baru', 'order' => 1],
                ['name' => 'Dihubungi', 'order' => 2],
                ['name' => 'Terkualifikasi', 'order' => 3],
                ['name' => 'Proposal', 'order' => 4],
                ['name' => 'Negosiasi', 'order' => 5],
                ['name' => 'Menang', 'order' => 6, 'is_won' => true],
                ['name' => 'Kalah', 'order' => 7, 'is_lost' => true],
            ] as $s) {
                LeadStatus::create($s);
            }
        }
        $this->leadStatusIds = LeadStatus::pluck('id')->toArray();

        if (LeadSource::count() === 0) {
            foreach (['Website', 'Referral', 'LinkedIn', 'WhatsApp', 'Email', 'Event', 'Iklan FB', 'Iklan Google'] as $n) {
                LeadSource::create(['name' => $n, 'is_active' => true]);
            }
        }
        $this->leadSourceIds = LeadSource::pluck('id')->toArray();

        if (Department::count() === 0) {
            foreach (['Support', 'Sales', 'Teknis', 'Finance', 'Umum'] as $n) {
                Department::create(['name' => $n, 'is_active' => true]);
            }
        }
        $this->departmentIds = Department::pluck('id')->toArray();

        if (TicketPriority::count() === 0) {
            foreach ([['name' => 'Rendah', 'order' => 1], ['name' => 'Sedang', 'order' => 2], ['name' => 'Tinggi', 'order' => 3], ['name' => 'Kritis', 'order' => 4]] as $p) {
                TicketPriority::create($p);
            }
        }
        $this->ticketPriorityIds = TicketPriority::pluck('id')->toArray();

        if (TicketStatus::count() === 0) {
            foreach ([['name' => 'Buka', 'order' => 1], ['name' => 'Proses', 'order' => 2], ['name' => 'Menunggu Klien', 'order' => 3], ['name' => 'Selesai', 'order' => 4], ['name' => 'Tutup', 'order' => 5]] as $s) {
                TicketStatus::create($s);
            }
        }
        $this->ticketStatusIds = TicketStatus::pluck('id')->toArray();
    }

    protected function seedClientsAndContacts(): void
    {
        $companies = [
            'CV Maju Jaya', 'PT Teknologi Nusantara', 'UD Sumber Makmur',
            'PT Mitra Solusi Digital', 'CV Karya Bersama', 'PT Bumi Pertiwi Sejahtera',
            'UD Berkah Abadi', 'PT Sinergi Informatika', 'CV Harapan Baru',
            'PT Mandiri Teknologi',
        ];

        $industries = ['Teknologi', 'Manufaktur', 'Ritel', 'Konstruksi', 'Konsultan', 'Pendidikan', 'Kesehatan', 'Keuangan', 'Logistik', 'E-commerce'];
        $cities = ['Jakarta Selatan', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Yogyakarta', 'Tangerang', 'Bekasi', 'Depok', 'Makassar'];
        $addresses = [
            'Jl. Sudirman No. 12', 'Jl. Thamrin No. 45', 'Jl. Gatot Subroto No. 78',
            'Jl. Rasuna Said No. 33', 'Jl. HR Rasuna Said Kav. 5', 'Jl. MH Thamrin No. 90',
            'Jl. Cikini Raya No. 21', 'Jl. Kemang Raya No. 56', 'Jl. Fatmawati No. 67',
            'Jl. Pondok Indah No. 88',
        ];

        $clients = [];
        foreach ($companies as $i => $name) {
            $clients[] = [
                'company_name' => $name,
                'industry' => $industries[$i],
                'website' => 'https://' . Str::slug($name) . '.co.id',
                'phone' => '021-' . rand(1000, 9999) . rand(1000, 9999),
                'billing_address' => $addresses[$i],
                'billing_city' => $cities[array_rand($cities)],
                'billing_state' => 'DKI Jakarta',
                'billing_country' => 'ID',
                'billing_postal' => rand(10000, 19999),
                'tax_id' => '0' . rand(10, 99) . '.' . rand(100, 999) . '.' . rand(100, 999) . '.' . rand(1, 9) . '-000.' . rand(100, 999),
                'account_manager_id' => $this->faker->randomElement($this->userIds),
                'default_currency_id' => $this->currencyIds[0],
                'default_language' => 'id',
                'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
                'notes' => 'Klien sejak ' . date('Y', strtotime('-' . rand(1, 5) . ' years')),
                'created_at' => now()->subDays(rand(30, 365)),
                'updated_at' => now(),
            ];
        }
        Client::insert($clients);
        $this->clientIds = Client::pluck('id')->toArray();

        $indonesianFirstNames = ['Budi', 'Andi', 'Rina', 'Sari', 'Dewi', 'Eko', 'Fitri', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Maya', 'Nina', 'Oki', 'Putri', 'Qori', 'Rudi', 'Sinta', 'Tono'];
        $indonesianLastNames = ['Santoso', 'Wijaya', 'Hartono', 'Kusuma', 'Pratama', 'Putra', 'Hidayat', 'Saputra', 'Nugroho', 'Wibowo', 'Setiawan', 'Susanto', 'Gunawan', 'Mahendra', 'Pangestu'];
        $positions = ['Direktur', 'Manager', 'Staff IT', 'Finance', 'Marketing', 'HRD', 'Operasional', 'Customer Service'];

        $contacts = [];
        foreach ($this->clientIds as $ci => $cid) {
            for ($j = 0; $j < 2; $j++) {
                $firstName = $this->faker->randomElement($indonesianFirstNames);
                $lastName = $this->faker->randomElement($indonesianLastNames);
                $contacts[] = [
                    'client_id' => $cid,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => strtolower($firstName . '.' . $lastName . rand(10, 99) . '@demo.local'),
                    'phone' => '081' . rand(10000000, 99999999),
                    'position' => $positions[array_rand($positions)],
                    'is_primary' => $j === 0,
                    'portal_access' => $this->faker->boolean(40),
                    'password' => bcrypt('password'),
                    'locale' => 'id',
                    'created_at' => now()->subDays(rand(30, 365)),
                    'updated_at' => now(),
                ];
            }
        }
        Contact::insert($contacts);
        $this->contactIds = Contact::pluck('id')->toArray();
    }

    protected function seedLeads(): void
    {
        $companies = ['PT Digital Kreatif', 'CV Solusi Tekno', 'UD Prima Jaya', 'PT Inovasi Nusa', 'CV Global Media'];
        $names = $this->faker->name();

        $leads = [];
        for ($i = 0; $i < 50; $i++) {
            $statusId = $this->faker->randomElement($this->leadStatusIds);
            $leads[] = [
                'name' => $this->faker->name(),
                'company' => $this->faker->randomElement($companies),
                'email' => 'lead' . ($i + 1) . '@demo.local',
                'phone' => '081' . rand(10000000, 99999999),
                'website' => 'https://demo-lead' . ($i + 1) . '.co.id',
                'city' => $this->faker->city(),
                'country' => 'ID',
                'estimated_value' => $this->faker->randomFloat(2, 500000, 50000000),
                'currency_id' => $this->currencyIds[0],
                'lead_source_id' => $this->faker->randomElement($this->leadSourceIds),
                'lead_status_id' => $statusId,
                'assigned_to' => $this->faker->randomElement($this->userIds),
                'description' => 'Prospek dari ' . $this->faker->randomElement(['website', 'iklan FB', 'LinkedIn', 'referral']) . '. Tertarik dengan layanan CRM.',
                'expected_close' => now()->addDays(rand(7, 90)),
                'last_activity_at' => now()->subDays(rand(0, 30)),
                'created_at' => now()->subDays(rand(1, 180)),
                'updated_at' => now(),
            ];
        }
        Lead::insert($leads);
    }

    protected function seedInvoices(): void
    {
        $statuses = ['draft', 'draft', 'sent', 'sent', 'sent', 'partial', 'paid', 'paid', 'paid', 'overdue'];
        $invoices = [];

        for ($i = 0; $i < 30; $i++) {
            $subtotal = $this->faker->randomFloat(2, 500000, 50000000);
            $taxTotal = round($subtotal * 0.11, 2);
            $total = $subtotal + $taxTotal;
            $status = $statuses[array_rand($statuses)];
            $paidTotal = match ($status) {
                'paid' => $total,
                'partial' => round($total * $this->faker->randomFloat(2, 0.3, 0.7), 2),
                default => 0,
            };
            $invoiceDate = now()->subDays(rand(0, 90));
            $invoices[] = [
                'number' => 'INV-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'project_id' => $this->faker->boolean(30) ? $this->faker->randomElement($this->projectIds) : null,
                'invoice_date' => $invoiceDate,
                'due_date' => (clone $invoiceDate)->addDays(rand(14, 30)),
                'currency_id' => $this->currencyIds[0],
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => $taxTotal,
                'total' => $total,
                'paid_total' => $paidTotal,
                'balance_due' => $total - $paidTotal,
                'status' => $status,
                'is_recurring' => $this->faker->boolean(10),
                'notes' => 'Pembayaran untuk layanan ' . $this->faker->randomElement(['Pengembangan Web', 'Konsultasi IT', 'Maintenance Bulanan', 'Desain UI/UX', 'SEO']),
                'terms' => 'Pembayaran dalam 14 hari',
                'public_token' => Str::random(40),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => $invoiceDate,
                'updated_at' => now(),
            ];
        }
        Invoice::insert($invoices);
    }

    protected function seedEstimates(): void
    {
        $statuses = ['draft', 'draft', 'sent', 'sent', 'sent', 'accepted', 'accepted', 'declined', 'expired', 'draft'];
        $estimates = [];

        for ($i = 0; $i < 10; $i++) {
            $subtotal = $this->faker->randomFloat(2, 1000000, 30000000);
            $taxTotal = round($subtotal * 0.11, 2);
            $total = $subtotal + $taxTotal;
            $estimates[] = [
                'number' => 'EST-' . date('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'client_id' => $this->faker->randomElement($this->clientIds),
                'estimate_date' => now()->subDays(rand(0, 60)),
                'expiry_date' => now()->addDays(rand(7, 30)),
                'currency_id' => $this->currencyIds[0],
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => $taxTotal,
                'total' => $total,
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'Estimasi biaya untuk project ' . $this->faker->randomElement(['Website', 'Mobile App', 'Sistem ERP', 'Dashboard Admin', 'Integrasi API']),
                'public_token' => Str::random(40),
                'created_by' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now(),
            ];
        }
        Estimate::insert($estimates);
    }

    protected function seedProjects(): void
    {
        $projectNames = [
            'Website Company Profile', 'Sistem Inventory Gudang', 'Mobile App E-commerce',
            'Dashboard Analytics', 'Integrasi Payment Gateway',
        ];
        $statuses = ['not_started', 'in_progress', 'in_progress', 'in_progress', 'completed'];
        $billingMethods = ['fixed', 'hourly', 'milestone', 'fixed', 'fixed'];

        $projects = [];
        for ($i = 0; $i < 5; $i++) {
            $startDate = now()->subDays(rand(10, 180));
            $projects[] = [
                'name' => $projectNames[$i],
                'description' => 'Project ' . $projectNames[$i] . ' untuk klien. Mencakup analisis kebutuhan, pengembangan, testing, dan deployment.',
                'client_id' => $this->clientIds[$i],
                'project_manager_id' => $this->faker->randomElement($this->userIds),
                'start_date' => $startDate,
                'deadline' => (clone $startDate)->addDays(rand(30, 120)),
                'estimate_hours' => $this->faker->randomFloat(2, 40, 300),
                'billing_method' => $billingMethods[$i],
                'fixed_price' => $billingMethods[$i] === 'fixed' ? $this->faker->randomFloat(2, 5000000, 100000000) : null,
                'hourly_rate' => $billingMethods[$i] === 'hourly' ? $this->faker->randomFloat(2, 100000, 500000) : null,
                'currency_id' => $this->currencyIds[0],
                'status' => $statuses[$i],
                'progress_pct' => match ($statuses[$i]) {
                    'completed' => 100,
                    'in_progress' => rand(30, 80),
                    'not_started' => 0,
                    default => 0,
                },
                'is_visible_to_customer' => true,
                'created_at' => $startDate,
                'updated_at' => now(),
            ];
        }
        Project::insert($projects);
        $this->projectIds = Project::pluck('id')->toArray();

        $milestoneNames = [
            ['Analisis Kebutuhan', 'Desain UI/UX', 'Development Backend', 'Testing QA'],
            ['Setup Database', 'API Development', 'Frontend Integration', 'Deployment'],
            ['Wireframe', 'Prototype', 'Development', 'UAT'],
            ['Data Collection', 'Dashboard Build', 'Integration', 'Go Live'],
            ['Requirement', 'Configuration', 'Testing', 'Training'],
        ];

        foreach ($this->projectIds as $pi => $pid) {
            $milestones = [];
            foreach ($milestoneNames[$pi] as $j => $mn) {
                $milestones[] = [
                    'project_id' => $pid,
                    'name' => $mn,
                    'description' => 'Milestone ' . ($j + 1) . ' untuk project ' . $projectNames[$pi],
                    'due_date' => now()->addDays(rand(7, 90)),
                    'order' => $j + 1,
                    'complete_pct' => rand(0, 100),
                    'created_at' => now()->subDays(rand(10, 60)),
                    'updated_at' => now(),
                ];
            }
            Milestone::insert($milestones);
        }
        $this->milestoneIds = Milestone::pluck('id')->toArray();

        $taskStatuses = ['todo', 'in_progress', 'review', 'done'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $taskTemplates = [
            ['Buat requirement document', 'Setup development environment', 'Desain database schema', 'Buat REST API endpoint', 'Implementasi autentikasi', 'Buat halaman dashboard', 'Implementasi fitur pencarian', 'Buat laporan PDF', 'Integrasi email notifikasi', 'Setup CI/CD pipeline'],
            ['Analisis flow gudang', 'Desain tabel inventory', 'Buat form input barang', 'Implementasi barcode scanner', 'Buat laporan stok', 'Setup role akses gudang', 'Integrasi printer label', 'Testing akurasi stok', 'Buat dashboard gudang', 'Dokumentasi user manual'],
            ['Desain wireframe app', 'Setup Flutter project', 'Buat halaman login', 'Implementasi product catalog', 'Buat shopping cart', 'Integrasi payment gateway', 'Implementasi push notif', 'Testing di Android & iOS', 'Optimasi performa', 'Submit ke App Store & Play Store'],
            ['Kumpulkan data source', 'Desain skema analytics', 'Buat data pipeline ETL', 'Implementasi chart library', 'Buat filter interaktif', 'Setup scheduled refresh', 'Implementasi export CSV', 'Buat user management', 'Testing akurasi data', 'Deploy dashboard ke production'],
            ['Analisis kebutuhan payment', 'Setup akun payment gateway', 'Integrasi API Midtrans', 'Implementasi QRIS', 'Buat halaman checkout', 'Testing simulasi bayar', 'Handle callback notifikasi', 'Implementasi refund flow', 'Buat laporan transaksi', 'Dokumentasi teknis'],
        ];

        foreach ($this->projectIds as $pi => $pid) {
            $projectMilestones = Milestone::where('project_id', $pid)->pluck('id')->toArray();
            $tasks = [];
            for ($j = 0; $j < 10; $j++) {
                $status = $this->faker->randomElement($taskStatuses);
                $tasks[] = [
                    'project_id' => $pid,
                    'milestone_id' => $this->faker->randomElement($projectMilestones),
                    'title' => $taskTemplates[$pi][$j],
                    'description' => 'Task: ' . $taskTemplates[$pi][$j] . '. Detail implementasi akan didiskusikan di daily standup.',
                    'priority' => $this->faker->randomElement($priorities),
                    'status' => $status,
                    'start_date' => now()->subDays(rand(0, 30)),
                    'due_date' => now()->addDays(rand(1, 60)),
                    'estimate_hours' => $this->faker->randomFloat(2, 2, 40),
                    'is_billable' => $this->faker->boolean(70),
                    'hourly_rate' => $this->faker->randomFloat(2, 100000, 350000),
                    'is_visible_to_customer' => $this->faker->boolean(30),
                    'order' => $j + 1,
                    'completed_at' => $status === 'done' ? now() : null,
                    'created_by' => $this->faker->randomElement($this->userIds),
                    'created_at' => now()->subDays(rand(10, 90)),
                    'updated_at' => now(),
                ];
            }
            Task::insert($tasks);

            $taskIds = Task::where('project_id', $pid)->pluck('id')->toArray();
            foreach ($taskIds as $tid) {
                $assignees = [];
                $assigneeCount = rand(1, 2);
                $usedUids = [];
                for ($k = 0; $k < $assigneeCount; $k++) {
                    do {
                        $uid = $this->faker->randomElement($this->userIds);
                    } while (in_array($uid, $usedUids));
                    $usedUids[] = $uid;
                    $assignees[] = [
                        'task_id' => $tid,
                        'user_id' => $uid,
                        'assigned_at' => now()->subDays(rand(0, 30)),
                    ];
                }
                DB::table('task_assignees')->insert($assignees);
            }
        }
        $this->taskIds = Task::pluck('id')->toArray();
    }

    protected function seedTickets(): void
    {
        $subjects = [
            'Tidak bisa login ke portal', 'Invoice tidak muncul di dashboard',
            'Error saat upload file', 'Performa lambat di jam sibuk',
            'Permintaan reset password', 'Fitur export tidak berfungsi',
            'Integrasi email gagal', 'Data client tidak tersimpan',
            'Notifikasi tidak masuk', 'Permintaan penambahan user baru',
            'Bug di halaman laporan', 'SLA approaching untuk ticket #45',
            'Permintaan custom report', 'Server down sejak jam 08:00',
            'Migrasi data dari CRM lama', 'Training untuk tim baru',
            'Update payment method', 'Akses API ditolak',
            'Domain whitelist error', 'Backup database gagal',
        ];

        $bodies = [
            'User melaporkan tidak bisa login menggunakan akun portal. Sudah mencoba reset password tetapi link reset tidak masuk ke email.',
            'Invoice yang seharusnya muncul di dashboard klien tidak tampil. Total ada 3 invoice yang hilang.',
            'Saat mencoba upload file PDF ukuran 5MB, muncul error "File terlalu besar". Padahal setting maksimal 10MB.',
            'Aplikasi terasa lambat terutama di jam 10:00-12:00 WIB. Loading dashboard butuh > 10 detik.',
            'Beberapa user lupa password dan tombol reset tidak mengirimkan email. Perlu diinvestigasi konfigurasi SMTP.',
        ];

        $tickets = [];
        for ($i = 0; $i < 20; $i++) {
            $tickets[] = [
                'number' => 'T-' . date('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'subject' => $subjects[$i],
                'body' => $bodies[array_rand($bodies)],
                'client_id' => $this->faker->randomElement($this->clientIds),
                'contact_id' => $this->faker->boolean(50) ? $this->faker->randomElement($this->contactIds) : null,
                'department_id' => $this->faker->randomElement($this->departmentIds),
                'priority_id' => $this->faker->randomElement($this->ticketPriorityIds),
                'status_id' => $this->faker->randomElement($this->ticketStatusIds),
                'assigned_to' => $this->faker->randomElement($this->userIds),
                'created_at' => now()->subDays(rand(0, 60)),
                'updated_at' => now(),
            ];
        }
        Ticket::insert($tickets);
    }

    protected function seedKbArticles(): void
    {
        $cat = KbCategory::firstOrCreate(
            ['slug' => 'panduan-crm'],
            ['name' => 'Panduan CRM', 'description' => 'Panduan penggunaan CRM Office', 'is_public' => true, 'order' => 1]
        );

        $articles = [
            ['Cara Menambahkan Klien Baru', 'Panduan langkah demi langkah untuk menambahkan klien baru ke dalam sistem CRM. Mulai dari mengisi data perusahaan, kontak person, hingga mengatur preferensi billing.'],
            ['Mengelola Pipeline Prospek', 'Cara mengelola prospek dari tahap awal hingga closing. Termasuk tips mengatur lead status, meng-assign sales, dan memonitor konversi.'],
            ['Membuat Faktur Profesional', 'Panduan membuat faktur dengan template profesional. Mencakup pengaturan item, pajak PPN 11%, diskon, dan pengiriman faktur via email ke klien.'],
            ['Setup Proyek & Manajemen Tugas', 'Cara membuat proyek baru, menambahkan milestone, meng-assign tugas ke tim, dan memonitor progres melalui Gantt chart.'],
            ['Konfigurasi Helpdesk & SLA', 'Panduan mengatur departemen support, prioritas ticket, kebijakan SLA, dan canned responses untuk mempercepat balasan ticket.'],
            ['Integrasi Provider Payment', 'Cara menghubungkan payment gateway ke CRM. Dukung Midtrans, Xendit, dan provider lain via format-based adapter.'],
            ['Export Data ke Excel & PDF', 'Panduan lengkap export data ke format Excel dan PDF untuk laporan, backup, dan keperluan audit.'],
            ['Mengelola Basis Pengetahuan', 'Tips membuat dan mengelola artikel knowledge base yang efektif untuk customer self-service dan SEO.'],
            ['Setup Notifikasi Email & Push', 'Cara mengkonfigurasi notifikasi email dan push notification agar tim selalu update dengan aktivitas terbaru.'],
            ['Keamanan & Manajemen User', 'Panduan mengelola user, role, permission, dan pengaturan keamanan seperti 2FA dan IP whitelist.'],
        ];

        foreach ($articles as $i => [$title, $excerpt]) {
            $content = '<h2>' . $title . '</h2>';
            $content .= '<p>' . $excerpt . '</p>';
            $content .= '<p>CRM Office dirancang untuk memudahkan bisnis Anda mengelola hubungan pelanggan. Artikel ini akan memandu Anda melalui setiap langkah dengan jelas.</p>';
            $content .= '<h3>Langkah-langkah</h3><ol>';
            for ($j = 1; $j <= 5; $j++) {
                $content .= '<li>Langkah ke-' . $j . ': ' . $this->faker->sentence() . '</li>';
            }
            $content .= '</ol>';
            $content .= '<p>Jika mengalami kendala, silakan hubungi tim support melalui ticket atau email ke support@crmoffice.local.</p>';

            KbArticle::create([
                'category_id' => $cat->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => $excerpt,
                'content' => $content,
                'is_published' => true,
                'view_count' => rand(50, 2000),
                'helpful_count' => rand(5, 100),
                'unhelpful_count' => rand(0, 20),
                'author_id' => $this->faker->randomElement($this->userIds),
                'published_at' => now()->subDays(rand(1, 180)),
            ]);
        }
    }

    protected function seedBlogPosts(): void
    {
        $cat = BlogCategory::firstOrCreate(
            ['slug' => 'tips-crm'],
            ['name' => 'Tips CRM', 'description' => 'Tips dan trik penggunaan CRM']
        );

        $posts = [
            [
                'title' => '5 Tips Memaksimalkan CRM untuk Bisnis Kecil',
                'excerpt' => 'CRM bukan hanya untuk perusahaan besar. Pelajari 5 tips praktis menggunakan CRM untuk bisnis kecil Anda agar lebih efisien dan terorganisir.',
                'content' => '<p>Banyak pemilik bisnis kecil berpikir CRM hanya untuk perusahaan besar. Padahal, justru bisnis kecil yang paling diuntungkan dengan CRM — karena setiap pelanggan sangat berharga dan tidak boleh ada yang terlewat.</p><p>Berikut 5 tips praktis: (1) Mulai dari data klien yang bersih, (2) Otomatisasi follow-up lead, (3) Gunakan template faktur, (4) Aktifkan customer portal untuk self-service, (5) Pantau laporan mingguan untuk insight bisnis.</p>',
            ],
            [
                'title' => 'Kenapa Bisnis Indonesia Perlu Beralih dari Excel ke CRM',
                'excerpt' => 'Excel sudah tidak cukup untuk mengelola pertumbuhan bisnis. Temukan alasan kenapa CRM adalah investasi yang menguntungkan untuk jangka panjang.',
                'content' => '<p>Excel adalah alat yang hebat — untuk spreadsheet. Tapi ketika bisnis Anda tumbuh, data pelanggan tersebar di puluhan file, follow-up terlewat, dan laporan keuangan tidak akurat.</p><p>CRM modern seperti CRM Office mengintegrasikan semuanya: kontak, prospek, faktur, proyek, dan support ticket dalam satu platform. Tidak ada lagi data yang hilang atau terduplikasi.</p>',
            ],
            [
                'title' => 'Panduan Memilih CRM Self-Hosted untuk Keamanan Data',
                'excerpt' => 'Self-hosted CRM memberi Anda kontrol penuh atas data pelanggan. Berikut panduan memilih CRM self-hosted yang tepat untuk kebutuhan keamanan data bisnis Anda.',
                'content' => '<p>Dengan UU PDP yang mulai berlaku, keamanan data pelanggan semakin penting. CRM self-hosted memastikan data Anda tetap di server sendiri — tidak dikirim ke server pihak ketiga di luar negeri.</p><p>CRM Office adalah solusi self-hosted yang bisa Anda deploy di VPS sendiri. Data tetap di Indonesia, Anda punya akses penuh ke database, dan tidak ada biaya langganan per bulan.</p>',
            ],
        ];

        foreach ($posts as $i => $post) {
            BlogPost::create([
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'content' => $post['content'],
                'excerpt' => $post['excerpt'],
                'category_id' => $cat->id,
                'author_id' => $this->faker->randomElement($this->userIds),
                'published_at' => now()->subDays(($i + 1) * 15),
                'is_published' => true,
                'meta_title' => $post['title'],
                'meta_description' => $post['excerpt'],
            ]);
        }
    }

    protected function seedSurveys(): void
    {
        $surveyData = [
            ['Kepuasan Pelanggan Q1', 'Survei kepuasan pelanggan untuk kuartal pertama tahun ' . date('Y')],
            ['Feedback Layanan Support', 'Kami ingin mendengar pengalaman Anda dengan tim support kami'],
            ['Survey Kebutuhan Fitur Baru', 'Bantu kami menentukan prioritas fitur yang paling Anda butuhkan'],
        ];

        foreach ($surveyData as $i => [$title, $desc]) {
            $survey = Survey::create([
                'title' => $title,
                'description' => $desc,
                'audience' => 'all_clients',
                'public_token' => Str::random(32),
                'is_active' => true,
                'starts_at' => now()->subDays(rand(0, 30)),
                'ends_at' => now()->addDays(rand(14, 60)),
                'created_by' => $this->faker->randomElement($this->userIds),
            ]);

            $questions = [
                ['Seberapa puas Anda dengan layanan kami?', 'rating', true],
                ['Apa yang paling Anda sukai dari CRM ini?', 'text', true],
                ['Fitur apa yang paling sering Anda gunakan?', 'multiple_choice', true],
                ['Apakah Anda akan merekomendasikan kami ke rekan?', 'yes_no', true],
                ['Saran untuk perbaikan:', 'text', false],
            ];

            foreach ($questions as $j => [$q, $type, $required]) {
                SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'question' => $q,
                    'type' => $type,
                    'options' => $type === 'multiple_choice' ? json_encode(['CRM', 'Faktur', 'Proyek', 'Support', 'Laporan']) : null,
                    'is_required' => $required,
                    'order' => $j + 1,
                ]);
            }

            $questionIds = SurveyQuestion::where('survey_id', $survey->id)->pluck('id')->toArray();

            for ($k = 0; $k < rand(3, 6); $k++) {
                $response = SurveyResponse::create([
                    'survey_id' => $survey->id,
                    'contact_id' => $this->faker->randomElement($this->contactIds),
                    'anonymous_token' => Str::random(16),
                    'ip_address' => $this->faker->ipv4(),
                    'submitted_at' => now()->subDays(rand(0, 20)),
                ]);

                foreach ($questionIds as $qid) {
                    $question = SurveyQuestion::find($qid);
                    $answer = match ($question->type) {
                        'rating' => (string) rand(1, 5),
                        'yes_no' => $this->faker->randomElement(['Ya', 'Tidak']),
                        'multiple_choice' => $this->faker->randomElement(['CRM', 'Faktur', 'Proyek', 'Support']),
                        'text' => $this->faker->sentence(),
                        default => $this->faker->word(),
                    };
                    SurveyAnswer::create([
                        'response_id' => $response->id,
                        'question_id' => $qid,
                        'answer' => $answer,
                    ]);
                }
            }
        }
    }

    protected function seedAnnouncements(): void
    {
        $announcements = [
            ['Pemeliharaan Sistem Terjadwal', 'Kami akan melakukan pemeliharaan sistem pada hari Sabtu, pukul 02:00-04:00 WIB. Layanan mungkin tidak tersedia selama periode ini.', 'all'],
            ['Fitur Baru: Export PDF untuk Laporan', 'Sekarang Anda bisa mengekspor semua laporan ke format PDF. Fitur ini tersedia di menu Laporan > Export PDF.', 'staff'],
            ['Update Kebijakan Privasi', 'Kebijakan privasi kami telah diperbarui untuk mematuhi UU PDP. Silakan baca perubahan di halaman Kebijakan Privasi.', 'customers'],
            ['Selamat Datang Tim Baru!', 'Kami menyambut 3 anggota tim baru di departemen support. Tim support sekarang tersedia lebih cepat!', 'staff'],
            ['Promo Akhir Tahun: Diskon Setup 20%', 'Dapatkan diskon 20% untuk paket Growth setup sampai 31 Desember. Hubungi sales untuk info lebih lanjut.', 'customers'],
        ];

        foreach ($announcements as $i => [$title, $body, $audience]) {
            Announcement::create([
                'title' => $title,
                'body' => $body,
                'audience' => $audience,
                'author_id' => $this->faker->randomElement($this->userIds),
                'publish_at' => now()->subDays(rand(0, 14)),
                'expires_at' => now()->addDays(rand(7, 60)),
            ]);
        }
    }

    protected function seedCalendarEvents(): void
    {
        $events = [
            ['Deadline Project Website', 'Final deadline untuk deliver website company profile ke klien', false],
            ['Meeting Weekly Review', 'Weekly review meeting dengan tim development', false],
            ['Training CRM untuk Tim Baru', 'Training penggunaan CRM Office untuk anggota tim baru', false],
            ['Pitching ke Calon Klien', 'Presentasi proposal ke PT Teknologi Nusantara', false],
            ['Deadline Laporan Keuangan', 'Batas akhir submit laporan keuangan bulanan', false],
            ['Standup Harian', 'Daily standup 15 menit untuk update progress', false],
            ['Webinar: Optimasi CRM', 'Webinar gratis tentang cara mengoptimalkan penggunaan CRM untuk bisnis', false],
            ['Libur Nasional', 'Hari libur nasional — kantor tutup', true],
            ['Sprint Planning', 'Sprint planning untuk 2 minggu ke depan', false],
            ['Demo Produk ke Klien', 'Demo produk ke CV Maju Jaya', false],
        ];

        $colors = ['#4f46e5', '#22c55e', '#ef4444', '#f97316', '#8b5cf6', '#eab308', '#06b6d4', '#ec4899'];

        foreach ($events as $i => [$title, $desc, $allDay]) {
            $start = $i < 5 ? now()->addDays(rand(1, 14))->setHour(rand(8, 16))->setMinute(0) :
                now()->subDays(rand(0, 14))->setHour(rand(8, 16))->setMinute(0);

            CalendarEvent::create([
                'user_id' => $this->faker->randomElement($this->userIds),
                'title' => $title,
                'description' => $desc,
                'starts_at' => $start,
                'ends_at' => $allDay ? (clone $start)->addDay() : (clone $start)->addHours(rand(1, 3)),
                'all_day' => $allDay,
                'color' => $colors[array_rand($colors)],
                'reminder_minutes_before' => $this->faker->randomElement([10, 15, 30, 60, null]),
            ]);
        }
    }

    protected function seedTimeEntries(): void
    {
        if (empty($this->taskIds)) {
            return;
        }

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
        ];

        $timeEntries = [];
        $taskCount = min(count($this->taskIds), 30);

        for ($i = 0; $i < 50; $i++) {
            $taskId = $this->taskIds[array_rand($this->taskIds)];
            $task = Task::find($taskId);
            $start = now()->subHours(rand(1, 336));
            $minutes = rand(30, 480);
            $end = (clone $start)->addMinutes($minutes);

            $timeEntries[] = [
                'task_id' => $taskId,
                'project_id' => $task?->project_id ?? $this->projectIds[0],
                'user_id' => $this->faker->randomElement($this->userIds),
                'start_at' => $start,
                'end_at' => $end,
                'minutes' => $minutes,
                'hourly_rate' => $this->faker->randomFloat(2, 100000, 350000),
                'is_billable' => $this->faker->boolean(75),
                'is_invoiced' => $this->faker->boolean(20),
                'note' => $this->faker->randomElement($descriptions),
            ];
        }

        TimeEntry::insert($timeEntries);
    }
}
