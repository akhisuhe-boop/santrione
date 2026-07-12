<?php

namespace App\Http\Controllers;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;

class SlipGajiController extends Controller
{
    protected function payroll(Payroll $payroll): Payroll
    {
        abort_if($payroll->status !== 'dibayar', 404);

        return $payroll->load([
            'pegawai.lembagas.yayasan',
            'items',
            'adjustments',
        ]);
    }

    public function show(Payroll $payroll)
    {
        return view('slip-gaji.show', [
            'payroll' => $this->payroll($payroll),
        ]);
    }

    public function thermal58(Payroll $payroll)
    {
        return view('slip-gaji.thermal58', [
            'payroll' => $this->payroll($payroll),
        ]);
    }

    public function thermal80(Payroll $payroll)
    {
        return view('slip-gaji.thermal80', [
            'payroll' => $this->payroll($payroll),
        ]);
    }

    public function dotmatrix(Payroll $payroll)
    {
        return view('slip-gaji.dotmatrix', [
            'payroll' => $this->payroll($payroll),
        ]);
    }

    public function pdf(Payroll $payroll)
    {
        $payroll = $this->payroll($payroll);

        return Pdf::loadView('slip-gaji.pdf', compact('payroll'))
            ->setPaper('a4')
            ->stream('Slip-Gaji-'.$payroll->kode.'.pdf');
    }

    public function cetak(Payroll $payroll)
    {
        $payroll = $this->payroll($payroll);
        $printer = $payroll->pegawai
            ?->lembaga
            ?->printer_kwitansi ?? 'thermal80';

        return match ($printer) {

            'thermal58' => view('slip-gaji.thermal58', compact('payroll')),
            'dotmatrix' => view('slip-gaji.dotmatrix', compact('payroll')),
            default => view('slip-gaji.thermal80', compact('payroll')),
        };
    }
}