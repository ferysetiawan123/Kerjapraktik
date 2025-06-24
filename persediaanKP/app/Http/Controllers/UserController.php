<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Untuk Datatables, kita akan mengembalikan view kosong dan data akan diambil via AJAX
        return view('user.index');
    }

    // Metode untuk mengambil data pengguna untuk Datatables (AJAX)
    public function data()
    {
        $users = User::orderBy('id', 'desc')->get();

        return datatables()
            ->of($users)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                // Jangan izinkan user menghapus dirinya sendiri
                $deleteButton = '<button type="button" onclick="deleteData(\'' . route('user.destroy', $user->id) . '\')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i> Hapus</button>';
                if (Auth::user()->id == $user->id) { // Gunakan Auth::user()
                    $deleteButton = ''; // Jangan tampilkan tombol hapus untuk user yang sedang login
                }
                return '
                    <button type="button" onclick="editForm(\'' . route('user.update', $user->id) . '\')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i> Edit</button>
                    ' . $deleteButton . '
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // Metode ini tidak akan digunakan jika Anda menggunakan modal form seperti contoh blade
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:administrator,manager,kasir', // Validasi role
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            // 'foto' => '/img/user.jpg', // Default foto jika diperlukan dan ada di fillable
        ]);

        return response()->json($user, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Metode ini tidak akan digunakan jika Anda menggunakan modal form seperti contoh blade
    public function show($id)
    {
        $user = User::find($id);

        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Metode ini tidak akan digunakan jika Anda menggunakan modal form seperti contoh blade
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user // Gunakan Route Model Binding
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // Pastikan email unik kecuali email user itu sendiri
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:6|confirmed', // Password bisa kosong jika tidak ingin diubah
            'role' => 'required|in:administrator,manager,kasir',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->has('password') && $request->password != "") {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json($user, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user // Gunakan Route Model Binding
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Pastikan user tidak bisa menghapus dirinya sendiri
        if (Auth::user()->id == $user->id) { // Gunakan Auth::user()
            return response()->json('Anda tidak bisa menghapus akun Anda sendiri!', 403);
        }

        $user->delete();

        return response()->json(null, 204); // No content
    }

    // Metode untuk mengelola profil pengguna yang sedang login (profil sendiri)
    public function profil()
    {
        $profil = Auth::user(); // Gunakan Auth::user()
        return view('user.profil', compact('profil'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user(); // Gunakan Auth::user()

        // Validasi untuk update profil
        $request->validate([
            'name' => 'required|string|max:255',
            'old_password' => 'nullable|required_with:password|string', // Hanya wajib jika password baru diisi
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->name = $request->name;
        // Jika Anda memiliki kolom foto dan ingin mengelolanya melalui profil
        // if ($request->hasFile('foto')) {
        //     $file = $request->file('foto');
        //     $fileName = time() . '.' . $file->getClientOriginalExtension();
        //     $file->move(public_path('img'), $fileName);
        //     $user->foto = '/img/' . $fileName;
        // }

        if ($request->has('password') && $request->password != "") {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json('Password lama tidak sesuai.', 422);
            }
            $user->password = bcrypt($request->password);
        }

        $user->update();

        return response()->json($user, 200);
    }
}