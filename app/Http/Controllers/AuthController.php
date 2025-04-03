<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Models\BusinessType;
use App\Models\AppointmentField;
use App\Http\Resources\BusinessTypeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $imagePath,
        ]);

        $code = rand(100000, 999999);
        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'is_valid' => true,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw("Your verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Email Verification Code');
        });

        return response()->json(['message' => 'User registered. Please check your email for verification code.'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'user' => null,
                    'error' => 'Las credenciales proporcionadas son incorrectas.',
                    'token' => null
                ], 401);
            }

            $user = $request->user();
            $token = $user->createToken('auth_token')->plainTextToken;

            $profileImageUrl = $user->profile_image
                ? asset('storage/' . $user->profile_image)
                : null;

            return response()->json([
                'success' => true,
                'user' => [
                    'displayName' => $user->name,
                    'email' => $user->email,
                    'photoURL' => $profileImageUrl,
                    'id' => $user->id,
                    'emailVerified' => $user->email_verified_at !== null,
                    'hasCustomFields' => $user->has_custom_fields,
                ],
                'error' => null,
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'user' => null,
                'error' => 'Error al iniciar sesión: ' . $e->getMessage(),
                'token' => null
            ], 500);
        }
    }

    public function getUsers()
    {
        $users = User::all();
        return response()->json(['users' => $users], 200);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('is_valid', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 400);
        }

        $user->email_verified_at = now();
        $user->save();

        $verificationCode->is_valid = false;
        $verificationCode->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    public function setBusinessType(Request $request)
    {
        $request->validate([
            'business_type_id' => 'required|exists:business_types,id',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            $user->appointmentFields()->delete();

            $templateFields = AppointmentField::where('business_type_id', $request->business_type_id)
                ->get();

            foreach ($templateFields as $field) {
                $user->appointmentFields()->create([
                    'name' => $field->name,
                    'type' => $field->type,
                    'required' => $field->required,
                    'options' => $field->options,
                    'order' => $field->order,
                    'active' => true
                ]);
            }

            $user->update([
                'has_custom_fields' => true
            ]);
        });

        return response()->json([
            'message' => 'Campos configurados exitosamente',
            'fields' => $user->appointmentFields
        ]);
    }

    public function setCustomFields(Request $request)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,select,boolean,date,number',
            'fields.*.required' => 'required|boolean',
            'fields.*.options' => 'required_if:fields.*.type,select|array',
            'fields.*.order' => 'integer',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            $user->appointmentFields()->delete();

            foreach ($request->fields as $index => $fieldData) {
                $user->appointmentFields()->create([
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'],
                    'required' => $fieldData['required'],
                    'options' => $fieldData['options'] ?? [],
                    'order' => $fieldData['order'] ?? $index,
                    'active' => true
                ]);
            }

            $user->update([
                'has_custom_fields' => true
            ]);
        });

        return response()->json([
            'message' => 'Campos personalizados configurados exitosamente',
            'fields' => $user->appointmentFields
        ]);
    }

    public function getBusinessTypes()
    {
        $types = BusinessType::with('appointmentFields')->get();
        return response()->json([
            'business_types' => BusinessTypeResource::collection($types)
        ]);
    }

    public function getProfile()
    {
        $user = Auth::user();
        $user->load(['appointmentFields']);

        $profileImageUrl = $user->profile_image
            ? asset('storage/' . $user->profile_image)
            : null;

        return response()->json([
            'success' => true,
            'user' => [
                'displayName' => $user->name,
                'email' => $user->email,
                'photoURL' => $profileImageUrl,
                'id' => $user->id,
                'emailVerified' => $user->email_verified_at !== null,
                'hasCustomFields' => $user->has_custom_fields,
            ],
            'error' => null,
            'token' => null
        ], 200);
    }

    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($user->profile_image && file_exists(storage_path('app/public/' . $user->profile_image))) {
            unlink(storage_path('app/public/' . $user->profile_image));
        }

        $imagePath = $request->file('profile_image')->store('profile_images', 'public');

        $user->update([
            'profile_image' => $imagePath
        ]);

        $profileImageUrl = asset('storage/' . $imagePath);

        return response()->json([
            'message' => 'Imagen de perfil actualizada exitosamente',
            'photoURL' => $profileImageUrl
        ]);
    }
}
