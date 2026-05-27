<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Webhook ingestion endpoint – POST /api/webhook/{source}
|
| The {source} segment identifies the sending system (e.g. "dapodik",
| "simak").  The VerifyWebhookSignature middleware validates the
| X-Webhook-Signature and X-Webhook-Timestamp headers before the request
| reaches the controller.
|
*/

Route::post('/webhook/{source}', [WebhookController::class, 'receive'])
    ->middleware('webhook.signature')
    ->name('webhook.receive');
