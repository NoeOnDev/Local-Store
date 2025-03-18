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
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $code = rand(100000, 999999);
        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
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

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token], 200);
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
                'business_type_id' => null,
                'has_custom_fields' => true
            ]);
        });

        return response()->json([
            'message' => 'Campos configurados exitosamente',
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
}
