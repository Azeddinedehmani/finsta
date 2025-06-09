<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SystemSetting;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::withCount('activityLogs');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15);
        
        // Statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $adminUsers = User::where('role', 'responsable')->count();
        $pharmacistUsers = User::where('role', 'pharmacien')->count();
        $recentLogins = User::whereNotNull('last_login_at')
                           ->where('last_login_at', '>=', now()->subDays(7))
                           ->count();

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'activeUsers', 'adminUsers', 
            'pharmacistUsers', 'recentLogins'
        ));
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $query = User::withCount('activityLogs');

        // Apply same filters as in index method
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->get();

        // Log export activity
        ActivityLog::logActivity(
            'export',
            'Export de la liste des utilisateurs (' . $users->count() . ' utilisateurs)'
        );

        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Nom',
                'Email',
                'Téléphone',
                'Rôle',
                'Statut',
                'Date de naissance',
                'Adresse',
                'Créé le',
                'Dernière connexion',
                'Dernière IP',
                'Total activités'
            ], ';');

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?: '',
                    $user->role === 'responsable' ? 'Responsable' : 'Pharmacien',
                    $user->is_active ? 'Actif' : 'Inactif',
                    $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : '',
                    $user->address ?: '',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais connecté',
                    $user->last_login_ip ?: '',
                    $user->activity_logs_count
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:responsable,pharmacien',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'force_password_change' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = new User();
        $user->fill($request->except(['password', 'profile_photo', 'force_password_change']));
        $user->password = Hash::make($request->password);
        $user->is_active = $request->has('is_active');
        $user->force_password_change = $request->has('force_password_change');
        $user->password_changed_at = now();

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = $photoPath;
        }

        $user->save();

        // Log activity
        ActivityLog::logActivity(
            'create',
            "Utilisateur créé: {$user->name} ({$user->email})",
            $user,
            null,
            $user->toArray()
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès!');
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with(['activityLogs' => function($query) {
            $query->latest()->take(50);
        }])->findOrFail($id);

        // Get user statistics
        $stats = [
            'total_activities' => $user->activityLogs()->count(),
            'logins_count' => $user->activityLogs()->where('action', 'login')->count(),
            'sales_count' => $user->activityLogs()->forModel('App\Models\Sale')->count(),
            'last_activity' => $user->activityLogs()->latest()->first(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldValues = $user->toArray();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:responsable,pharmacien',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'force_password_change' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->fill($request->except(['password', 'profile_photo', 'force_password_change']));
        $user->is_active = $request->has('is_active');
        $user->force_password_change = $request->has('force_password_change');

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_changed_at = now();
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            $photoPath = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo = $photoPath;
        }

        $user->save();

        // Log activity
        ActivityLog::logActivity(
            'update',
            "Utilisateur modifié: {$user->name} ({$user->email})",
            $user,
            $oldValues,
            $user->toArray()
        );

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Utilisateur mis à jour avec succès!');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deactivating the last admin
        if ($user->role === 'responsable' && $user->is_active) {
            $activeAdmins = User::where('role', 'responsable')
                              ->where('is_active', true)
                              ->where('id', '!=', $id)
                              ->count();
            
            if ($activeAdmins === 0) {
                return redirect()->back()
                    ->withErrors(['error' => 'Impossible de désactiver le dernier administrateur actif.']);
            }
        }

        $oldStatus = $user->is_active;
        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'activé' : 'désactivé';
        
        ActivityLog::logActivity(
            'update',
            "Utilisateur {$action}: {$user->name}",
            $user,
            ['is_active' => $oldStatus],
            ['is_active' => $user->is_active]
        );

        return redirect()->back()
            ->with('success', "Utilisateur {$action} avec succès!");
    }

    /**
     * Delete the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting the last admin
        if ($user->role === 'responsable') {
            $otherAdmins = User::where('role', 'responsable')
                             ->where('id', '!=', $id)
                             ->count();
            
            if ($otherAdmins === 0) {
                return redirect()->back()
                    ->withErrors(['error' => 'Impossible de supprimer le dernier administrateur.']);
            }
        }

        // Prevent deleting current user
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        // Delete profile photo
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Log activity before deletion
        ActivityLog::logActivity(
            'delete',
            "Utilisateur supprimé: {$userName} ({$userEmail})",
            null,
            $user->toArray(),
            null
        );

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès!');
    }

    /**
     * Reset user password
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        // Generate temporary password
        $tempPassword = 'temp' . rand(1000, 9999);
        $user->password = Hash::make($tempPassword);
        $user->force_password_change = true;
        $user->password_changed_at = now();
        $user->save();

        ActivityLog::logActivity(
            'update',
            "Mot de passe réinitialisé pour: {$user->name}",
            $user
        );

        return redirect()->back()
            ->with('success', "Mot de passe réinitialisé. Nouveau mot de passe temporaire: {$tempPassword}")
            ->with('temp_password', $tempPassword);
    }

    /**
     * View user activity logs
     */
    public function activityLogs($id, Request $request)
    {
        $user = User::findOrFail($id);
        
        $query = $user->activityLogs();

        // Filter by action
        if ($request->has('action') && $request->action !== '') {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50);
        
        return view('admin.users.activity-logs', compact('user', 'activities'));
    }
}