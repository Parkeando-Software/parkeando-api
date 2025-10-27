<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function google(Request $request)
    {
        $request->validate(['access_token' => 'required|string']);

        $p = Socialite::driver('google')->stateless()
            ->userFromToken($request->access_token);

        return $this->handleProvider('google', $p, $request->access_token);
    }

    protected function handleProvider(string $provider, $providerUser, ?string $accessToken = null)
    {
        $providerId = (string) $providerUser->getId();
        $email      = $providerUser->getEmail();
        $name       = $providerUser->getName() ?: 'Usuario';

        return DB::transaction(function () use ($provider, $providerId, $email, $name, $accessToken) {

            // 1) ¿ya existe el vínculo social?
            $social = SocialAccount::where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($social) {
                $user = $social->user;
                // opcional: actualizar token del proveedor
                $social->update(['provider_token' => $accessToken]);
            } else {
                // 2) ¿hay usuario con ese email? (si viene) → vincular
                $user = $email ? User::where('email', $email)->first() : null;

                if (! $user) {
                    // 3) crear usuario nuevo (role por defecto)
                    $defaultRoleId = config('auth.default_role_id', 1);

                    // (A) guardar con username temporal único (NOT NULL + unique garantizado)
                    $tempUsername = 'tmp-' . str_replace('-', '', Str::uuid()); // <= 36, pero tu columna es 30
                    $tempUsername = substr($tempUsername, 0, 30);

                    $user = User::create([
                        'role_id'           => $defaultRoleId,
                        'username'          => $tempUsername,          // placeholder
                        'name'              => $name ?? null,          // name es nullable
                        'email'             => $email ?? "no-email-{$provider}-{$providerId}@example.local",
                        'password'          => bcrypt(Str::random(32)), // placeholder
                        'phone'             => null,
                        'accept_terms'      => true,
                        'account_activated' => true,
                    ]);

                    // (B) actualizar al definitivo: Parki{id}
                    $finalUsername = 'parki_' . $user->id + 12598;
                    $user->update(['username' => $finalUsername]);
                }

                // 4) crear el vínculo social si no existía
                SocialAccount::create([
                    'user_id'        => $user->id,
                    'provider'       => $provider,
                    'provider_id'    => $providerId,
                    'provider_token' => $accessToken,
                ]);
            }

            // 5) Asegurar Customer asociado
            Customer::firstOrCreate(
                ['user_id' => $user->id],
                ['points' => 0, 'reputation' => 5.0]
            );

            // 6) Emitir token
            $token = $user->createToken('mobile', ['*'])->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'email'    => $user->email,
                ],
            ]);
        });
    }
}
