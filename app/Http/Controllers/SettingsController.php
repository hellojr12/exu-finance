<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    public function index()
    {
        $eventCategories   = EventCategory::withTrashed()->get();
        $expenseCategories = ExpenseCategory::withTrashed()->get();
        $users             = User::with('roles')->get();
        $roles             = Role::all();
        $settings          = Setting::all()->keyBy('key');

        return view('settings.index', compact(
            'eventCategories', 'expenseCategories', 'users', 'roles', 'settings'
        ));
    }

    // ─── Event Categories ────────────────────────────────────────────────────

    public function storeEventCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:event_categories,name']);
        EventCategory::create([
            'name'  => $request->name,
            'slug'  => Str::slug($request->name),
            'color' => $request->color ?? '#3B82F6',
            'description' => $request->description,
        ]);
        return back()->with('success', 'Event category added.');
    }

    public function updateEventCategory(Request $request, EventCategory $eventCategory)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $eventCategory->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'color'       => $request->color ?? $eventCategory->color,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);
        return back()->with('success', 'Event category updated.');
    }

    public function destroyEventCategory(EventCategory $eventCategory)
    {
        $eventCategory->delete();
        return back()->with('success', 'Event category removed.');
    }

    // ─── Expense Categories ──────────────────────────────────────────────────

    public function storeExpenseCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:expense_categories,name']);
        ExpenseCategory::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'color'       => $request->color ?? '#EF4444',
            'description' => $request->description,
        ]);
        return back()->with('success', 'Expense category added.');
    }

    public function updateExpenseCategory(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $expenseCategory->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'color'       => $request->color ?? $expenseCategory->color,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);
        return back()->with('success', 'Expense category updated.');
    }

    public function destroyExpenseCategory(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();
        return back()->with('success', 'Expense category removed.');
    }

    // ─── Users ───────────────────────────────────────────────────────────────

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole($request->role);

        return back()->with('success', 'User created.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|exists:roles,name',
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);
        $user->syncRoles([$request->role]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'User updated.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    // ─── General Settings ────────────────────────────────────────────────────

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name'    => 'required|string|max:100',
            'currency'    => 'required|string|max:3',
            'fiscal_year' => 'required|string',
        ]);

        Setting::set('app_name', $request->app_name, 'general');
        Setting::set('currency', $request->currency, 'general');
        Setting::set('fiscal_year', $request->fiscal_year, 'general');

        return back()->with('success', 'Settings saved.');
    }
}
