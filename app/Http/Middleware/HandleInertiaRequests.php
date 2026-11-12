<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'branch_id' => $user->branch_id,
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'name' => $user->branch->name,
                        'code' => $user->branch->code,
                    ] : null,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'notifications' => $user ? [
                'unread_count' => $user->unreadNotifications()->count(),
                'latest' => $user->notifications()->take(10)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Notifikasi',
                    'message' => $n->data['message'] ?? '',
                    'url' => $n->data['url'] ?? null,
                    'type' => $n->data['type'] ?? 'info',
                    'read_at' => $n->read_at?->format('Y-m-d H:i:s'),
                    'created_at_human' => $n->created_at->diffForHumans(),
                ]),
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
