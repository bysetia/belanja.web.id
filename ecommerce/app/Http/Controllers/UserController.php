<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRequest;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $query = User::query();

            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    return '
                        <div class="text-center">
                        <a class="inline-block border border-corn-500 bg-corn-500 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-corn-400 focus:outline-none focus:shadow-outline" 
                            href="' . route('dashboard.user.edit', $item->id) . '"
                            style="background-color: #E7B10A; color: white;">
                            Edit
                        </a>
                        <form class="inline-block" action="' . route('dashboard.user.destroy', $item->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this user?\');">
                            <button class="border border-red-500 bg-red-500 text-white rounded-md px-2 py-1 m-2 transition duration-500 ease select-none hover:bg-red-600 focus:outline-none focus:shadow-outline" >
                                Delete
                            </button>
                            ' . method_field('delete') . csrf_field() . '
                        </form>
                  
                    </div>
                    ';
                })
                ->addColumn('profile_photo_path', function ($item) {
                    $imagePath = $item->profile_photo_path ? asset($item->profile_photo_path) : asset($item->profile_photo_url);
                    return '<div style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                                <img src="' . $imagePath . '" width="50" height="50" style="object-fit: cover;">
                            </div>';
                })
                ->rawColumns(['action', 'profile_photo_path'])
                ->make();
        }

        return view('pages.dashboard.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(User $user)
    {
        return view('pages.dashboard.user.edit', [
            'item' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, User $user)
    {
        $data = $request->all();

        $user->update($data);

        return redirect()->route('dashboard.user.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('dashboard.user.index');
    }

    public function showResetPasswordForm(User $user)
    {
        return view('pages.dashboard.user.reset-password', compact('user'));
    }

    public function resetPassword(Request $request, User $user)
    {
        // Validasi input
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Update password pengguna
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Redirect atau kembalikan respon sukses
        return redirect()->route('dashboard.user.index')->with('success', 'Password reset successfully');
    }
}
