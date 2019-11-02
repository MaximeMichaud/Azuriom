<?php

namespace Azuriom\Http\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Http\Requests\UserRequest;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Role;
use Azuriom\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('ban')->paginate(25);

        foreach ($users as $user) {
            $user->refreshActiveBan();
        }

        return view('admin.users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create', ['roles' => Role::all()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $role = Role::findOrFail($request->input('role'));

        $request->offsetSet('password', Hash::make($request->input('password')));

        $user = new User($request->all());
        $user->role()->associate($role);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user->refreshActiveBan(),
            'roles' => Role::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Azuriom\Http\Requests\UserRequest  $request
     * @param  \Azuriom\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        if ($user->is_deleted) {
            return redirect()->back();
        }

        $user->fill($request->except(['password']));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $role = Role::findOrFail($request->input('role'));

        $user->role()->associate($role);
        $user->save();

        ActionLog::logUpdate($user);

        return redirect()->route('admin.users.index')->with('success', 'User updated');
    }

    public function verifyEmail(User $user)
    {
        if ($user->is_deleted) {
            return redirect()->back();
        }

        $user->markEmailAsVerified();

        ActionLog::logUpdate($user);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Email verified');
    }

    public function disable2fa(User $user)
    {
        $user->update(['google_2fa_secret' => null]);

        ActionLog::logUpdate($user);

        return redirect()->route('admin.users.edit', $user)->with('success', '2fa disabled');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin() || $user->is_deleted) {
            return redirect()->back();
        }

        $user->comments()->delete();
        $user->likes()->delete();

        $user->fill([
            'name' => 'Deleted #'.$user->id,
            'email' => 'deleted'.$user->id.'@deleted.ltd',
            'password' => Hash::make(Str::random()),
            'role_id' => 1,
            'google_2fa_secret' => null,
        ]);

        $user->email_verified_at = null;
        $user->last_ip = null;
        $user->is_deleted = true;

        $user->setRememberToken(null);
        $user->save();

        return redirect()->route('admin.users.index', $user)->with('success', 'User deleted');
    }
}
