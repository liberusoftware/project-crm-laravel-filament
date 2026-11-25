<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Liberu\CRM\Activities\Api\Http\Controllers\TaskController;
use Liberu\CRM\AutomationPack\Http\Controllers\WorkflowController;
use Liberu\CRM\Core\Api\Http\Controllers\ContactController;
use Liberu\CRM\Core\Api\Http\Controllers\LeadController;
use Liberu\CRM\LeadCaptureApi\Http\Controllers\LeadImportController;
use Liberu\CRM\MarketingResourcesApi\Http\Controllers\MarketingController;
use Liberu\CRM\SalesWorkspaceApi\Http\Controllers\DealController;
use Liberu\Foundation\Integrations\Http\Controllers\Api\WebhookController;
use Liberu\Foundation\Integrations\Models\Webhook;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::prefix('marketing')->controller(MarketingController::class)->group(function (): void {
        Route::get('posts', 'index');
        Route::post('posts', 'store');
        Route::put('posts/{post}', 'update');
        Route::post('posts/{post}/approve', 'approve');
        Route::post('posts/{post}/schedule', 'schedule');
        Route::post('posts/{post}/cancel', 'cancel');
        Route::post('generate', 'generate')->middleware('throttle:10,1');
        Route::post('campaigns/{campaign}/manage', 'campaign');
    });

    // Core resources
    Route::apiResource('contacts', ContactController::class);
    Route::apiResource('deals', DealController::class);
    Route::get('leads/import/template', [LeadImportController::class, 'template']);
    Route::post('leads/import', [LeadImportController::class, 'store']);
    Route::apiResource('leads', LeadController::class);
    Route::apiResource('tasks', TaskController::class);

    // Bulk operations — contacts
    Route::post('contacts/bulk/update', [ContactController::class, 'bulkUpdate']);
    Route::post('contacts/bulk/delete', [ContactController::class, 'bulkDelete']);
    Route::post('contacts/bulk/assign', [ContactController::class, 'bulkAssign']);

    // Bulk operations — deals
    Route::post('deals/bulk/update', [DealController::class, 'bulkUpdate']);
    Route::post('deals/bulk/delete', [DealController::class, 'bulkDelete']);
    Route::post('deals/bulk/assign', [DealController::class, 'bulkAssign']);

    // Bulk operations — tasks
    Route::post('tasks/bulk/update', [TaskController::class, 'bulkUpdate']);
    Route::post('tasks/bulk/delete', [TaskController::class, 'bulkDelete']);
    Route::post('tasks/bulk/assign', [TaskController::class, 'bulkAssign']);

    // Webhook management
    Route::bind('webhook', function ($value) {
        $teamId = request()->user()?->currentTeam?->id;

        return $teamId
            ? Webhook::where('team_id', $teamId)->findOrFail($value)
            : Webhook::findOrFail($value);
    });
    Route::get('webhooks/events', [WebhookController::class, 'events']);
    Route::post('webhooks/{webhook}/secret', [WebhookController::class, 'regenerateSecret']);
    Route::apiResource('webhooks', WebhookController::class);

    // Workflow management
    Route::apiResource('workflows', WorkflowController::class);
});
