<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Register a new user
     *
     * This endpoint allows users to create a new account. Returns a Sanctum PAT token if token_auth is true.
     *
     * @group Authentication
     *
     * @bodyParam name string required The user's full name. Example: Jane Doe
     * @bodyParam email string required The user's email address. Example: jane@example.com
     * @bodyParam password string required The user's password (min: 8 characters). Example: secret12345
     * @bodyParam token_auth boolean If true, returns a Personal Access Token for mobile/external use. Example: true
     *
     * @responseField user object The registered user object
     * @responseField token string The Personal Access Token (only if token_auth=true)
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Registered",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Jane Doe",
     *       "email": "jane@example.com",
     *       "email_verified_at": null,
     *       "created_at": "2023-08-19T10:00:00.000000Z",
     *       "updated_at": "2023-08-19T10:00:00.000000Z"
     *     },
     *     "token": "1|abcdef1234567890"
     *   },
     *   "meta": {}
     * }
     * @response 201 scenario="Web session (no token)" {
     *   "status": "success",
     *   "message": "Registered",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Jane Doe",
     *       "email": "jane@example.com",
     *       "email_verified_at": null,
     *       "created_at": "2023-08-19T10:00:00.000000Z",
     *       "updated_at": "2023-08-19T10:00:00.000000Z"
     *     }
     *   },
     *   "meta": {}
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "data": {
     *     "errors": {
     *       "email": ["The email has already been taken."],
     *       "password": ["The password must be at least 8 characters."]
     *     }
     *   },
     *   "meta": {}
     * }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'token_auth' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        if ($request->boolean('token_auth')) {
            $token = $user->createToken('mobile')->plainTextToken;
            return $this->created(['user' => $user, 'token' => $token], 'Registered');
        }

        // For web/SPA cookie sessions, token not required
        return $this->created(['user' => $user], 'Registered');
    }

    /**
     * Login user
     *
     * Authenticate a user and return a Sanctum PAT token if token_auth is true, otherwise rely on cookie session.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: jane@example.com
     * @bodyParam password string required The user's password. Example: secret12345
     * @bodyParam token_auth boolean If true, returns a Personal Access Token for mobile/external use. Example: true
     *
     * @responseField user object The authenticated user object
     * @responseField token string The Personal Access Token (only if token_auth=true)
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Logged in",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Jane Doe",
     *       "email": "jane@example.com",
     *       "email_verified_at": "2023-08-19T10:00:00.000000Z",
     *       "created_at": "2023-08-19T10:00:00.000000Z",
     *       "updated_at": "2023-08-19T10:00:00.000000Z"
     *     },
     *     "token": "1|abcdef1234567890"
     *   },
     *   "meta": {}
     * }
     * @response 200 scenario="Web session (no token)" {
     *   "status": "success",
     *   "message": "Logged in",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Jane Doe",
     *       "email": "jane@example.com",
     *       "email_verified_at": "2023-08-19T10:00:00.000000Z",
     *       "created_at": "2023-08-19T10:00:00.000000Z",
     *       "updated_at": "2023-08-19T10:00:00.000000Z"
     *     }
     *   },
     *   "meta": {}
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "The provided credentials are incorrect.",
     *   "data": {
     *     "errors": {
     *       "email": ["The provided credentials are incorrect."]
     *     }
     *   },
     *   "meta": {}
     * }
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'      => ['required', 'email'],
            'password'   => ['required', 'string'],
            'token_auth' => ['sometimes', 'boolean'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            // Keep Laravel's 422 semantics for bad credentials
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($request->boolean('token_auth')) {
            $token = $user->createToken('mobile')->plainTextToken;
            return $this->success(['user' => $user, 'token' => $token], 'Logged in');
        }

        // Web/SPA: cookie-based session (after hitting /sanctum/csrf-cookie on the client)
        return $this->success(['user' => $user], 'Logged in');
    }

    /**
     * Logout user
     *
     * Revokes current PAT token if present (mobile) and ends web session.
     * Requires authentication via either cookie session or bearer token.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Logged out",
     *   "data": null,
     *   "meta": {}
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "Unauthenticated",
     *   "data": null,
     *   "meta": {}
     * }
     */
    public function logout(Request $request)
    {
        // Revoke current access token for token-based clients
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // End cookie session for web
        auth()->guard('web')->logout();

        return $this->success(null, 'Logged out');
    }
}
