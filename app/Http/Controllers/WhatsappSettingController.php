<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WhatsappSetting;

class WhatsappSettingController extends Controller
{

public function index()
{
    $setting = WhatsappSetting::first();
    return view('setting.whatsapp', compact('setting'));
}

public function update(Request $request)
{
    $setting = WhatsappSetting::first();

    if(!$setting){
        $setting = new WhatsappSetting();
    }

    $setting->update([
        'provider' => $request->provider,
        'api_url' => $request->api_url,
        'token' => $request->token,
        'sender' => $request->sender,
        'is_active' => $request->is_active
    ]);

    return back()->with('success','Setting berhasil disimpan');
}


public function test(Request $request)
{
    $setting = WhatsappSetting::first();

    try {

        $response = Http::withHeaders([
            'Authorization' => $setting->token
        ])->post($setting->api_url, [
            'target' => $request->no_wa,
            'message' => $request->message
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Pesan test berhasil dikirim');
        }

        return back()->with('error', 'Gagal mengirim pesan');

    } catch (\Exception $e) {

        return back()->with('error', 'Koneksi ke WhatsApp Gateway gagal');

    }
}

}