<?php

namespace Database\Seeders;

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
use App\Models\Task;
use App\Models\TaxRate;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TimeEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Reference rows that the rest depend on
        if (Currency::count() === 0) {
            Currency::factory()->create(['code' => 'IDR', 'symbol' => 'Rp', 'name' => 'Rupiah', 'is_base' => true]);
            Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar']);
        }
        if (TaxRate::count() === 0) {
            TaxRate::factory()->create(['name' => 'PPN 11%', 'percentage' => 11]);
        }
        if (LeadStatus::count() === 0) {
            LeadStatus::factory()->create(['name' => 'New', 'order' => 1]);
            LeadStatus::factory()->create(['name' => 'Qualified', 'order' => 2]);
            LeadStatus::factory()->create(['name' => 'Won', 'order' => 3, 'is_won' => true]);
            LeadStatus::factory()->create(['name' => 'Lost', 'order' => 4, 'is_lost' => true]);
        }
        if (LeadSource::count() === 0) {
            LeadSource::factory()->count(4)->create();
        }
        if (Department::count() === 0) {
            Department::factory()->create(['name' => 'Support']);
        }
        if (TicketPriority::count() === 0) {
            TicketPriority::factory()->create(['name' => 'Medium', 'order' => 2]);
        }
        if (TicketStatus::count() === 0) {
            TicketStatus::factory()->create(['name' => 'Open', 'order' => 1]);
        }

        $clients = Client::factory()->count(10)->create();

        $clients->each(function (Client $client) {
            Contact::factory()->primary()->create(['client_id' => $client->id]);
            Contact::factory()->count(rand(1, 3))->create(['client_id' => $client->id]);
        });

        Lead::factory()->count(50)->create();

        $projects = Project::factory()->count(8)->create([
            'client_id' => fn () => $clients->random()->id,
        ]);

        $projects->each(function (Project $project) {
            Milestone::factory()->count(rand(2, 4))->create(['project_id' => $project->id]);
            $tasks = Task::factory()->count(rand(8, 16))->create(['project_id' => $project->id]);
            $tasks->random(min(5, $tasks->count()))->each(function (Task $task) {
                TimeEntry::factory()->count(rand(1, 3))->create(['task_id' => $task->id]);
            });
        });

        Estimate::factory()->count(15)->create([
            'client_id' => fn () => $clients->random()->id,
        ]);

        Invoice::factory()->count(30)->create([
            'client_id' => fn () => $clients->random()->id,
        ]);

        Ticket::factory()->count(20)->create([
            'client_id' => fn () => $clients->random()->id,
        ]);

        $kbCat = KbCategory::factory()->create(['name' => 'Getting Started']);
        KbArticle::factory()->count(6)->create(['category_id' => $kbCat->id]);

        $this->command?->info('Demo data seeded: 10 clients, 50 leads, 8 projects, 30 invoices, 20 tickets, 6 KB articles.');
    }
}
