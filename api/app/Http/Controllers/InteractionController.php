<?php

namespace App\Http\Controllers;

use App\Jobs\Interact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Actions for interaction
 */
class InteractionController extends Controller
{
    /**
     * Handle an interaction.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = json_decode($request->input('payload'));

        Interact::dispatch($payload);

        return new JsonResponse();
    }

}
