<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Customer; // ⬅️ importar
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // ⬅️ (recomendado) transacción
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

    public function facebook(Request $request)
    {
        $request->validate(['access_token' => 'required|string']);

        $p = Socialite::driver('facebook')->stateless()
            ->userFromToken($request->access_token);

        return $this->handleProvider('facebook', $p, $request->access_token);
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

                if (!$user) {
                    // 3) crear usuario nuevo (role por defecto)
                    $defaultRoleId = config('auth.default_role_id', 2); // ajustar a su caso
                    $user = User::create([
                        'role_id'  => $defaultRoleId,
                        'name'     => $name,
                        'email'    => $email ?? "no-email-{$provider}-{$providerId}@example.local",
                        'password' => bcrypt(Str::random(32)), // placeholder
                        'phone'    => null,
                        
                        'account_activated' => true,
                    ]);
                }

                // 4) crear el vínculo social
                $social = SocialAccount::create([
                    'user_id'        => $user->id,
                    'provider'       => $provider,
                    'provider_id'    => $providerId,
                    'provider_token' => $accessToken,
                ]);
            }

            // ⬅️ 5) **Asegurar Customer** (clave del fix)
            //     NOTA: ajustar campos por defecto a su esquema (points/reputation u otros NOT NULL)
            Customer::firstOrCreate(
                ['user_id' => $user->id],
                ['points' => 0, 'reputation' => 5]
            );

            // 6) emitir token (alineado con el flujo clásico)
            $token = $user->createToken('mobile', ['*'])->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);
        });
    }
}
