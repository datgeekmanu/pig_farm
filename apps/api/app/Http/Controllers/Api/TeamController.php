<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farm\InviteMemberRequest;
use App\Http\Resources\TeamMemberResource;
use App\Models\Farm;
use App\Models\User;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TeamMemberResource::collection($this->farm()->users);
    }

    // Creates a user in this farm's account and grants them access to the farm.
    public function store(InviteMemberRequest $request): JsonResponse
    {
        $farm = $this->farm();
        $data = $request->validated();

        $user = User::create([
            'account_id' => $farm->account_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed via cast
        ]);
        $farm->users()->attach($user->id, ['role' => $data['role']]);

        $member = $farm->users()->whereKey($user->id)->first();

        return TeamMemberResource::make($member)->response()->setStatusCode(201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot remove yourself.'], 422);
        }

        $farm = $this->farm();
        if (! $farm->users()->whereKey($user->id)->exists()) {
            abort(404);
        }
        $farm->users()->detach($user->id);

        return response()->json(['message' => 'Member removed.']);
    }

    private function farm(): Farm
    {
        return Farm::findOrFail(app(CurrentFarm::class)->id());
    }
}
