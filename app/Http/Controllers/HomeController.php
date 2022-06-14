<?php

namespace App\Http\Controllers;

use App\Models\absen;
use App\Models\surat;
use App\Models\edit;
use App\Models\User;
use App\Models\datauser;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
date_default_timezone_set('Asia/Jakarta');
use Illuminate\Support\Facades\DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        config(['app.timezone' => 'Asia/Jakarta']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('pns');
    }

    public function tabel()
    {
        // dd(Auth::user()->id);
        if(request('month') != NULL && request('year') != NULL){
            return view('tabel', ['absen' => absen::whereMonth('tanggal', request('month'))->whereYear('tanggal', request('year'))->where('user_id', Auth::user()->id)->get()]);
        }else{
            return view('tabel', ['absen' => absen::whereMonth('tanggal', date('m'))->where('user_id', Auth::user()->id)->get()]);
        }
    }

    public function datakaryawan()
    {
        return view('datakaryawan');
    }

    public function profil()
    {
        return view('profil');
    }

    public function absen()
    {
        return view('absen');
    }

    public function admin()
    {
        return view('admin');
    }

    public function kabid()
    {
        return view('kabid');
    }

    public function pns()
    {
        return view('pns');
    }

    public function absensi()
    {
        return view('absensi' , ['today' => absen::where('tanggal', date('Y-m-d'))->where('user_id', Auth::user()->id)->first()]);
    }

    public function webcam()
    {
        return view('webcam');
    }

    public function presentasiAbsen()
    {
        if(request('id') != NUll)
        {
            $hitungmasuk = absen::where('keterangan','Masuk')->where('user_id', request('id'))->count();
            $hitungijin = absen::where('keterangan','Ijin')->where('user_id', request('id'))->count();
            $hitungdl = absen::where('keterangan','DL')->where('user_id', request('id'))->count();
            $karyawan = DB::table('users')->where('id',request('id'))->get();

            if(request('month') != NULL && request('year') != NULL){
                return view('presentasiabsen',['absen' => absen::whereMonth('tanggal', request('month'))->whereYear('tanggal', request('year'))->where('user_id', request('id'))->get()],['hitungmasuk'=>$hitungmasuk,'hitungijin'=>$hitungijin,'hitungdl'=>$hitungdl,'karyawan'=>$karyawan]);
            }else{
                return view('presentasiabsen', ['absen' => absen::whereMonth('tanggal', date('m'))->where('user_id', request('id'))->get()],['hitungmasuk'=>$hitungmasuk,'hitungijin'=>$hitungijin,'hitungdl'=>$hitungdl,'karyawan'=>$karyawan]);
            }
        }
        else{
            $hitungmasuk = absen::where('keterangan','Masuk')->where('user_id', Auth::user()->id)->count();
            $hitungijin = absen::where('keterangan','Ijin')->where('user_id',  Auth::user()->id)->count();
            $hitungdl = absen::where('keterangan','DL')->where('user_id',  Auth::user()->id)->count();
            $karyawan = DB::table('users')->where('id', Auth::user()->id)->get();
            if(request('month') != NULL && request('year') != NULL){
                return view('presentasiabsen', ['absen' => absen::whereMonth('tanggal', request('month'))->whereYear('tanggal', request('year'))->where('user_id', Auth::user()->id)->get()],['hitungmasuk'=>$hitungmasuk,'hitungijin'=>$hitungijin,'hitungdl'=>$hitungdl,'karyawan'=>$karyawan]);
            }else{
                return view('presentasiabsen', ['absen' => absen::whereMonth('tanggal', date('m'))->where('user_id', Auth::user()->id)->get()],['hitungmasuk'=>$hitungmasuk,'hitungijin'=>$hitungijin,'hitungdl'=>$hitungdl,'karyawan'=>$karyawan]);
            }
        }
    }

    // public function coba()
    // {
    //     $tgl = '2022-06-07';
    //     if (date('Y-m-d', strtotime('+1 days', strtotime($tgl))) == date('Y-m-d')) {
    //         dd('sama');
    //     } else {
    //         dd('beda');
    //     }

    //     return view('coba');
    // }


    public function surat(Request $request)
    {
            $request->validate([
                'surat' => 'file',
            ]);
            $filesurat = $request->file('surat')->store('public/surat');
            $namesurat = explode('/',$filesurat);
            surat::create([
                'tanggal' => date('Y-m-d'),
                'waktu' => date('G:i:s'),
                'keluar' => date('G:i:s'),
                'user_id' => Auth::user()->id,
                'keterangan' => 'Ijin',
                'surat' => $namesurat[2],
            ]);
            return redirect('/tabel')->with('success', 'Berhasil Absen Ijin Masuk Pada '. date('Y-m-d') . ' ' . date('G:i:s'));
    }


    public function absenmasuk(Request $request)
    {
        absen::create([
            'tanggal' => date('Y-m-d'),
            'waktu' => date('G:i:s'),
            'user_id' => Auth::user()->id,
            'keterangan' => 'Masuk',
        ]);
        return redirect('/tabel')->with('success', 'Berhasil Absen Masuk Pada '. date('Y-m-d') . ' ' . date('G:i:s'));
    }

    public function absenkeluar(absen $absen)
    {
        absen::where('id', $absen->id)->update([
            'keluar' => date('G:i:s')
        ]);

        return redirect('/tabel')->with('success', 'Berhasil Absen Keluar Pada '. date('Y-m-d') . ' ' . date('G:i:s'));
    }

    use RegistersUsers;
     /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function tambahuser(Request $request)
    {
        User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'eemail' => $request['eemail'],
            'password' => Hash::make($request['password']),
        ]);
        return redirect('/datakaryawan')->with('success', 'Berhasil Menambahkan User '. date('Y-m-d') . ' ' . date('G:i:s'));
    }

    public function datauser($id)
    {
        $data_user= DB::select('select * from users where id = ?', [$id]);
    }
    public function edit(Request $request,$id)
    {
        $data_user =DB::select('select * from users where id = ?', [$id]);
        $user_name = $request->input('name');
        $user_email = $request->input('email');
        $user_eemail = $request->input('eemail');
        DB::update('update from users set name = ?,email = ?,eemail = ?',[$user_name,$user_email,$user_eemail]);
        return redirect('/datakaryawan')->with('success', 'Berhasil Mengedit Data '. date('Y-m-d') . ' ' . date('G:i:s'));
    }




    public function destroy($id)
    {
        DB::delete('delete from users where id = ?', [$id]);
        return redirect('/datakaryawan')->with('success', 'Berhasil Menghapus Data '. date('Y-m-d') . ' ' . date('G:i:s'));
    }


    public function hitungpresentasi()
    {
        $hitungmasuk = absen::where('keterangan','Masuk')->where('user_id',  Auth::user()->id)->count();
        $hitungmasuk = absen::where('keterangan','Ijin')->where('user_id',  Auth::user()->id)->get();
        return ($hitungmasuk);
    }
}