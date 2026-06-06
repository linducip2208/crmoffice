<?php

use App\Models\Client;
use App\Models\Contact;
use App\Models\Department;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\User;

test('ticket flow: create ticket, assign, add reply, resolve', function () {
    $client = Client::factory()->create(['company_name' => 'Client Support']);
    $department = Department::factory()->create(['name' => 'Technical Support']);
    $priority = TicketPriority::factory()->create(['name' => 'High']);
    $openStatus = TicketStatus::factory()->create(['name' => 'Open', 'is_open' => true]);
    $resolvedStatus = TicketStatus::factory()->create(['name' => 'Resolved', 'is_open' => false]);
    $agent = User::factory()->create(['name' => 'Support Agent']);

    $ticket = Ticket::factory()->create([
        'client_id' => $client->id,
        'department_id' => $department->id,
        'priority_id' => $priority->id,
        'status_id' => $openStatus->id,
        'assigned_to' => $agent->id,
        'subject' => 'Cannot export reports',
    ]);

    expect($ticket->subject)->toBe('Cannot export reports');
    expect($ticket->client_id)->toBe($client->id);
    expect($ticket->assignee->name)->toBe('Support Agent');
    expect($ticket->status->is_open)->toBeTrue();

    TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'body' => 'Please try clearing your cache and try again.',
        'is_internal' => false,
        'source' => 'web',
    ]);

    TicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $agent->id,
        'body' => 'Escalate to backend team if issue persists.',
        'is_internal' => true,
        'source' => 'web',
    ]);

    expect($ticket->replies()->count())->toBe(2);
    expect($ticket->replies()->where('is_internal', false)->count())->toBe(1);
    expect($ticket->replies()->where('is_internal', true)->count())->toBe(1);

    $ticket->update([
        'status_id' => $resolvedStatus->id,
        'resolved_at' => now(),
        'closed_at' => now(),
    ]);

    $ticket->refresh();
    expect($ticket->status_id)->toBe($resolvedStatus->id);
    expect($ticket->resolved_at)->not->toBeNull();
    expect($ticket->closed_at)->not->toBeNull();
});

test('ticket flow: customer contact can reply to ticket', function () {
    $client = Client::factory()->create();
    $contact = Contact::factory()->create(['client_id' => $client->id]);
    $openStatus = TicketStatus::factory()->create(['is_open' => true]);
    $priority = TicketPriority::factory()->create();

    $ticket = Ticket::factory()->create([
        'client_id' => $client->id,
        'contact_id' => $contact->id,
        'status_id' => $openStatus->id,
        'priority_id' => $priority->id,
    ]);

    TicketReply::create([
        'ticket_id' => $ticket->id,
        'contact_id' => $contact->id,
        'body' => 'Here is the screenshot of the error.',
        'is_internal' => false,
        'source' => 'portal',
    ]);

    $reply = $ticket->replies()->first();
    expect($reply->contact_id)->toBe($contact->id);
    expect($reply->body)->toBe('Here is the screenshot of the error.');
});

test('ticket flow: sla due dates are tracked', function () {
    $openStatus = TicketStatus::factory()->create(['is_open' => true]);
    $priority = TicketPriority::factory()->create();

    $ticket = Ticket::factory()->create([
        'status_id' => $openStatus->id,
        'priority_id' => $priority->id,
        'first_response_due_at' => now()->addHours(2),
        'resolve_due_at' => now()->addHours(8),
    ]);

    expect($ticket->first_response_due_at)->not->toBeNull();
    expect($ticket->resolve_due_at)->not->toBeNull();
    expect($ticket->first_response_due_at->gt(now()))->toBeTrue();
    expect($ticket->resolve_due_at->gt($ticket->first_response_due_at))->toBeTrue();
});

test('kb flow: create category, create article, publish, verify', function () {
    $author = User::factory()->create(['name' => 'KB Author']);
    $category = KbCategory::factory()->create([
        'name' => 'Getting Started',
        'slug' => 'getting-started',
        'is_public' => true,
    ]);

    expect($category->name)->toBe('Getting Started');
    expect($category->is_public)->toBeTrue();

    $article = KbArticle::factory()->create([
        'category_id' => $category->id,
        'title' => 'How to Setup Your Account',
        'slug' => 'how-to-setup',
        'is_published' => true,
        'author_id' => $author->id,
        'published_at' => now(),
    ]);

    expect($article->title)->toBe('How to Setup Your Account');
    expect($article->is_published)->toBeTrue();
    expect($article->category_id)->toBe($category->id);
    expect($article->author->name)->toBe('KB Author');
});

test('kb flow: unpublished article is not searchable', function () {
    $category = KbCategory::factory()->create(['is_public' => true]);

    $published = KbArticle::factory()->create([
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $draft = KbArticle::factory()->create([
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
    ]);

    expect($published->is_published)->toBeTrue();
    expect($draft->is_published)->toBeFalse();

    $publishedArticles = KbArticle::where('is_published', true)->count();
    expect($publishedArticles)->toBe(1);
});
